<?php
// public/admin/employees_edit.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

$admin_id = $_SESSION['user_id'];
$admin    = new admin($admin_id);

$emp_id = $_GET['emp_id'] ?? null;
if (!$emp_id) {
    header('Location: index.php?page=employees');
    exit;
}

$message = "";

// --------- handle POST (update employee) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_employee'])) {

    $branch_id = $_POST['branch_id'] ?? '';
    $email     = $_POST['email'] ?? '';
    $phone     = $_POST['phone'] ?? '';
    $lore      = $_POST['lore'] ?? '';
    $quit_date = $_POST['quit_date'] ?? ''; // Can be empty

    // If quit_date is empty, pass NULL or empty string depending on your database
    $quit_date = ($quit_date === '') ? null : $quit_date;

    $message = $admin->update_emp($emp_id, $branch_id, $email, $phone, $lore, $quit_date);

    // PRG: if ok, go back to list with message
    if (strpos($message, 'failed') === false) {
        header('Location: index.php?page=employees&msg=' . urlencode($message));
        exit;
    }
}

// --------- load employee data from read_all_emp ----------
$row  = null;
$data = $admin->read_all_emp();

if (is_array($data)) {
    foreach ($data as $e) {
        if ((string)$e['emp_id'] === (string)$emp_id) {
            $row = $e;
            break;
        }
    }
} else {
    $message = $data; // error string
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Edit employee – Bauman Bank</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
    }

    .box {
        border: 1px solid #ccc;
        padding: 10px;
        margin-bottom: 15px;
    }

    .msg {
        margin: 10px 0;
        color: blue;
    }

    label {
        display: block;
        margin: 4px 0;
    }

    input[type="text"],
    input[type="email"],
    input[type="date"] {
        width: 250px;
        box-sizing: border-box;
    }

    .back-link {
        margin-top: 10px;
        display: inline-block;
    }
    </style>
</head>

<body>

    <h2>Edit employee #<?= htmlspecialchars($emp_id) ?></h2>

    <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$row): ?>

    <p>Employee not found.</p>
    <a href="index.php?page=employees" class="back-link">&laquo; Back to employees</a>

    <?php else: ?>

    <div class="box">
        <h3>Passport & login info (read only)</h3>
        <p><strong>Passport ID:</strong> <?= htmlspecialchars($row['passport_id']) ?></p>
        <p><strong>Login ID:</strong> <?= htmlspecialchars($row['login_id'] ?? '') ?></p>
        <p><strong>Login:</strong> <?= htmlspecialchars($row['login'] ?? '') ?></p>
        <p><strong>Name:</strong>
            <?= htmlspecialchars(($row['last_name'] ?? '') . ' ' . ($row['first_name'] ?? '') . ' ' . ($row['middle_name'] ?? '')) ?>
        </p>
        <p><strong>Gender:</strong> <?= htmlspecialchars($row['gender'] ?? '') ?></p>
        <p><strong>Nationality:</strong> <?= htmlspecialchars($row['nationality'] ?? '') ?></p>
        <p><strong>Passport type:</strong> <?= htmlspecialchars($row['passport_type'] ?? '') ?></p>
        <p><strong>Birth date:</strong> <?= htmlspecialchars($row['birth_date'] ?? '') ?></p>
        <p><strong>Birth place:</strong> <?= htmlspecialchars($row['birth_place'] ?? '') ?></p>
    </div>

    <div class="box">
        <h3>Employee data (editable)</h3>
        <form method="post">
            <label>Branch ID:
                <input type="text" name="branch_id" value="<?= htmlspecialchars($row['branch_id'] ?? '') ?>">
            </label>
            <label>Email:
                <input type="email" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>">
            </label>
            <label>Phone:
                <input type="text" name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>">
            </label>
            <label>Role (lore):
                <input type="text" name="lore" value="<?= htmlspecialchars($row['lore'] ?? '') ?>">
            </label>
            <label>Quit date (leave empty if still employed):
                <input type="date" name="quit_date" value="<?= htmlspecialchars($row['quit_date'] ?? '') ?>">
            </label>
            <button type="submit" name="save_employee">Save changes</button>
        </form>
    </div>

    <a href="employees.php" class="back-link">&laquo; Back to employees</a>

    <?php endif; ?>

</body>

</html>