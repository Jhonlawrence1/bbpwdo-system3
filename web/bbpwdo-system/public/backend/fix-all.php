<?php
$dbUrl = getenv('DATABASE_URL');

if (!$dbUrl) {
    $dbUrl = 'mysql://root:lcVREDwKUblqHHhXVgcrEWCXhNjdEOCp@monorail.proxy.rlwy.net:19171/railway';
}

$url = parse_url($dbUrl);
$host = $url['host'];
$port = $url['port'];
$dbName = ltrim($url['path'], '/');
$user = $url['user'];
$pass = $url['pass'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS pwd_records (
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
        disability_type VARCHAR(200),
        assistive_device VARCHAR(200),
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
        guardian_contact VARCHAR(50),
        guardian_address TEXT,
        skills TEXT,
        trainings TEXT,
        is_registered VARCHAR(10) DEFAULT 'No',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Table pwd_records created/updated<br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(100),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "Table contact_messages created/updated<br>";
    
    $password = password_hash('1985', PASSWORD_DEFAULT);
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $pdo->exec("INSERT IGNORE INTO users (username, email, password) VALUES ('admin', 'rcabolbol@gmail.com', '$password')");
    echo "Admin user created<br>";
    
    echo "<br><strong>SUCCESS! All tables fixed.</strong>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
