<?php
require_once __DIR__."/bootsatrap.php";

$host = 'localhost';
$db   = 'starstarff';
$user = 'root';
$pass = '';

$mysqli = new mysqli($host, $user, $pass, $db);

// Существование $mysqli->connect_errno говорит о том что не получилось соединиться
if ($mysqli->connect_errno)
    throw new Exception("Не удалась создать соединение с базой MySQL");

$verify = new lib\Verify($mysqli, 'users', 'i_id', 'm_mail', 's_status');
$result = $verify->setLimit(10)->run();
print_r($result);
