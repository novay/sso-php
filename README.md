# SSO-PHP

SSO-Samarinda Client for PHP Language.

### Install
```bash
composer require novay/sso-php
```

### Contoh Penggunaan

Untuk Login:
```php
<?php
// Autoloader Composer
require_once __DIR__ . '/vendor/autoload.php';

// Untuk Login
$properti = [
    'url' => 'https://sso.samarindakota.go.id', 
    'name' => 'XXX', 
    'secret' => 'XXX'
];

// Login Script
$broker = new \Novay\SsoPhp\Services\Broker($properti);
echo $broker->getLogin();

```

Untuk Get User Information:
```php
<?php 

require __DIR__ . '/../vendor/autoload.php';

$properti = [
    'url' => 'https://sso.samarindakota.go.id', 
    'name' => 'XXX', 
    'secret' => 'XXX'
];

$broker = new \Novay\SsoPhp\Services\Broker($properti);
$user = $broker->getUser($_GET['code'], $_GET['uid'], $_GET['pwd']);

echo $user['id'];
echo $user['photo'];
echo $user['name'];
echo $user['email'];
echo $user['phone'];
echo $user['address'];
echo $user['gender'];
echo $user['date_birth'];
echo $user['number_id'];
echo $user['type_id'];
echo $user['level'];

// Silahkan lakukan penyimpanan session atau pembagian hak akses mandiri kedalam aplikasi client
```

Untuk Logout:
```php
<?php
// Autoloader Composer
require_once __DIR__ . '/vendor/autoload.php';

// Untuk Login
$properti = [
    'url' => 'https://sso.samarindakota.go.id', 
    'name' => 'XXX', 
    'secret' => 'XXX'
];

// Login Script
$broker = new \Novay\SsoPhp\Services\Broker($properti);
echo $broker->logout();

```