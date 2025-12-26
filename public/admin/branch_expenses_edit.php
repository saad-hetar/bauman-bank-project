<?php
// public/admin/branch_expenses_edit.php

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

$expenses_id = $_GET['expenses_id'] ?? null;
if (!$expenses_id) {
    header('Location: index.php?page=branch_expenses');
    exit;
}

$message = "";

// ---------- handle update ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_expense'])) {

    $branch_id     = $_POST['branch_id']     ?? '';
    $expenses_type = $_POST['expenses_type'] ?? '';
    $cost          = $_POST['cost']          ?? '';

    // keep same signature as your current update_expenses
    $message = $admin->update_expenses($expenses_id, $branch_id, $expenses_type, $cost);

    // PRG: redirect on success (no "failed" word in message)
    if (strpos($message, 'failed') === false) {
        header('Location: index.php?page=branch_expenses&msg=' . urlencode($message));
        exit;
    }
}

// ---------- load single expense via read_all_expenses ----------
$row  = null;
$data = $admin->read_all_expenses();

if (is_array($data)) {
    foreach ($data as $e) {
        if ((string)$e['expenses_id'] === (string)$expenses_id) {
            $row = $e;
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
    <title>Edit expense – Bauman Bank</title>
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

    <h2>Edit expense #<?= htmlspecialchars($expenses_id) ?></h2>

    <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$row): ?>

    <p>Expense not found.</p>
    <a href="index.php?page=branch_expenses" class="back-link">&laquo; Back to expenses</a>

    <?php else: ?>

    <div class="box">
        <h3>Current data</h3>
        <p><strong>Expense ID:</strong> <?= htmlspecialchars($row['expenses_id']) ?></p>
        <p><strong>Paid by:</strong> <?= htmlspecialchars($row['paid_by'] ?? '') ?></p>
        <p><strong>Paid at:</strong> <?= htmlspecialchars($row['paid_at'] ?? '') ?></p>
    </div>

    <div class="box">
        <h3>Edit expense info</h3>
        <form method="post">
            <label>Branch ID:
                <input type="text" name="branch_id" value="<?= htmlspecialchars($row['branch_id']) ?>">
            </label>
            <label>Type:
                <select name="expenses_type">
                    <option value="">Select trans type</option>
                    <option value="business">branch's rent</option>
                    <option value="business">utility</option>
                    <option value="business">internet</option>
                    <option value="business">water</option>
                    <option value="business">gas</option>
                    <option value="business">electricity</option>
                    <option value="business">goverments' tax</option>
                </select><input type="text" name="expenses_type" value="<?= htmlspecialchars($row['expenses_type']) ?>">
            </label>
            <label>Cost:
                <input type="text" name="cost" value="<?= htmlspecialchars($row['cost']) ?>">
            </label>
            <button type="submit" name="save_expense">Save changes</button>
        </form>
    </div>

    <a href="index.php?page=branch_expenses" class="back-link">&laquo; Back to expenses</a>

    <?php endif; ?>

</body>

</html>