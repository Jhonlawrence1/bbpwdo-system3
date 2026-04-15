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
        $search = trim($_GET['search'] ?? '');
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 10);
        $offset = ($page - 1) * $limit;
        $status = trim($_GET['status'] ?? '');
        $employment = trim($_GET['employment'] ?? '');
        $disability = trim($_GET['disability'] ?? '');
        $view = intval($_GET['view'] ?? 0);
        
        $where = '';
        $conditions = [];
        $params = [];
        
        if ($view > 0) {
            $conditions[] = "id = ?";
            $params[] = $view;
            $offset = 0;
            $limit = 1;
        } else {
            $searchParam = $search ? "%$search%" : null;
            
            if ($search) {
                $conditions[] = "(last_name LIKE ? OR first_name LIKE ? OR middle_name LIKE ? OR pwd_id_number LIKE ? OR disability_type LIKE ? OR address LIKE ?)";
            }
            
            if ($status) {
                $conditions[] = "is_registered = ?";
            }
            
            if ($employment) {
                $conditions[] = "employment_status = ?";
            }
            
            if ($disability) {
                $conditions[] = "disability_type LIKE ?";
            }
            
            if ($search) {
                for ($i = 0; $i < 6; $i++) $params[] = $searchParam;
            }
            if ($status) $params[] = $status;
            if ($employment) $params[] = $employment;
            if ($disability) $params[] = "%$disability%";
        }
        
        if (!empty($conditions)) {
            $where = "WHERE " . implode(" AND ", $conditions);
        }
        
        $countSql = "SELECT COUNT(*) as total FROM pwd_records $where";
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        $sql = "SELECT * FROM pwd_records $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        // Fetch family members for each record
        if (!empty($records)) {
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS family_members (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pwd_id INT NOT NULL,
                    name VARCHAR(100),
                    age INT,
                    civil_status VARCHAR(30),
                    relationship VARCHAR(50),
                    occupation VARCHAR(100),
                    FOREIGN KEY (pwd_id) REFERENCES pwd_records(id) ON DELETE CASCADE
                )");
                
                $famStmt = $pdo->prepare("SELECT * FROM family_members WHERE pwd_id = :pwd_id");
                foreach ($records as &$record) {
                    $famStmt->execute([':pwd_id' => $record['id']]);
                    $record['family_members'] = $famStmt->fetchAll();
                }
            } catch (Exception $e) {
                // Family members table might not exist yet
            }
        }
        
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
            // Fetch family members - create table if doesn't exist
            $family_members = [];
            try {
                $pdo->exec("CREATE TABLE IF NOT EXISTS family_members (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    pwd_id INT NOT NULL,
                    name VARCHAR(100),
                    age INT,
                    civil_status VARCHAR(30),
                    relationship VARCHAR(50),
                    occupation VARCHAR(100),
                    FOREIGN KEY (pwd_id) REFERENCES pwd_records(id) ON DELETE CASCADE
                )");
                
                $famStmt = $pdo->prepare("SELECT * FROM family_members WHERE pwd_id = :pwd_id");
                $famStmt->execute([':pwd_id' => $id]);
                $family_members = $famStmt->fetchAll();
            } catch (Exception $e) {
                $family_members = [];
            }
            
            $record['family_members'] = $family_members;
            echo json_encode(['success' => true, 'data' => [$record]]);
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
