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
    
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS assistive_device VARCHAR(255)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS employment_type VARCHAR(50)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS education_elementary VARCHAR(100)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS education_highschool VARCHAR(100)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS education_college VARCHAR(100)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS education_vocational VARCHAR(100)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS guardian_name VARCHAR(100)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS guardian_relationship VARCHAR(50)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS guardian_contact VARCHAR(20)");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS guardian_address TEXT");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS skills TEXT");
    $pdo->exec("ALTER TABLE pwd_records ADD COLUMN IF NOT EXISTS trainings TEXT");
    
    $pdo->exec("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS phone VARCHAR(50)");
    $pdo->exec("ALTER TABLE contact_messages ADD COLUMN IF NOT EXISTS subject VARCHAR(200)");
    
    echo "✅ All missing columns added!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>