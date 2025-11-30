<?php
session_start();
require_once('database/db.php');

// If already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_id']) && isset($_POST['password'])) {
    $login_id = $_POST['login_id'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM login WHERE login_id = :login_id");
        $stmt->execute([':login_id' => $login_id]);
        $login = $stmt->fetch();
        
        if ($login && password_verify($password, $login['password_hash'])) {
            $_SESSION['user_id'] = $login['user_id'];
            $_SESSION['role'] = $login['role'];
            $_SESSION['login_id'] = $login['login_id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid login credentials';
        }
    } catch (PDOException $e) {
        $error = 'Login error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bauman Bank - Login</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="bank-logo">
                <h1>🏦 Bauman Bank</h1>
            </div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your account</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <form method="POST" action="index.php" class="login-form">
                <div class="form-group">
                    <label for="login_id">Login ID</label>
                    <input type="text" id="login_id" name="login_id" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>

