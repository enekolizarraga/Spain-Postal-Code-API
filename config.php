<?php

$dbhost = "localhost"; // Database Hostname
$dbname = "postal_code_api"; // Database Name
$user = ""; // Database Username
$password = ""; // Database User's Password

try {
    $dsn = "mysql:host=$dbhost;dbname=$dbname";
    $db = new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $e){
    echo $e->getMessage();
}
