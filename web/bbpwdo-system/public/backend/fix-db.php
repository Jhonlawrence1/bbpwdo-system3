<?php
header('Content-Type: text/html');

$dbUrl = getenv('DATABASE_URL');
if (!$dbUrl) {
    echo "DATABASE_URL not set!";
    exit;
}

try {
    $url = parse_url($dbUrl);
    $dsn = "mysql:host=" . $url['host'] . ";port=" . $url['port'] . ";dbname=" . ltrim($url['path'], '/');
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $pdo->exec("DROP TABLE IF EXISTS pwd_records");
    $pdo->exec("DROP TABLE IF EXISTS contact_messages");
    
    $pdo->exec("CREATE TABLE pwd_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        last_name VARCHAR(100) NOT NULL,
        first_name VARCHAR(100) NOT NULL,
        middle_name VARCHAR(100),
        suffix VARCHAR(20),
        sex VARCHAR(20),
        age INT,
        birthdate DATE,
        blood_type VARCHAR(10),
        civil_status VARCHAR(30),
        contact_number VARCHAR(20),
        address TEXT,
        pwd_id_number VARCHAR(50),
        issued_date DATE,
        expiry_date DATE,
        is_registered VARCHAR(10) DEFAULT 'No',
        disability_type VARCHAR(255),
        assistive_device VARCHAR(255),
        employment_status VARCHAR(50),
        employment_type VARCHAR(50),
        employer_name VARCHAR(100),
        employer_address TEXT,
        education_elementary VARCHAR(100),
        education_highschool VARCHAR(100),
        education_college VARCHAR(100),
        education_vocational VARCHAR(100),
        guardian_name VARCHAR(100),
        guardian_relationship VARCHAR(50),
        guardian_contact VARCHAR(20),
        guardian_address TEXT,
        skills TEXT,
        trainings TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("CREATE TABLE contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(200),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✅ Tables recreated with ALL columns!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>