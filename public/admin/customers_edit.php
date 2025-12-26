<?php
// public/admin/customers_edit.php

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

$customer_id = $_GET['customer_id'] ?? null;
if (!$customer_id) {
    header('Location: index.php?page=customers');
    exit;
}

$message = "";

// --------- handle POST (update customer) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_customer'])) {

    $address = $_POST['address'] ?? '';
    $phone   = $_POST['phone']   ?? '';
    $email   = $_POST['email']   ?? '';

    $message = $admin->update_customer($customer_id, $address, $phone, $email);

    // PRG: if ok, go back to list with message
    if (strpos($message, 'failed') === false) {
        header('Location: index.php?page=customers&msg=' . urlencode($message));
        exit;
    }
}

// --------- load customer data from read_all_customer ----------
$row  = null;
$data = $admin->read_all_customer();

if (is_array($data)) {
    foreach ($data as $c) {
        if ((string)$c['customer_id'] === (string)$customer_id) {
            $row = $c;
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
    <title>Edit customer – Bauman Bank</title>
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
    input[type="email"] {
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

    <h2>Edit customer #<?= htmlspecialchars($customer_id) ?></h2>

    <?php if ($message): ?>
    <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$row): ?>

    <p>Customer not found.</p>
    <a href="index.php?page=customers" class="back-link">&laquo; Back to customers</a>

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
        <h3>Customer data (editable)</h3>
        <form method="post">
            <label>Address:
                <input type="text" name="address"
                    value="<?= htmlspecialchars($row['address'] ?? $row['adress'] ?? '') ?>">
            </label>
            <label>Phone:
                <input type="text" name="phone" value="<?= htmlspecialchars($row['phone'] ?? '') ?>">
            </label>
            <label>Email:
                <input type="email" name="email" value="<?= htmlspecialchars($row['email'] ?? '') ?>">
            </label>
            <button type="submit" name="save_customer">Save changes</button>
        </form>
    </div>

    <a href="customers.php" class="back-link">&laquo; Back to customers</a>

    <?php endif; ?>

</body>

</html>