<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
ob_start();

require_once 'db.php';

$output = ob_get_clean();

if (!$pdo) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (isset($_POST['subject']) || isset($_POST['message'])) {
    try {
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        
        if (empty($name) || empty($email) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        
        echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

try {
    // Auto-create tables if they don't exist
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
    
    $last_name = $_POST['lastName'] ?? '';
    $first_name = $_POST['firstName'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $age = intval($_POST['age'] ?? 0);
    $birthdate = $_POST['birthdate'] ?? '';
    $address = $_POST['address'] ?? '';
    
    if (empty($last_name) || empty($first_name) || empty($sex) || empty($age) || empty($birthdate) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
    
    $disabilityTypes = '';
    if (isset($_POST['disabilityType'])) {
        if (is_array($_POST['disabilityType'])) {
            $disabilityTypes = implode(', ', $_POST['disabilityType']);
        } else {
            $disabilityTypes = $_POST['disabilityType'];
        }
    }
    
    $otherDisability = htmlspecialchars(trim($_POST['otherDisability'] ?? ''));
    if (!empty($otherDisability)) {
        $disabilityTypes = str_replace('Others -', 'Others - ' . $otherDisability, $disabilityTypes);
    }
    
    $assistiveDevices = '';
    if (isset($_POST['assistiveDevice'])) {
        if (is_array($_POST['assistiveDevice'])) {
            $assistiveDevices = implode(', ', $_POST['assistiveDevice']);
        } else {
            $assistiveDevices = $_POST['assistiveDevice'];
        }
    }
    
    $data = [
        'last_name' => $last_name,
        'first_name' => $first_name,
        'middle_name' => $_POST['middleName'] ?? '',
        'suffix' => $_POST['suffix'] ?? '',
        'sex' => $sex,
        'age' => $age,
        'birthdate' => !empty($birthdate) ? $birthdate : null,
        'blood_type' => $_POST['bloodType'] ?? '',
        'civil_status' => $_POST['civilStatus'] ?? '',
        'contact_number' => $_POST['contactNumber'] ?? '',
        'address' => $address,
        'pwd_id_number' => $_POST['pwdIdNumber'] ?? '',
        'issued_date' => !empty($_POST['issuedDate']) ? $_POST['issuedDate'] : null,
        'expiry_date' => !empty($_POST['expiryDate']) ? $_POST['expiryDate'] : null,
        'is_registered' => $_POST['isRegistered'] ?? 'No',
        'disability_type' => $disabilityTypes,
        'assistive_device' => $assistiveDevices,
        'employment_status' => $_POST['employmentStatus'] ?? '',
        'employment_type' => $_POST['employmentType'] ?? '',
        'employer_name' => $_POST['employerName'] ?? '',
        'employer_address' => $_POST['employerAddress'] ?? '',
        'education_elementary' => $_POST['educationElementary'] ?? '',
        'education_highschool' => $_POST['educationHighschool'] ?? '',
        'education_college' => $_POST['educationCollege'] ?? '',
        'education_vocational' => $_POST['educationVocational'] ?? '',
        'guardian_name' => $_POST['guardianName'] ?? '',
        'guardian_relationship' => $_POST['guardianRelationship'] ?? '',
        'guardian_contact' => $_POST['guardianContact'] ?? '',
        'guardian_address' => $_POST['guardianAddress'] ?? '',
        'skills' => $_POST['skills'] ?? '',
        'trainings' => $_POST['trainings'] ?? ''
    ];
    
    $cols = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO pwd_records ($cols) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    
    $pwd_id = $pdo->lastInsertId();
    
    $family_members = [];
    if (isset($_POST['family_members'])) {
        $family_members = json_decode($_POST['family_members'], true) ?? [];
    }
    
    if (!empty($family_members) && is_array($family_members)) {
        $famSQL = "INSERT INTO family_members (pwd_id, name, age, civil_status, relationship, occupation) 
                   VALUES (:pwd_id, :name, :age, :civil_status, :relationship, :occupation)";
        $famStmt = $pdo->prepare($famSQL);
        
        foreach ($family_members as $member) {
            if (!empty($member['name'])) {
                $famStmt->execute([
                    ':pwd_id' => $pwd_id,
                    ':name' => htmlspecialchars(trim($member['name'])),
                    ':age' => intval($member['age']),
                    ':civil_status' => htmlspecialchars(trim($member['civil_status'])),
                    ':relationship' => htmlspecialchars(trim($member['relationship'])),
                    ':occupation' => htmlspecialchars(trim($member['occupation']))
                ]);
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Registration submitted successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>