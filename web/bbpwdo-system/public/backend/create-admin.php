<?php
require_once 'db.php';

$password = password_hash('1985', PASSWORD_DEFAULT);
$email = 'rcabolbol@gmail.com';

try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->execute(['admin', $email, $password]);
    echo json_encode(['success' => true, 'message' => 'Admin user created']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
