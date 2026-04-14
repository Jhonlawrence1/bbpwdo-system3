<?php
header('Content-Type: application/json');
require_once 'db.php';

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
    $data = [
        'last_name' => htmlspecialchars(trim($_POST['lastName'] ?? '')),
        'first_name' => htmlspecialchars(trim($_POST['firstName'] ?? '')),
        'middle_name' => htmlspecialchars(trim($_POST['middleName'] ?? '')),
        'suffix' => htmlspecialchars(trim($_POST['suffix'] ?? '')),
        'sex' => htmlspecialchars(trim($_POST['sex'] ?? '')),
        'age' => intval($_POST['age'] ?? 0),
        'birthdate' => $_POST['birthdate'] ?? null,
        'blood_type' => htmlspecialchars(trim($_POST['bloodType'] ?? '')),
        'civil_status' => htmlspecialchars(trim($_POST['civilStatus'] ?? '')),
        'contact_number' => htmlspecialchars(trim($_POST['contactNumber'] ?? '')),
        'address' => htmlspecialchars(trim($_POST['address'] ?? '')),
        'pwd_id_number' => htmlspecialchars(trim($_POST['pwdIdNumber'] ?? '')),
        'issued_date' => $_POST['issuedDate'] ?? null,
        'expiry_date' => $_POST['expiryDate'] ?? null,
        'is_registered' => htmlspecialchars(trim($_POST['isRegistered'] ?? 'No')),
        'disability_type' => htmlspecialchars(trim($_POST['disabilityType'] ?? '')),
        'assistive_device' => htmlspecialchars(trim($_POST['assistiveDevice'] ?? '')),
        'employment_status' => htmlspecialchars(trim($_POST['employmentStatus'] ?? '')),
        'employment_type' => htmlspecialchars(trim($_POST['employmentType'] ?? '')),
        'employer_name' => htmlspecialchars(trim($_POST['employerName'] ?? '')),
        'employer_address' => htmlspecialchars(trim($_POST['employerAddress'] ?? '')),
        'education_elementary' => htmlspecialchars(trim($_POST['educationElementary'] ?? '')),
        'education_highschool' => htmlspecialchars(trim($_POST['educationHighschool'] ?? '')),
        'education_college' => htmlspecialchars(trim($_POST['educationCollege'] ?? '')),
        'education_vocational' => htmlspecialchars(trim($_POST['educationVocational'] ?? '')),
        'guardian_name' => htmlspecialchars(trim($_POST['guardianName'] ?? '')),
        'guardian_relationship' => htmlspecialchars(trim($_POST['guardianRelationship'] ?? '')),
        'guardian_contact' => htmlspecialchars(trim($_POST['guardianContact'] ?? '')),
        'guardian_address' => htmlspecialchars(trim($_POST['guardianAddress'] ?? '')),
        'skills' => htmlspecialchars(trim($_POST['skills'] ?? '')),
        'trainings' => htmlspecialchars(trim($_POST['trainings'] ?? ''))
    ];
    
    if (empty($data['last_name']) || empty($data['first_name']) || empty($data['sex']) || empty($data['age']) || empty($data['birthdate']) || empty($data['address'])) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
    
    $fields = array_keys($data);
    $placeholders = array_fill(0, count($fields), '?');
    
    $sql = "INSERT INTO pwd_records (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    
    echo json_encode(['success' => true, 'message' => 'Registration submitted successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>