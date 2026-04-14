<?php
/**
 * Unified MySQL PDO Connection
 */
function getPdo() {
    $dbUrl = getenv('DATABASE_URL');
    
    if ($dbUrl && strpos($dbUrl, 'mysql://') === 0) {
        $url = parse_url($dbUrl);
        $host = $url['host'] ?? 'localhost';
        $port = $url['port'] ?? 3306;
        $dbName = ltrim($url['path'] ?? '/bbpwdo', '/');
        $user = $url['user'] ?? 'root';
        $pass = $url['pass'] ?? '';
        $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    } else {
        $host = getenv('DB_HOST') ?: 'localhost';
        $port = getenv('DB_PORT') ?: 3306;
        $dbName = getenv('DB_NAME') ?: 'bbpwdo';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    }
    
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (PDOException $e) {
        return null;
    }
}

$pdo = getPdo();