<?php
require_once 'db.php';

header('Content-Type: application/json');

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM team_cards ORDER BY id ASC");
        $cards = $stmt->fetchAll();
        echo json_encode(['success' => true, 'data' => $cards]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    try {
        if ($action === 'update') {
            $stmt = $pdo->prepare("UPDATE team_cards SET name = ?, position = ? WHERE id = ?");
            $stmt->execute([
                $input['name'] ?? '',
                $input['position'] ?? '',
                $input['id']
            ]);
            echo json_encode(['success' => true, 'message' => 'Card updated!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
?>
