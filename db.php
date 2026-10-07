<?php
$host    = '127.0.0.1'; // Or 'localhost'
$db      = 'income_tracker'; // MUST match your database name in phpMyAdmin
$user    = 'root';           // Default XAMPP username
$pass    = '';               // Default XAMPP password (leave empty)
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Temporarily uncomment line below while debugging to see exact MySQL error:
    // die("Database connection failed: " . $e->getMessage());
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>