<?php
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "chatbot_db";

try {
    // Establishing a secure PDO connection with error exceptions and emulated prepares disabled
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Database Connection Critical Failure: " . $e->getMessage());
}
?>