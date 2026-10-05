<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host    = 'sql211.infinityfree.com';
$db      = 'if0_43090720_income_tracker';
$user    = 'if0_43090720'; // Update with your MySQL database username
$pass    = 'RLFUvZapFl4zp4';     // Update with your MySQL database password
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
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
?>
