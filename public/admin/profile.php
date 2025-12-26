<?php
// public/admin/profile.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

require_once __DIR__ . '/../../classes/admin.class.php';

$adminId = $_SESSION['user_id'];
$admin   = new admin($adminId);

$message = "";
$pass_msg = "";

// initialize to avoid undefined variable notices
$emp = null;
$passport = null;
$login = null;

// ---------- LOAD ADMIN EMPLOYEE RECORD ----------
$emp_result = $admin->read_emp($adminId); // returns fetchAll()
if (is_array($emp_result) && count($emp_result) > 0) {
    $emp = $emp_result[0];
}
// If read_emp returns null/empty, $emp remains null

// If we have an emp row, try to load passport & login (login_id often comes from read_emp)
// if ($emp) {
    $passport_id = $emp['passport_id'] ?? null;
    $login_id    = $_SESSION['login_id'] ?? null; // read_emp should provide this (you said it does)
    $password_orig = $_SESSION['password_orig'] ?? '';

    if ($passport_id !== null) {
        $passport_result = $admin->read_passport($passport_id);
        if (is_array($passport_result) && count($passport_result) > 0) {
            $passport = $passport_result[0];
        }
    }

    if ($login_id !== null) {
        $login_result = $admin->read_login($login_id);
        $login = $login_result;
        // if (is_array($login_result) && count($login_result) > 0) {
        //     $login = $login_result[0];
        // }
    }
// }

// ------------ UPDATE EMPLOYEE INFO ------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_emp'])) {

    $branch_id = $_POST['branch_id'] ?? '';
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $lore      = $_POST['lore'] ?? '';
    $quit_date = (isset($_POST['quit_date']) && $_POST['quit_date'] !== "") ? $_POST['quit_date'] : null;

    // call the admin wrapper which uses update_emp stored procedure
    $message = $admin->update_emp($adminId, $branch_id, $email, $phone, $lore, $quit_date);

    // reload emp/login/passport after update
    $emp_result = $admin->read_emp($adminId);
    if (is_array($emp_result) && count($emp_result) > 0) {
        $emp = $emp_result[0];
        $passport_id = $emp['passport_id'] ?? null;
        $login_id = $emp['login_id'] ?? null;

        if ($passport_id !== null) {
            $passport_result = $admin->read_passport($passport_id);
            if (is_array($passport_result) && count($passport_result) > 0) {
                $passport = $passport_result[0];
            } else {
                $passport = null;
            }
        }

        if ($login_id !== null) {
            $login_result = $admin->read_login($login_id);
            if (is_array($login_result) && count($login_result) > 0) {
                $login = $login_result[0];
            } else {
                $login = null;
            }
        }
    }
}

