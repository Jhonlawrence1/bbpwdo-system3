<?php
session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../backend/db.php';
    
    if (isset($_POST['login'])) {
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        
        if (empty($email) || empty($password)) {
            $error = 'Please enter email and password';
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        }
    }

    if (isset($_POST['forgot'])) {
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        
        if (empty($email)) {
            $error = 'Please enter your email';
        } else {
            try {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $code = rand(100000, 999999);
                    $_SESSION['reset_code'] = $code;
                    $_SESSION['reset_email'] = $email;
                    
                    $success = "Your verification code is: <strong style='font-size:1.5rem;'>$code</strong><br><small>Enter this code to reset your password.</small>";
                } else {
                    $error = 'Email not found';
                }
            } catch (Exception $e) {
                $error = 'Error: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_POST['verify_code'])) {
        $code = trim($_POST['code'] ?? '');
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($code != ($_SESSION['reset_code'] ?? '')) {
            $error = 'Invalid verification code';
        } elseif (empty($newPassword) || $newPassword != $confirmPassword) {
            $error = 'Passwords do not match';
        } elseif (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            $email = $_SESSION['reset_email'] ?? '';
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hash, $email]);
            
            unset($_SESSION['reset_code'], $_SESSION['reset_email']);
            $success = "Password has been reset successfully!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - BBPWDO</title>
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
        
        .auth-tabs { display: flex; margin-bottom: 30px; border-bottom: 2px solid #e5e7eb; }
        .auth-tabs button { flex: 1; padding: 12px; border: none; background: none; cursor: pointer; font-weight: 600; color: #6b7280; border-bottom: 2px solid transparent; margin-bottom: -2px; }
        .auth-tabs button.active { color: #4f46e5; border-bottom-color: #4f46e5; }
        
        .auth-form { display: none; }
        .auth-form.active { display: block; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.9rem; font-weight: 500; color: #374151; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 14px; border: 1px solid #e5e7eb; border-radius: 10px; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #4f46e5; }
        
        .btn-auth { width: 100%; padding: 14px; background: #4f46e5; color: white; border: none; border-radius: 10px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; }
        .btn-auth:hover { background: #4338ca; }
        
        .error-msg { background: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success-msg { background: #d1fae5; color: #059669; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; word-break: break-all; font-size: 0.85rem; }
        
        .forgot-link { text-align: center; margin-top: 15px; }
        .forgot-link a { color: #4f46e5; text-decoration: none; font-size: 0.9rem; }
        
        .back-home { text-align: center; margin-top: 20px; }
        .back-home a { color: #6b7280; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 5px; }
        .back-home a:hover { color: #4f46e5; }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-logo">
                <i class="fa-solid fa-universal-access"></i>
                <h1>BBPWDO Admin</h1>
            </div>
            
            <div class="auth-tabs">
                <button class="active" onclick="switchTab('login')">Login</button>
            </div>
            
            <?php if ($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form active" id="loginForm">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Enter your email">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit" class="btn-auth">Login</button>
                <div class="forgot-link">
                    <a href="#" onclick="showForgot(); return false;">Forgot Password?</a>
                </div>
            </form>
            
            <div id="forgotSection" style="display: none;">
                <form method="POST">
                    <input type="hidden" name="forgot" value="1">
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required placeholder="Enter your email">
                    </div>
                    <button type="submit" class="btn-auth">Send Code</button>
                    <div class="forgot-link">
                        <a href="#" onclick="showLogin(); return false;">Back to Login</a>
                    </div>
                </form>
            </div>
            
            <div id="verifySection" style="display: none;">
                <form method="POST">
                    <input type="hidden" name="verify_code" value="1">
                    <div class="form-group">
                        <label>Verification Code</label>
                        <input type="text" name="code" required placeholder="Enter 6-digit code" maxlength="6">
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required placeholder="Enter new password" minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required placeholder="Confirm new password" minlength="6">
                    </div>
                    <button type="submit" class="btn-auth">Reset Password</button>
                    <div class="forgot-link">
                        <a href="#" onclick="showForgot(); return false;">Back</a>
                    </div>
                </form>
            </div>
            <div class="back-home">
                <a href="/index.html"><i class="fa-solid fa-arrow-left"></i> Back to Home</a>
            </div>
        </div>
    </div>
    
    </script>
    <script>
    function showForgot() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('forgotSection').style.display = 'block';
        document.getElementById('verifySection').style.display = 'none';
    }
    
    function showLogin() {
        document.getElementById('loginForm').style.display = 'block';
        document.getElementById('forgotSection').style.display = 'none';
        document.getElementById('verifySection').style.display = 'none';
    }
    
    function showVerify() {
        document.getElementById('loginForm').style.display = 'none';
        document.getElementById('forgotSection').style.display = 'none';
        document.getElementById('verifySection').style.display = 'block';
    }
    
    <?php if (isset($_SESSION['reset_code'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showVerify();
    });
    <?php endif; ?>
    </script>
</body>
</html>