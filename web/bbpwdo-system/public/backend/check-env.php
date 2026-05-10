<?php
echo "DATABASE_URL: " . (getenv('DATABASE_URL') ?: 'NOT SET') . "<br>";

$dbUrl = getenv('DATABASE_URL');
if ($dbUrl && strpos($dbUrl, 'mysql://') === 0) {
    $url = parse_url($dbUrl);
    echo "Host: " . ($url['host'] ?? 'N/A') . "<br>";
    echo "Port: " . ($url['port'] ?? 'N/A') . "<br>";
    echo "User: " . ($url['user'] ?? 'N/A') . "<br>";
    echo "DB: " . ltrim($url['path'] ?? '', '/') . "<br>";
    
    try {
        $dsn = "mysql:host=" . ($url['host'] ?? 'localhost') . ";port=" . ($url['port'] ?? 3306) . ";dbname=" . ltrim($url['path'] ?? '', '/') . ";charset=utf8mb4";
        $pdo = new PDO($dsn, $url['user'] ?? 'root', $url['pass'] ?? '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "✅ DB Connected!";
        
        $stmt = $pdo->query("SELECT COUNT(*) as cnt FROM users");
        $result = $stmt->fetch();
        echo "<br>Users count: " . $result['cnt'];
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
} else {
    echo "❌ DATABASE_URL not set or invalid format";
}
?>