<?php
require_once 'db.php';

$email = $_GET['email'] ?? 'rcabolbol@gmail.com';

$stmt = $pdo->prepare("SELECT id, username, email, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    echo "User found!<br>";
    echo "Email: " . $user['email'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Password Hash: " . $user['password'] . "<br><br>";
    
    $test = password_verify('1985', $user['password']);
    echo "Test password '1985': " . ($test ? "MATCH" : "NO MATCH") . "<br";
} else {
    echo "No user found with email: $email";
}
?>