// ------------ UPDATE PASSWORD ------------
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_pass'])) {

    $new_pass = $_POST['new_pass'] ?? '';
    $confirm  = $_POST['confirm_pass'] ?? '';

    if ($new_pass === "" || $confirm === "") {
        $pass_msg = "Password fields cannot be empty";
    } elseif ($new_pass !== $confirm) {
        $pass_msg = "Passwords do not match";
    } else {
        // ensure we have login info (login_id) from read_emp
        $login_id = $emp['login_id'] ?? ($login['login_id'] ?? null);
        if ($login_id) {
            // $hash = hash('sha3-256', $new_pass);
            $pass_msg = $admin->update_passwort($login_id, $new_pass);
            // reload login (if update_password modifies something you want to display)
            $login_result = $admin->read_login($login_id);
            if (is_array($login_result) && count($login_result) > 0) {
                $login = $login_result;
            }
        } else {
            $pass_msg = "Cannot update password: no login record found for this admin.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Admin profile – Bauman Bank</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
    }

    .box {
        border: 1px solid #b9cce4;
        padding: 12px;
        margin-bottom: 18px;
        background: #f4f8ff;
        /* very light blue */
        border-radius: 6px;
    }

    .box h3 {
        margin-top: 0;
    }

    label {
        display: block;
        margin: 8px 0;
    }

    input[type="text"],
    input[type="email"],
    input[type="date"],
    input[type="password"] {
        width: 300px;
        box-sizing: border-box;
    }

    .msg {
        color: blue;
        margin-bottom: 10px;
    }

    .err {
        color: red;
        margin-bottom: 10px;
    }
    </style>
</head>

<body>
    <h2>Admin Profile</h2>

    <!-- ========== PASSPORT INFO ========== -->
    <div class="box">
        <h3>Passport information (read only)</h3>

        <?php if ($passport): ?>
        <p><strong>Passport ID:</strong> <?= htmlspecialchars($passport['passport_id']) ?></p>
        <p><strong>Name:</strong>
            <?= htmlspecialchars($passport['last_name'] . " " . $passport['first_name'] . " " . $passport['middle_name']) ?>
        </p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($passport['gender']) ?></p>
        <p><strong>Nationality:</strong> <?= htmlspecialchars($passport['nationality']) ?></p>
        <p><strong>Birth date:</strong> <?= htmlspecialchars($passport['birth_date']) ?></p>
        <p><strong>Birth place:</strong> <?= htmlspecialchars($passport['birth_place']) ?></p>
        <p><strong>Passport number:</strong>
            <?= htmlspecialchars($passport['passport_series'] . " " . $passport['passport_num']) ?></p>
        <p><strong>Issue date:</strong> <?= htmlspecialchars($passport['issue_date']) ?></p>
        <p><strong>Expire date:</strong> <?= htmlspecialchars($passport['expire_date']) ?></p>
        <p><strong>Authority:</strong> <?= htmlspecialchars($passport['assuing_authority']) ?></p>
        <?php else: ?>
        <p>No passport info available.</p>
        <?php endif; ?>
    </div>

    <!-- ========== EMPLOYEE INFO (READ ONLY) ========== -->
    <div class="box">
        <h3>Employee information (read only)</h3>

        <?php if ($emp): ?>
        <p><strong>Employee ID:</strong> <?= htmlspecialchars($emp['emp_id']) ?></p>
        <p><strong>Branch ID:</strong> <?= htmlspecialchars($emp['branch_id']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($emp['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($emp['phone']) ?></p>
        <p><strong>Role (lore):</strong> <?= htmlspecialchars($emp['lore']) ?></p>
        <p><strong>Hire date:</strong> <?= htmlspecialchars($emp['hire_date']) ?></p>
        <p><strong>Quit date:</strong> <?= htmlspecialchars($emp['quit_date']) ?></p>

        <a href="employees_edit.php?emp_id=<?= htmlspecialchars($emp['emp_id']) ?>" class="button">Edit employee
            info</a>

        <?php else: ?>
        <p>No employee information found.</p>
        <?php endif; ?>
    </div>

    <!-- ========== LOGIN INFO + PASSWORD HASH ========== -->
    <div class="box">
        <h3>Login information (read only)</h3>

        <?php if ($login): ?>
        <p><strong>Login ID:</strong> <?= htmlspecialchars($login['login_id']) ?></p>
        <p><strong>User ID:</strong> <?= htmlspecialchars($login['user_id']) ?></p>
        <p><strong>Role:</strong> <?= htmlspecialchars($login['role']) ?></p>

        <p><strong>Password hash:</strong>
            <code><?= htmlspecialchars($password_orig) ?></code>
        </p>

        <?php else: ?>
        <p>No login record found.</p>
        <?php endif; ?>
    </div>

    <!-- ========== PASSWORD UPDATE ========== -->
    <div class="box">
        <h3>Change password</h3>
        <?php if (isset($pass_msg)): ?>
        <p style="color:blue"><?= htmlspecialchars($pass_msg) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>New password:
                <input type="password" name="new_pass">
            </label>
            <label>Confirm password:
                <input type="password" name="confirm_pass">
            </label>

            <button type="submit" name="update_pass">Update Password</button>
        </form>
    </div>