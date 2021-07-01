<?php

namespace Novay\SsoPhp\Services;

use Zefy\SimpleSSO\SSOBroker;
use GuzzleHttp;

class Broker extends SSOBroker
{
    /**
     * SSO servers URL.
     * @var string
     */
    protected $ssoServerUrl;

    /**
     * Broker name by which it will be identified.
     * @var string
     */
    protected $brokerName;

    /**
     * Super secret broker's key.
     * @var string
     */
    protected $brokerSecret;

    /**
     * Get Dari String User
     * kemudian simpan ke property
     */
    public function __construct($properti)
    {
        $this->ssoServerUrl = $properti['url'];
        $this->brokerName = $properti['name'];
        $this->brokerSecret = $properti['secret'];
    }

    /**
     * Set base class options (sso server url, broker name and secret, etc).
     *
     * @return void
     *
     * @throws Exception
     */
    protected function setOptions()
    {
        if (!$this->ssoServerUrl || !$this->brokerName || !$this->brokerSecret) {
            throw new \Exception('Missing configuration values.');
        }
    }

    /**
     * Attach client session to broker session in SSO server.
     *
     * @return void
     */
    public function getLogin()
    {
        $queries = http_build_query([
            'name' => $this->brokerName, 
            'secret' => $this->brokerSecret, 
            'redirect_uri' => $this->getCurrentUrl(), 
            'response_type' => 'code', 
        ]);

        $this->redirect($this->ssoServerUrl . '/oauth/sso/authorize?' . $queries);
    }

    /**
     * Login client to SSO server with user credentials.
     *
     * @param string $username
     * @param string $password
     *
     * @return bool
     */
    public function login(string $username, string $password)
    {
        $token = $this->token;

        $this->userInfo = $this->makeRequest('POST', 'login', compact('username', 'password', 'token'));

        return var_dump($this->userInfo);

        if (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Getting user info from SSO based on client session.
     *
     * @return array
     */
    public function getUser($token, $uid, $pwd)
    {
        $uid = base64_decode($uid);
        $pwd = base64_decode($pwd);

        if (!isset($this->userInfo) || !$this->userInfo) {
            $this->userInfo = $this->makeRequest('GET', 'userInfo', compact('token', 'uid', 'pwd'));
        }

        return $this->userInfo;
    }

    /**
     * Login client to SSO server with user token.
     *
     * @param string $token
     *
     * @return bool
     */
    public function token(string $token)
    {
        $this->userInfo = $this->makeRequest('POST', 'token', compact('token'));

        if (!isset($this->userInfo['error']) && isset($this->userInfo['data']['id'])) {
            return true;
        }

        return false;
    }

    /**
     * Somehow save random token for client.
     *
     * @return void
     */
    protected function saveToken()
    {
        if (isset($this->token) && $this->token) {
            return;
        }

        if ($this->token = $this->getCookie($this->getCookieName())) {
            return;
        }

        // If cookie token doesn't exist, we need to create it with unique token...
        $this->token = base_convert(md5(uniqid(rand(), true)), 16, 36);
        setcookie($this->getCookieName(), $this->token, time() + 60 * 60 * 12, '/');

        // ... and attach it to broker session in SSO server.
        $this->attach();
    }

    /**
     * Delete saved token.
     *
     * @return void
     */
    protected function deleteToken()
    {
        $this->token = null;
        setcookie($this->getCookieName(), null, -1, '/');
    }

    /**
     * Make request to SSO server.
     *
     * @param string $method Request method 'post' or 'get'.
     * @param string $command Request command name.
     * @param array $parameters Parameters for URL query string if GET request and form parameters if it's POST request.
     *
     * @return array
     */
    protected function makeRequest(string $method, string $command, array $parameters = [])
    {
        $commandUrl = $this->generateCommandUrl($command);
        $headers = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '. $this->getSessionId(),
        ];
        switch ($method) {
            case 'POST':
                $body = ['form_params' => $parameters];
                break;
            case 'GET':
                $body = ['query' => $parameters];
                break;
            default:
                $body = [];
                break;
        }
        $client = new GuzzleHttp\Client;
        $response = $client->request($method, $commandUrl, $body + ['headers' => $headers]);
        return json_decode($response->getBody(), true);
    }

    /**
     * Redirect client to specified url.
     *
     * @param string $url URL to be redirected.
     * @param array $parameters HTTP query string.
     * @param int $httpResponseCode HTTP response code for redirection.
     *
     * @return void
     */
    protected function redirect(string $url, array $parameters = [], int $httpResponseCode = 307)
    {
        $query = '';
        // Making URL query string if parameters given.
        if (!empty($parameters)) {
            $query = '?';
            if (parse_url($url, PHP_URL_QUERY)) {
                $query = '&';
            }
            $query .= http_build_query($parameters);
        }

        header('Location: ' . $url . $query, true, $httpResponseCode);
        exit;
    }

    /**
     * Getting current url which can be used as return to url.
     *
     * @return string
     */
    protected function getCurrentUrl()
    {
        $protocol = !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';

        return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Cookie name in which we save unique client token.
     *
     * @return string
     */
    protected function getCookieName()
    {
        // Cookie name based on broker's name because there can be some brokers on same domain
        // and we need to prevent duplications.
        return 'sso_token_' . preg_replace('/[_\W]+/', '_', strtolower($this->brokerName));
    }

    /**
     * Get COOKIE value by it's name.
     *
     * @param string $cookieName
     *
     * @return string|null
     */
    protected function getCookie(string $cookieName)
    {
        if (isset($_COOKIE[$cookieName])) {
            return $_COOKIE[$cookieName];
        }

        return null;
    }
    
    /**
     * Generate request url.
     *
     * @param string $command
     * @param array $parameters
     *
     * @return string
     */
    protected function generateCommandUrl(string $command, array $parameters = [])
    {
        $query = '';
        if (!empty($parameters)) {
            $query = '?' . http_build_query($parameters);
        }

        return $this->ssoServerUrl . '/api/sso-native/' . $command . $query;
    }
}