<?php
$dbUrl = getenv('DATABASE_URL');

echo "DATABASE_URL: " . ($dbUrl ? "SET" : "NOT SET") . "<br>";

if ($dbUrl) {
    $url = parse_url($dbUrl);
    echo "Host: " . ($url['host'] ?? 'N/A') . "<br>";
    echo "User: " . ($url['user'] ?? 'N/A') . "<br>";
    echo "DB: " . ltrim($url['path'] ?? '', '/') . "<br><br>";
    
    try {
        $dsn = "mysql:host=" . $url['host'] . ";port=" . $url['port'] . ";dbname=" . ltrim($url['path'], '/');
        $pdo = new PDO($dsn, $url['user'], $url['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo "✅ DB Connected!<br><br>";
        
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables: " . implode(', ', $tables) . "<br><br>";
        
        if (in_array('pwd_records', $tables)) {
            $stmt = $pdo->query("DESCRIBE pwd_records");
            $cols = $stmt->fetchAll();
            echo "pwd_records columns:<br>";
            foreach ($cols as $c) {
                echo "- " . $c['Field'] . "<br>";
            }
        }
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>