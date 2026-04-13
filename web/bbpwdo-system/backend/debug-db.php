<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'bbpwdo';
$user = 'root';
$pass = '';

echo "Connecting to MySQL...<br>";

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to MySQL<br>";
    
    echo "Creating database if not exists...<br>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    echo "Using database...<br>";
    $pdo->exec("USE `$db`");
    
    echo "Listing tables:<br>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    foreach($tables as $t) {
        echo "- " . $t[0] . "<br>";
    }
    
    if (count($tables) == 0) {
        echo "No tables found. Creating all tables...<br>";
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("INSERT INTO users (username, password) VALUES 
            ('admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
        ");
        
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
            is_registered ENUM('Yes', 'No') DEFAULT 'No',
            employment_status VARCHAR(50),
            employment_type VARCHAR(50),
            education_elementary VARCHAR(100),
            education_highschool VARCHAR(100),
            education_college VARCHAR(100),
            education_vocational VARCHAR(100),
            disability_type TEXT,
            assistive_device TEXT,
            guardian_name VARCHAR(100),
            guardian_relationship VARCHAR(50),
            guardian_contact VARCHAR(20),
            guardian_address TEXT,
            skills TEXT,
            trainings TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
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
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS homepage_stats (
            id INT AUTO_INCREMENT PRIMARY KEY,
            stat_key VARCHAR(50) NOT NULL UNIQUE,
            stat_value INT DEFAULT 0,
            stat_label VARCHAR(100),
            stat_icon VARCHAR(50),
            sort_order INT DEFAULT 0
        )");
        
        $pdo->exec("INSERT INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES 
            ('registered_pwd', 0, 'Registered PWDs', 'fa-users', 1),
            ('programs', 50, 'Programs This Year', 'fa-calendar-check', 2),
            ('partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3),
            ('success_stories', 100, 'Success Stories', 'fa-award', 4)
        ");
        
        echo "All tables created!<br>";
    }
    
    echo "<br>Final tables:<br>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    foreach($tables as $t) {
        echo "- " . $t[0] . "<br>";
    }
    
    echo "<br>DONE! You can close this page.";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
