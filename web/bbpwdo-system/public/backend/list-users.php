<?php
require_once '../../includes/db.php';

if (!$pdo) {
    echo "DB not connected!";
    exit;
}

$stmt = $pdo->query("SELECT id, username, email FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Users in database: " . count($users) . "<br>";
foreach ($users as $u) {
    echo "- " . $u['email'] . " (" . $u['username'] . ")<br>";
}
?>