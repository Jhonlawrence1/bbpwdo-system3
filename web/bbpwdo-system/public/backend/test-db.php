<?php
require_once __DIR__ . '/../../includes/db.php';

echo json_encode([
    'success' => true,
    'message' => 'DB Connected!',
    'host' => 'Host: ' . ($pdo->getAttribute(PDO::ATTR_SERVER_INFO) ?? 'connected')
]);
?>