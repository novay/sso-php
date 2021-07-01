<?php 

require __DIR__ . '/../vendor/autoload.php';

// Get User Information
// Notes: Code, Uid, Pwd is getting after Callback

$properti = [
	'url' => 'https://sso.samarindakota.go.id', 
	'name' => 'PHPClient', 
	'secret' => 'PhIp6lvJcchPILDqmF4KpOAY0mr6HSqZeDeFx2OR'
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

exit();