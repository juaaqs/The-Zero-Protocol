<?php
// 1. Set PHP's timezone to UTC
date_default_timezone_set('UTC');

$host = 'localhost';
$db_name = 'game';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Set MySQL's timezone to UTC for this connection
    $pdo->exec("SET time_zone = '+00:00'");
    
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>