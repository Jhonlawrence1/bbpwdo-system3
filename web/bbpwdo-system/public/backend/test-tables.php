<?php
$dbUrl = getenv('DATABASE_URL');

if (!$dbUrl) {
    echo "DATABASE_URL not set!";
    exit;
}

$url = parse_url($dbUrl);
$dsn = "mysql:host=" . $url['host'] . ";port=" . $url['port'] . ";dbname=" . ltrim($url['path'], '/');

try {
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ DB Connected!<br><br>";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables: " . implode(', ', $tables) . "<br><br>";
    
    if (in_array('pwd_records', $tables)) {
        echo "✅ pwd_records exists<br>";
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM pwd_records");
        $result = $stmt->fetch();
        echo "Records: " . $result['cnt'] . "<br>";
    } else {
        echo "❌ pwd_records NOT found<br>";
    }
    
    if (in_array('contact_messages', $tables)) {
        echo "✅ contact_messages exists<br>";
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM contact_messages");
        $result = $stmt->fetch();
        echo "Messages: " . $result['cnt'] . "<br>";
    } else {
        echo "❌ contact_messages NOT found<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>