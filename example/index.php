<?php 

require __DIR__ . '/../vendor/autoload.php';

$properti = [
	'url' => 'https://sso.samarindakota.go.id', 
	'name' => 'PHPClient', 
	'secret' => 'PhIp6lvJcchPILDqmF4KpOAY0mr6HSqZeDeFx2OR'
];

// Login Script
$broker = new \Novay\SsoPhp\Services\Broker($properti);
echo $broker->getLogin();

// Logout Script
// $broker = new \Novay\SsoPhp\Services\Broker($properti);
// echo $broker->logout();