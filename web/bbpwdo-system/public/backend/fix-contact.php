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
        phone VARCHAR(50),
        subject VARCHAR(200),
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    echo "✅ contact_messages table recreated with subject column!";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>