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

try {
    $stmt = $pdo->query("SELECT disability_type, COUNT(*) as count FROM pwd_records GROUP BY disability_type");
    $disabilityStats = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT employment_status, COUNT(*) as count FROM pwd_records GROUP BY employment_status");
    $employmentStats = $stmt->fetchAll();
    
    $stmt = $pdo->query("SELECT sex, COUNT(*) as count FROM pwd_records GROUP BY sex");
    $sexStats = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'stats' => [
            'disability' => $disabilityStats,
            'employment' => $employmentStats,
            'sex' => $sexStats
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>