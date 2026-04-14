<?php
header('Content-Type: text/html');

$dbUrl = getenv('DATABASE_URL');
echo "DATABASE_URL: " . ($dbUrl ? "SET" : "NOT SET") . "<br><br>";

if (!$dbUrl) {
    echo "❌ DATABASE_URL not set in environment!";
    exit;
}

try {
    $url = parse_url($dbUrl);
    $dsn = "mysql:host=" . $url['host'] . ";port=" . $url['port'] . ";dbname=" . ltrim($url['path'], '/');
    $pdo = new PDO($dsn, $url['user'], $url['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "✅ Connected to MySQL!<br><br>";
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100),
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "✅ Table 'users' created/verified<br>";
    
    $hash = password_hash('1985', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE password = ?");
    $stmt->execute(['admin', 'rcabolbol@gmail.com', $hash, $hash]);
    echo "✅ Admin user created/updated<br><br>";
    
    $stmt = $pdo->query("SELECT id, username, email FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Total users: " . count($users) . "<br>";
    foreach ($users as $u) {
        echo "- ID: " . $u['id'] . ", Email: " . $u['email'] . ", Username: " . $u['username'] . "<br>";
    }
    
    echo "<br><strong>Login now:</strong><br>";
    echo "Email: rcabolbol@gmail.com<br>";
    echo "Password: 1985";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
?>