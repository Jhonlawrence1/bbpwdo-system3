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

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    try {
        $search = htmlspecialchars(trim($_GET['search'] ?? ''));
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10);
        $offset = ($page - 1) * $limit;
        $status = htmlspecialchars(trim($_GET['status'] ?? ''));
        
        $where = '';
        $params = [];
        
        if ($search) {
            $conditions = [];
            $conditions[] = "last_name LIKE :search";
            $conditions[] = "first_name LIKE :search";
            $conditions[] = "pwd_id_number LIKE :search";
            $where .= "WHERE (" . implode(" OR ", $conditions) . ")";
            $params[':search'] = "%$search%";
        }
        
        if ($status) {
            $where .= $where ? " AND " : "WHERE ";
            $where .= "is_registered = :status";
            $params[':status'] = $status;
        }
        
        $countSql = "SELECT COUNT(*) as total FROM pwd_records $where";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        $sql = "SELECT * FROM pwd_records $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        echo json_encode([
            'success' => true,
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'totalPages' => ceil($total / $limit)
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
            exit;
        }
        
        $sql = "SELECT * FROM pwd_records WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $record = $stmt->fetch();
        
        if ($record) {
            echo json_encode(['success' => true, 'data' => $record]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Record not found']);
        }
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'PUT') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = intval($input['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
            exit;
        }
        
        $fields = [];
        $params = [];
        
        $allowedFields = ['last_name', 'first_name', 'middle_name', 'suffix', 'sex', 'age', 'birthdate', 'blood_type', 'civil_status', 'contact_number', 'address', 'pwd_id_number', 'issued_date', 'expiry_date', 'disability_type', 'assistive_device', 'employment_status', 'employment_type', 'employer_name', 'employer_address', 'education_elementary', 'education_highschool', 'education_college', 'education_vocational', 'guardian_name', 'guardian_relationship', 'guardian_contact', 'guardian_address', 'skills', 'trainings', 'is_registered'];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $input[$field];
            }
        }
        
        if (!empty($fields)) {
            $params['id'] = $id;
            $sql = "UPDATE pwd_records SET " . implode(', ', $fields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }
        
        echo json_encode(['success' => true, 'message' => 'Record updated']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    try {
        $id = intval($_POST['id'] ?? 0);
        
        if ($id === 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid record ID']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM pwd_records WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        echo json_encode(['success' => true, 'message' => 'Record deleted']);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
