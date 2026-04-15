<?php
/**
 * Database connection - MySQL PDO
 * Supports DATABASE_URL, MYSQL_URL, or individual DB_* / MYSQL_* env vars.
 */
function getPdo() {
    $dbUrl = getenv('DATABASE_URL') ?: getenv('MYSQL_URL') ?: getenv('JAWSDB_MARIA_URL');

    if ($dbUrl && strpos($dbUrl, 'mysql://') === 0) {
        $url = parse_url($dbUrl);
        $host   = $url['host'] ?? 'localhost';
        $port   = $url['port'] ?? 3306;
        $dbName = ltrim($url['path'] ?? '/railway', '/');
        $user   = $url['user'] ?? 'root';
        $pass   = $url['pass'] ?? '';
        $dsn    = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    } else {
        $host   = getenv('DB_HOST')     ?: getenv('MYSQL_HOST')     ?: 'localhost';
        $port   = getenv('DB_PORT')     ?: getenv('MYSQL_PORT')     ?: 3306;
        $dbName = getenv('DB_NAME')     ?: getenv('MYSQL_DB')       ?: 'railway';
        $user   = getenv('DB_USER')     ?: getenv('MYSQL_USER')     ?: 'root';
        $pass   = getenv('DB_PASS')     ?: getenv('MYSQL_PASSWORD') ?: '';
        $dsn    = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4";
    }

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

$pdo = getPdo();