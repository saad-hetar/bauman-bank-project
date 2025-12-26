<?php
// public/admin/accounts_edit.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

$admin_id   = $_SESSION['user_id'];
$admin      = new admin($admin_id);

$account_id = $_GET['account_id'] ?? null;
if (!$account_id) {
    header('Location: index.php?page=accounts');
    exit;
}

$message = "";

// ---------- handle update (with auto closed_at) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_account'])) {
    $account_type   = $_POST['account_type']   ?? '';
    $account_status = $_POST['account_status'] ?? '';

    // set closed_at automatically if status is "closed"
    if ($account_status === 'closed') {
        // if your DB column is DATETIME:
        $closed_at = date('Y-m-d H:i:s');
        // if it's DATE only, use:
        // $closed_at = date('Y-m-d');
    } else {
        // for non-closed statuses we clear it (or keep old value in update_account, up to you)
        $closed_at = null;
    }

    $message = $admin->update_account($account_id, $account_type, $account_status, $closed_at);

    if (strpos($message, 'failed') === false) {
        header('Location: index.php?page=accounts&msg=' . urlencode($message));
        exit;
    }
}

// ---------- load account data using read_all_account ----------
$row  = null;
$data = $admin->read_all_account();

if (is_array($data)) {
    foreach ($data as $r) {
        if ((string)$r['account_id'] === (string)$account_id) {
            $row = $r;
            break;
        }
    }
} else {
    // read_all_account returned an error string
    $message = $data;
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Edit account – Bauman Bank</title>
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

    <h2>Edit account #<?= htmlspecialchars($account_id) ?></h2>

    <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$row): ?>
    <p>Account not found.</p>
    <a href="accounts.php" class="back-link">&laquo; Back to accounts</a>
    <?php else: ?>

    <div class="box">
        <h3>Customer & passport info (read only)</h3>
        <p><strong>Customer ID:</strong> <?= htmlspecialchars($row['customer_id']) ?></p>
        <p><strong>Passport ID:</strong> <?= htmlspecialchars($row['passport_id']) ?></p>
        <p><strong>Name:</strong>
            <?= htmlspecialchars($row['last_name'] . ' ' . $row['first_name']) ?>
        </p>
        <p><strong>Address:</strong> <?= htmlspecialchars($row['adress'] ?? $row['address'] ?? '') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($row['email']) ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($row['phone']) ?></p>
        <p><strong>Customer created by:</strong> <?= htmlspecialchars($row['created_by']) ?></p>
        <p><strong>Customer created at:</strong> <?= htmlspecialchars($row['created_at']) ?></p>
        <p><strong>Customer updated by:</strong> <?= htmlspecialchars($row['updated_by']) ?></p>
    </div>

    <div class="box">
        <h3>Account data</h3>
        <p><strong>Account ID:</strong> <?= htmlspecialchars($row['account_id']) ?></p>
        <p><strong>Currency:</strong> <?= htmlspecialchars($row['currency']) ?></p>
        <p><strong>Account created by:</strong>
            <?= htmlspecialchars($row['account_created_by'] ?? $row['created_by']) ?></p>
        <p><strong>Account created at:</strong>
            <?= htmlspecialchars($row['account_created_at'] ?? $row['created_at']) ?></p>
        <p><strong>Account updated by:</strong>
            <?= htmlspecialchars($row['account_updated_by'] ?? $row['updated_by']) ?></p>

        <form method="post">
            <label>
                Account type:('personal', 'business')
                <input type="text" name="account_type" value="<?= htmlspecialchars($row['account_type']) ?>">
            </label>
            <label>
                Account status:('active', 'closed', 'frozen')
                <input type="text" name="account_status" value="<?= htmlspecialchars($row['account_status']) ?>">
            </label>
            <label>
                Closed at:
                <input type="date" value="<?= htmlspecialchars(substr((string)$row['closed_at'], 0, 10)) ?>" readonly>
            </label>

            <button type="submit" name="save_account">Save changes</button>
        </form>
    </div>

    <a href="index.php?page=accounts" class="back-link">&laquo; Back to accounts</a>

    <?php endif; ?>

</body>

</html>