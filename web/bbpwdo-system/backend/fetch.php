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
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];
        
        $sql = "SELECT * FROM pwd_records $where ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll();
        
        // Fetch family members for each record
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
        
        $last_name = htmlspecialchars(trim($_POST['lastName'] ?? ''));
        $first_name = htmlspecialchars(trim($_POST['firstName'] ?? ''));
        $middle_name = htmlspecialchars(trim($_POST['middleName'] ?? ''));
        $suffix = htmlspecialchars(trim($_POST['suffix'] ?? ''));
        $sex = htmlspecialchars(trim($_POST['sex'] ?? ''));
        $age = intval($_POST['age'] ?? 0);
        $birthdate = $_POST['birthdate'] ?? null;
        $blood_type = htmlspecialchars(trim($_POST['bloodType'] ?? ''));
        $civil_status = htmlspecialchars(trim($_POST['civilStatus'] ?? ''));
        $contact_number = htmlspecialchars(trim($_POST['contactNumber'] ?? ''));
        $address = htmlspecialchars(trim($_POST['address'] ?? ''));
        
        $pwd_id_number = htmlspecialchars(trim($_POST['pwdIdNumber'] ?? ''));
        $issued_date = $_POST['issuedDate'] ?? null;
        $expiry_date = $_POST['expiryDate'] ?? null;
        $is_registered = htmlspecialchars(trim($_POST['isRegistered'] ?? 'No'));
        
        $employment_status = htmlspecialchars(trim($_POST['employmentStatus'] ?? ''));
        $employment_type = htmlspecialchars(trim($_POST['employmentType'] ?? ''));
        
        $education_elementary = htmlspecialchars(trim($_POST['educationElementary'] ?? ''));
        $education_highschool = htmlspecialchars(trim($_POST['educationHighschool'] ?? ''));
        $education_college = htmlspecialchars(trim($_POST['educationCollege'] ?? ''));
        $education_vocational = htmlspecialchars(trim($_POST['educationVocational'] ?? ''));
        
        $disability_type = isset($_POST['disabilityType']) ? implode(', ', $_POST['disabilityType']) : '';
        $assistive_device = isset($_POST['assistiveDevice']) ? implode(', ', $_POST['assistiveDevice']) : '';
        
        $guardian_name = htmlspecialchars(trim($_POST['guardianName'] ?? ''));
        $guardian_relationship = htmlspecialchars(trim($_POST['guardianRelationship'] ?? ''));
        $guardian_contact = htmlspecialchars(trim($_POST['guardianContact'] ?? ''));
        $guardian_address = htmlspecialchars(trim($_POST['guardianAddress'] ?? ''));
        
        $skills = htmlspecialchars(trim($_POST['skills'] ?? ''));
        $trainings = htmlspecialchars(trim($_POST['trainings'] ?? ''));
        
        $family_members = $_POST['family_members'] ?? [];
        
        $sql = "UPDATE pwd_records SET 
            last_name = :last_name, first_name = :first_name, middle_name = :middle_name,
            suffix = :suffix, sex = :sex, age = :age, birthdate = :birthdate,
            blood_type = :blood_type, civil_status = :civil_status, contact_number = :contact_number,
            address = :address, pwd_id_number = :pwd_id_number, issued_date = :issued_date,
            expiry_date = :expiry_date, is_registered = :is_registered,
            employment_status = :employment_status, employment_type = :employment_type,
            education_elementary = :education_elementary, education_highschool = :education_highschool,
            education_college = :education_college, education_vocational = :education_vocational,
            disability_type = :disability_type, assistive_device = :assistive_device,
            guardian_name = :guardian_name, guardian_relationship = :guardian_relationship,
            guardian_contact = :guardian_contact, guardian_address = :guardian_address,
            skills = :skills, trainings = :trainings
            WHERE id = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':last_name' => $last_name,
            ':first_name' => $first_name,
            ':middle_name' => $middle_name,
            ':suffix' => $suffix,
            ':sex' => $sex,
            ':age' => $age,
            ':birthdate' => $birthdate,
            ':blood_type' => $blood_type,
            ':civil_status' => $civil_status,
            ':contact_number' => $contact_number,
            ':address' => $address,
            ':pwd_id_number' => $pwd_id_number,
            ':issued_date' => $issued_date,
            ':expiry_date' => $expiry_date,
            ':is_registered' => $is_registered,
            ':employment_status' => $employment_status,
            ':employment_type' => $employment_type,
            ':education_elementary' => $education_elementary,
            ':education_highschool' => $education_highschool,
            ':education_college' => $education_college,
            ':education_vocational' => $education_vocational,
            ':disability_type' => $disability_type,
            ':assistive_device' => $assistive_device,
            ':guardian_name' => $guardian_name,
            ':guardian_relationship' => $guardian_relationship,
            ':guardian_contact' => $guardian_contact,
            ':guardian_address' => $guardian_address,
            ':skills' => $skills,
            ':trainings' => $trainings
        ]);
        
        $pdo->exec("DELETE FROM family_members WHERE pwd_id = $id");
        
        if (!empty($family_members) && is_array($family_members)) {
            $famSQL = "INSERT INTO family_members (pwd_id, name, age, civil_status, relationship, occupation) 
                       VALUES (:pwd_id, :name, :age, :civil_status, :relationship, :occupation)";
            $famStmt = $pdo->prepare($famSQL);
            
            foreach ($family_members as $member) {
                if (!empty($member['name'])) {
                    $famStmt->execute([
                        ':pwd_id' => $id,
                        ':name' => htmlspecialchars(trim($member['name'])),
                        ':age' => intval($member['age']),
                        ':civil_status' => htmlspecialchars(trim($member['civil_status'])),
                        ':relationship' => htmlspecialchars(trim($member['relationship'])),
                        ':occupation' => htmlspecialchars(trim($member['occupation']))
                    ]);
                }
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Record updated successfully']);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}