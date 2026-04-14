<?php
require_once 'db.php';

$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100),
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
";

$pdo->exec($sql);

$password = password_hash('1985', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->execute(['admin', 'rcabolbol@gmail.com', $password]);

echo "Done! Table created and admin user added.<br>";
echo "Login with: rcabolbol@gmail.com / 1985";
?>