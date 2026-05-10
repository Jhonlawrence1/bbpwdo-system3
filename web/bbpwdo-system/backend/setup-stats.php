<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = 'localhost';
$db = 'bbpwdo';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db`");
    $pdo->exec("USE `$db`");
    
    $pdo->exec("DROP TABLE IF EXISTS homepage_stats");
    
    $pdo->exec("CREATE TABLE homepage_stats (
        id INT AUTO_INCREMENT PRIMARY KEY,
        stat_key VARCHAR(50) NOT NULL UNIQUE,
        stat_value INT DEFAULT 0,
        stat_label VARCHAR(100),
        stat_icon VARCHAR(50),
        sort_order INT DEFAULT 0
    )");
    
    $pdo->exec("INSERT INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES 
        ('registered_pwd', 0, 'Registered PWDs', 'fa-users', 1),
        ('programs', 50, 'Programs This Year', 'fa-calendar-check', 2),
        ('partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3),
        ('success_stories', 100, 'Success Stories', 'fa-award', 4)
    ");
    
    echo "SUCCESS! Table created. Check homepage_stats table.";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
?>
