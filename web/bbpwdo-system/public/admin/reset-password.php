<?php
session_start();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../backend/db.php';
    
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || $password !== $confirm) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
            $stmt->execute([$hash, $user['id']]);
            $success = 'Password reset successfully!';
        } else {
            $error = 'Invalid or expired reset token';
        }
    }
}

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - BBPWDO</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        
        .auth-container { width: 100%; max-width: 420px; padding: 20px; }
        .auth-box { background: white; border-radius: 20px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .auth-logo { text-align: center; margin-bottom: 30px; }
        .auth-logo i { font-size: 3rem; color: #4f46e5; }
        .auth-logo h1 { font-size: 1.5rem; color: #1e1b4b; margin-top: 10px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 500; color: #374151; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #4f46e5; }
        
        .btn-auth { width: 100%; padding: 14px; background: #4f46e5; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-auth:hover { background: #4338ca; }
        
        .error-msg { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success-msg { background: #d1fae5; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <i class="fa-solid fa-key"></i>
                <h1>Reset Password</h1>
            </div>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-msg"><?php echo $success; ?></div>
                <a href="login.php" class="btn-auth" style="display: block; text-align: center; text-decoration: none;">Go to Login</a>
            <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-auth">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>