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
    
    $pdo->exec("DROP TABLE IF EXISTS contact_messages");
    $pdo->exec("CREATE TABLE contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("DROP TABLE IF EXISTS pwd_records");
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✅ Tables recreated with basic columns only!";
    echo "<br><br>contact_messages: id, name, email, message, created_at";
    echo "<br>pwd_records: id, last_name, first_name, middle_name, suffix, sex, age, birthdate, blood_type, civil_status, contact_number, address, pwd_id_number, issued_date, expiry_date, is_registered, created_at";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>