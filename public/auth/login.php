<?php
// C:\xampp\htdocs\bauman_bank_3\public\auth\login.php

session_start();

// adjust path if your db.php is somewhere else:
require_once __DIR__ . '/../../database/db.php';


// if already logged in, send them to the right panel
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: ../admin/index.php');
        exit;
    } elseif ($_SESSION['role'] === 'employee') {
        header('Location: ../employee/index.php');
        exit;
    } elseif ($_SESSION['role'] === 'customer') {
        header('Location: ../customer/index.php');
        exit;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($login_id === '' || $password === '') {
        $error = 'Please enter login ID and password.';
    } else {
        global $pdo;

        $sql = "SELECT * FROM login WHERE login_id = :login_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':login_id' => $login_id]);

        // VERY IMPORTANT: this makes $user an associative array, not a string
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ((password_verify($password, $user['password_hash']) || $user['password_hash'] == $password)
                 && $user['login_id'] == $login_id) {
            // login ok
            $_SESSION['login_id'] = $user['login_id'];
            $_SESSION['user_id']  = $user['user_id'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['password_orig'] = $password;

            if ($user['role'] === 'admin') {
                header('Location: ../admin/index.php');
                exit;
            } elseif ($user['role'] === 'employee') {
                header('Location: ../employee/index.php');
                exit;
            } elseif ($user['role'] === 'customer') {
                header('Location: ../customer/index.php');
                exit;
            } else {
                // unknown role
                session_unset();
                session_destroy();
                $error = 'Unknown role for this login.';
            }
        } else {
            $error = 'Invalid login ID or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Bauman Bank - Login</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background: #f0f4ff;
        margin: 0;
    }

    header {
        background: #0056b3;
        color: white;
        padding: 10px 20px;
    }

    .container {
        max-width: 400px;
        margin: 40px auto;
        background: white;
        padding: 20px;
        border-radius: 4px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    h1 {
        margin: 0;
    }

    label {
        display: block;
        margin-top: 10px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
        margin-top: 5px;
    }

    .btn {
        margin-top: 15px;
        padding: 8px 12px;
        width: 100%;
        background: #007bff;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 3px;
    }

    .btn:hover {
        background: #0056b3;
    }

    .error {
        color: #b30000;
        margin-top: 10px;
    }
    </style>
</head>

<body>
    <header>
        <h1>Bauman Bank</h1>
    </header>

    <div class="container">
        <h2>Login</h2>

        <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Login ID
                <input type="text" name="login_id" required>
            </label>

            <label>Password
                <input type="password" name="password" required>
            </label>

            <button class="btn" type="submit">Sign in</button>
        </form>
    </div>
</body>

</html>