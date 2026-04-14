<?php
require_once 'db.php';

$password = password_hash('1985', PASSWORD_DEFAULT);
$email = 'rcabolbol@gmail.com';
$username = 'admin';

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$username, $email, $password]);
    echo json_encode(['success' => true, 'message' => 'Admin user created - ' . $username . ' / 1985']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
