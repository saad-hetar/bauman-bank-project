<?php
// public/admin/branches_edit.php

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

$branch_id = $_GET['branch_id'] ?? null;
if (!$branch_id) {
    header('Location: branches.php');
    exit;
}

$message = "";

// ---------- handle update ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_branch'])) {

    $name    = $_POST['branch_name'] ?? '';
    $address = $_POST['address']     ?? '';
    $manager = $_POST['manager_id']  ?? '';

    $message = $admin->update_branch($branch_id, $name, $address, $manager);

    // PRG: redirect on success
    if (strpos($message, 'failed') === false) {
        header('Location: index.php?page=branches&msg=' . urlencode($message));
        exit;
    }
}

// ---------- load branch row via read_all_branch ----------
$row = null;
$data = $admin->read_all_branch();

if (is_array($data)) {
    foreach ($data as $b) {
        if ((string)$b['branch_id'] === (string)$branch_id) {
            $row = $b;
            break;
        }
    }
} else {
    $message = $data; // some error string
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Edit branch – Bauman Bank</title>
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

    input[type="text"] {
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

    <h2>Edit branch #<?= htmlspecialchars($branch_id) ?></h2>

    <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$row): ?>

    <p>Branch not found.</p>
    <a href="branches.php" class="back-link">&laquo; Back to branches</a>

    <?php else: ?>

    <div class="box">
        <h3>Current data</h3>
        <p><strong>Branch ID:</strong> <?= htmlspecialchars($row['branch_id']) ?></p>
        <p><strong>Created by:</strong> <?= htmlspecialchars($row['created_by'] ?? '') ?></p>
        <p><strong>Created at:</strong> <?= htmlspecialchars($row['created_at'] ?? '') ?></p>
        <p><strong>Updated by:</strong> <?= htmlspecialchars($row['updated_by'] ?? '') ?></p>
    </div>

    <div class="box">
        <h3>Edit branch info</h3>
        <form method="post">
            <label>Branch name:
                <input type="text" name="branch_name" value="<?= htmlspecialchars($row['branch_name']) ?>">
            </label>
            <label>Manager ID:
                <input type="text" name="manager_id" value="<?= htmlspecialchars($row['manager_id'] ?? '') ?>">
            </label>
            <label>Address:
                <input type="text" name="address" value="<?= htmlspecialchars($row['address']) ?>">
            </label>
            <button type="submit" name="save_branch">Save changes</button>
        </form>
    </div>

    <a href="branches.php" class="back-link">&laquo; Back to branches</a>

    <?php endif; ?>

</body>

</html>