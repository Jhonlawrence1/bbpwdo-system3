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
    
    $dateFields = ['birthdate', 'issued_date', 'expiry_date'];
    
    $fields = [
        'last_name', 'first_name', 'middle_name', 'suffix', 'sex', 'age', 'birthdate',
        'blood_type', 'civil_status', 'contact_number', 'address', 'pwd_id_number',
        'issued_date', 'expiry_date', 'is_registered',
        'disability_type', 'assistive_device',
        'employment_status', 'employment_type', 'employer_name', 'employer_address',
        'education_elementary', 'education_highschool', 'education_college', 'education_vocational',
        'guardian_name', 'guardian_relationship', 'guardian_contact', 'guardian_address',
        'skills', 'trainings'
    ];
    
    $data = [];
    foreach ($fields as $f) {
        $val = $_POST[$f] ?? '';
        if (in_array($f, $dateFields)) {
            $data[$f] = !empty($val) ? $val : null;
        } else {
            $data[$f] = $val;
        }
    }
    
    $cols = implode(', ', array_keys($data));
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    $sql = "INSERT INTO pwd_records ($cols) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($data));
    
    echo json_encode(['success' => true, 'message' => 'Registration submitted successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>