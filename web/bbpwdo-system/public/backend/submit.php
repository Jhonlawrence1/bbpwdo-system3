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
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        
        if (empty($name) || empty($email) || empty($message)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
            exit;
        }
        
        $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $message]);
        
        echo json_encode(['success' => true, 'message' => 'Message sent successfully!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

try {
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
    
    if (empty($last_name) || empty($first_name) || empty($sex) || empty($age) || empty($birthdate) || empty($address)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
        exit;
    }
    
    $stmt = $pdo->prepare("INSERT INTO pwd_records (
        last_name, first_name, middle_name, suffix, sex, age, birthdate, blood_type,
        civil_status, contact_number, address, pwd_id_number, issued_date, expiry_date,
        is_registered
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $stmt->execute([
        $last_name, $first_name, $middle_name, $suffix, $sex, $age, $birthdate, $blood_type,
        $civil_status, $contact_number, $address, $pwd_id_number, $issued_date, $expiry_date,
        $is_registered
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Registration submitted successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>