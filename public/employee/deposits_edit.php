<?php
// public/employee/deposits_edit.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/employee.class.php';

$employee_id = $_SESSION['user_id'];
$employee    = new employee($employee_id);

$deposit_id = $_GET['deposit_id'] ?? null;
if (!$deposit_id) {
    header('Location: index.php?page=deposits');
    exit;
}

$message = "";

// --------- handle POST (update deposit) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_deposit'])) {

    $amount        = $_POST['amount'] ?? '';
    $deposit_type  = $_POST['deposit_type'] ?? '';
    $status        = $_POST['status'] ?? '';
    $period_months = $_POST['period_months'] ?? '';

    $message = $employee->update_saving_deposit($deposit_id, $amount, $deposit_type, $status, $period_months);

    // PRG: if ok, go back to list with message
    if (strpos($message, 'failed') === false) {
        header('Location: index.php?page=deposits&msg=' . urlencode($message));
        exit;
    }
}

// --------- load deposit data from read_all_saving_deposit ----------
$row  = null;
$data = $employee->read_all_saving_deposit();

if (is_array($data)) {
    foreach ($data as $d) {
        if ((string)$d['deposit_id'] === (string)$deposit_id) {
            $row = $d;
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
    <title>Edit deposit – Bauman Bank</title>
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

    <h2>Edit saving deposit #<?= htmlspecialchars($deposit_id) ?></h2>

    <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$row): ?>

    <p>Deposit not found.</p>
    <a href="index.php?page=deposits" class="back-link">&laquo; Back to deposits</a>

    <?php else: ?>

    <div class="box">
        <h3>Deposit info (read only)</h3>
        <p><strong>Account ID:</strong> <?= htmlspecialchars($row['account_id'] ?? '') ?></p>
        <p><strong>Currency:</strong> <?= htmlspecialchars($row['currency'] ?? '') ?></p>
        <p><strong>Start date:</strong> <?= htmlspecialchars($row['start_date'] ?? '') ?></p>
        <p><strong>End date:</strong> <?= htmlspecialchars($row['end_date'] ?? '') ?></p>
        <p><strong>Created at:</strong> <?= htmlspecialchars($row['created_at'] ?? '') ?></p>
    </div>

    <div class="box">
        <h3>Deposit data (editable)</h3>
        <form method="post">
            <label>Amount:
                <input type="text" name="amount" value="<?= htmlspecialchars($row['amount'] ?? '') ?>">
            </label>
            <label>Deposit type:
                <input type="text" name="deposit_type" value="<?= htmlspecialchars($row['deposit_type'] ?? '') ?>">
            </label>
            <label>Status:
                <input type="text" name="status" value="<?= htmlspecialchars($row['status'] ?? '') ?>">
            </label>
            <label>Period (months):
                <input type="text" name="period_months" value="<?= htmlspecialchars($row['period_months'] ?? '') ?>">
            </label>
            <button type="submit" name="save_deposit">Save changes</button>
        </form>
    </div>

    <a href="deposits.php" class="back-link">&laquo; Back to deposits</a>

    <?php endif; ?>

</body>

</html>