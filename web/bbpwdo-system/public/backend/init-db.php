<?php
require_once 'db.php';

$tables = [
    'homepage_stats' => "CREATE TABLE IF NOT EXISTS homepage_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stat_key VARCHAR(50) NOT NULL UNIQUE,
        stat_value INT DEFAULT 0,
        stat_label VARCHAR(100),
        stat_icon VARCHAR(50),
        sort_order INT DEFAULT 0
    )",
    
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    'pwd_records' => "CREATE TABLE IF NOT EXISTS pwd_records (
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
        disability_type VARCHAR(100),
        cause_of_disability VARCHAR(100),
        employer_name VARCHAR(100),
        employer_address TEXT,
        employment_status VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_registered VARCHAR(10) DEFAULT 'No'
    )",
    
    'team_cards' => "CREATE TABLE IF NOT EXISTS team_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_key VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100),
        position VARCHAR(100)
    )",
    
    'contact_messages' => "CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )"
];

$inserts = [
    "INSERT IGNORE INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES ('registered_pwd', 0, 'Registered PWDs', 'fa-users', 1)",
    "INSERT IGNORE INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES ('programs', 50, 'Programs This Year', 'fa-calendar-check', 2)",
    "INSERT IGNORE INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES ('partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3)",
    "INSERT IGNORE INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES ('success_stories', 100, 'Success Stories', 'fa-award', 4)",
    "INSERT IGNORE INTO team_cards (card_key, name, position) VALUES ('head', 'JOYCE DAANG', 'Organization Head')",
    "INSERT IGNORE INTO team_cards (card_key, name, position) VALUES ('secretary', '---', 'Secretary')",
    "INSERT IGNORE INTO team_cards (card_key, name, position) VALUES ('treasurer', '---', 'Treasurer')",
    "INSERT IGNORE INTO team_cards (card_key, name, position) VALUES ('coordinator', 'RODELYN C. CABOLBOL', 'Program Coordinator')"
];

try {
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    
    foreach ($inserts as $sql) {
        $pdo->exec($sql);
    }
    
    echo json_encode(['success' => true, 'message' => 'Database initialized']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
