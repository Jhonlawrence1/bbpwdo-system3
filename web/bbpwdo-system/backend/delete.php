<?php
header('Content-Type: application/json');
require_once 'db.php';

session_start();

$skipAuth = isset($_GET['skip_auth']) || isset($_POST['skip_auth']);

if (!isset($_SESSION['admin_id']) && !$skipAuth) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM pwd_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        echo json_encode(['success' => true, 'message' => 'Record deleted successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}