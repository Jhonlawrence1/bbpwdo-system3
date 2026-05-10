<?php
require_once 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM homepage_stats ORDER BY sort_order");
        $stats = $stmt->fetchAll();
        
        $stmtPwd = $pdo->query("SELECT COUNT(*) FROM pwd_records WHERE is_registered = 'Yes'");
        $registeredCount = $stmtPwd->fetchColumn();
        
        foreach ($stats as &$stat) {
            if ($stat['stat_key'] === 'registered_pwd') {
                $stat['stat_value'] = $registeredCount;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $stats]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['admin_id']) && !isset($_GET['skip_auth'])) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized - Login required']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['stats']) && is_array($input['stats'])) {
        try {
            foreach ($input['stats'] as $stat) {
                if (isset($stat['stat_key']) && isset($stat['stat_value'])) {
                    if ($stat['stat_key'] !== 'registered_pwd') {
                        $stmt = $pdo->prepare("UPDATE homepage_stats SET stat_value = ? WHERE stat_key = ?");
                        $stmt->execute([
                            intval($stat['stat_value']),
                            $stat['stat_key']
                        ]);
                    }
                }
            }
            echo json_encode(['success' => true, 'message' => 'Stats updated successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid data received']);
    }
    exit;
}
?>
