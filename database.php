<?php

$host = "localhost";
$username = "root";
$password = "secret";
$dbname = "registrator_ku";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    $e->getMessage();
}
