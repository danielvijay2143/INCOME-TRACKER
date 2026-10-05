<?php
require_once 'db.php';

try {
    // Generate a fresh hash for password "2143" directly on this PHP engine
    $defaultUsername = 'VIJAY';
    $defaultPassword = '2143';
    $hashedPassword  = password_hash($defaultPassword, PASSWORD_BCRYPT);

    // Disable Foreign Keys temporarily to avoid #1701 constraint errors
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $pdo->exec("TRUNCATE TABLE users;");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    // Insert user with fresh hash
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $stmt->execute([$defaultUsername, $hashedPassword]);

    echo "<h2 style='color: green;'>Success! Admin user created successfully.</h2>";
    echo "<p><strong>Username:</strong> VIJAY</p>";
    echo "<p><strong>Password:</strong> 2143</p>";
    echo "<p><a href='login.php'>Click here to go to Login Page</a></p>";

} catch (PDOException $e) {
    echo "<h2 style='color: red;'>Setup Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>