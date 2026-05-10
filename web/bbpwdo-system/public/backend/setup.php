<?php
header('Content-Type: text/html');

$dbUrl = getenv('DATABASE_URL');
echo "Setting up all tables...<br><br>";

if (!$dbUrl) {
    echo "❌ DATABASE_URL not set!";
    exit;
}

try {
    $url = parse_url($dbUrl);
    $dsn = "mysql:host=" . $url['host'] . ";port=" . $url['port'] . ";dbname=" . ltrim($url['path'], '/');
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Connected!<br><br>";
    
    $pdo->exec("DROP TABLE IF EXISTS pwd_records");
    $pdo->exec("DROP TABLE IF EXISTS contact_messages");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
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
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS homepage_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stat_key VARCHAR(50) NOT NULL UNIQUE,
        stat_value INT DEFAULT 0,
        stat_label VARCHAR(100),
        stat_icon VARCHAR(50),
        sort_order INT DEFAULT 0
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS team_cards (
        id INT AUTO_INCREMENT PRIMARY KEY,
        card_key VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100),
        position VARCHAR(100)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(200),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✅ All tables created with full columns!<br><br>";
    
    $hash = password_hash('1985', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password = ?")
        ->execute(['admin', 'rcabolbol@gmail.com', $hash, $hash]);
    echo "✅ Admin user created/updated<br>";
    
    $stats = [
        ['registered_pwd', 0, 'Registered PWDs', 'fa-users', 1],
        ['programs', 50, 'Programs This Year', 'fa-calendar-check', 2],
        ['partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3],
        ['success_stories', 100, 'Success Stories', 'fa-award', 4]
    ];
    
    foreach ($stats as $s) {
        $pdo->prepare("INSERT IGNORE INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES (?, ?, ?, ?, ?)")
            ->execute($s);
    }
    echo "✅ Stats data added<br>";
    
    $team = [
        ['head', 'JOYCE DAANG', 'Organization Head'],
        ['secretary', '---', 'Secretary'],
        ['treasurer', '---', 'Treasurer'],
        ['coordinator', 'RODELYN C. CABOLBOL', 'Program Coordinator']
    ];
    
    foreach ($team as $t) {
        $pdo->prepare("INSERT IGNORE INTO team_cards (card_key, name, position) VALUES (?, ?, ?)")->execute($t);
    }
    echo "✅ Team data added<br><br>";
    
    echo "<strong>✅ SETUP COMPLETE!</strong><br><br>";
    echo "All columns added to pwd_records and contact_messages!<br>";
    echo "Login at: <a href='/admin/login.php'>/admin/login.php</a><br>";
    echo "Email: rcabolbol@gmail.com<br>";
    echo "Password: 1985";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>