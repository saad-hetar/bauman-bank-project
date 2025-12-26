<?php
// public/admin/central_bank_customers.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

$admin_id = $_SESSION['user_id'];
$admin = new admin($admin_id);

$message = '';
$rows = [];

// SEARCH functionality
$q = $_GET['q'] ?? '';
if ($q !== '') {
    // Search in central_bank_customer table
    // Note: You'll need to implement search_central_bank_customer() in your admin.class.php
    if (method_exists($admin, 'search_central_bank_customer')) {
        $rows = $admin->search_central_bank_customer($q);
    } else {
        // Fallback: get all and filter in PHP (not efficient for large datasets)
        $allRows = $admin->read_all_central_bank_customer();
        $rows = array_filter($allRows, function($row) use ($q) {
            return stripos($row['last_name'], $q) !== false || 
                   stripos($row['first_name'], $q) !== false ||
                   stripos($row['phone'], $q) !== false ||
                   stripos($row['card_num'], $q) !== false;
        });
    }
} else {
    // Get all central bank customers with bank info
    if (method_exists($admin, 'read_all_central_bank_customer')) {
        $rows = $admin->read_all_central_bank_customer();
    }
}

// Check if we're inside the template or standalone
$in_template = isset($_GET['page']);
?>

<?php if (!$in_template): ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bauman Bank – Central Bank Customers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .top-bar form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .top-bar form input {
        padding: 4px;
        width: 200px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        margin-top: 15px;
        font-size: 13px;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 6px 8px;
        text-align: left;
    }

    th {
        background: #e3f0ff;
        font-weight: bold;
    }

    .msg {
        margin: 10px 0;
        font-size: 14px;
        color: #0033cc;
        padding: 6px 8px;
        background: #eef6ff;
        border-left: 4px solid #1e70ff;
        display: inline-block;
    }

    .simple-form {
        background: #f9f9f9;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 4px;
    }

    .simple-form h3 {
        margin-top: 0;
        color: #333;
    }

    .simple-form label {
        display: block;
        margin-bottom: 8px;
    }

    .simple-form input[type="text"] {
        width: 250px;
        padding: 4px;
        margin-left: 5px;
    }

    .simple-form button {
        background: #1e70ff;
        color: white;
        border: none;
        padding: 6px 12px;
        border-radius: 3px;
        cursor: pointer;
        margin-top: 8px;
    }

    .simple-form button:hover {
        background: #0d5bdd;
    }
    </style>
</head>

<body>
    <header>
        <h1>Bauman Bank <small>- Admin panel</small></h1>
    </header>

    <div class="layout">
        <nav>
            <h3>Menu</h3>
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=customers">Customers</a>
            <a href="index.php?page=central_bank_customers" class="active">Central Bank Customers</a>
            <a href="index.php?page=bank_money">Bank Money</a>
            <!-- Add other menu items as needed -->
            <a href="../auth/logout.php">Logout</a>
        </nav>

        <main>
            <?php endif; ?>

            <h2>Central Bank Customers</h2>

            <?php if ($message): ?>
            <div class="msg"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="top-bar">
                <?php if ($in_template): ?>
                <!-- When in template, submit to index.php with page parameter -->
                <form method="get" action="index.php" class="simple-form">
                    <input type="hidden" name="page" value="central_bank_customers">
                    <label>Search:
                        <input type="text" name="q" placeholder="Search customers..."
                            value="<?= htmlspecialchars($q) ?>">
                    </label>
                    <button type="submit">Search</button>
                    <!-- <?php if ($q): ?>
                    <a href="index.php?page=central_bank_customers" style="margin-left: 10px;">Clear</a>
                    <?php endif; ?> -->
                </form>
                <?php else: ?>
                <!-- When standalone, submit to this file -->
                <form method="get" action="central_bank_customers.php" class="simple-form">
                    <label>Search:
                        <input type="text" name="q" placeholder="Search customers..."
                            value="<?= htmlspecialchars($q) ?>">
                    </label>
                    <button type="submit">Search</button>
                    <?php if ($q): ?>
                    <!-- <a href="central_bank_customers.php" style="margin-left: 10px;">Clear</a> -->
                    <?php endif; ?>
                </form>
                <?php endif; ?>
            </div>

            <!-- <?php if ($q): ?>
            <p>Showing results for: "<strong><?= htmlspecialchars($q) ?></strong>"</p>
            <?php endif; ?> -->

            <?php if (is_array($rows) && count($rows) > 0): ?>
            <table>
                <tr>
                    <th>Customer ID</th>
                    <th>Bank Name</th>
                    <th>Bank Currency</th>
                    <th>Bank Balance</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Middle Name</th>
                    <th>Phone</th>
                    <th>Customer Balance</th>
                    <th>Card Number</th>
                </tr>

                <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['customer_id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['bank_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['currency'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['bank_balance'] ?? $row['balance'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['last_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['first_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['middle_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['customer_balance'] ?? $row['balance'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['card_num'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <?php else: ?>
            <p>No central bank customers found.</p>
            <?php endif; ?>

            <?php if (!$in_template): ?>
        </main>
    </div>
</body>

</html>
<?php endif; ?>