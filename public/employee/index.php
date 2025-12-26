<?php
session_start();
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/employee.class.php';

$employeeId = $_SESSION['user_id'];
$employee   = new employee($employeeId);

$page = $_GET['page'] ?? 'home';

function is_active($p, $current)
{
    return $p === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Bauman Bank - employee</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Bauman Bank <small>- employee panel</small></h1>
    </header>

    <div class="layout">
        <nav>
            <h3>Menu</h3>
            <a href="index.php?page=home" class="<?= is_active('home', $page) ?>">profile</a>
            <a href="index.php?page=accounts" class="<?= is_active('accounts', $page) ?>">Accounts</a>
            <a href="index.php?page=transactions" class="<?= is_active('transactions', $page) ?>">Transactions</a>
            <a href="index.php?page=transfers" class="<?= is_active('transfers', $page) ?>">Transfers</a>
            <a href="index.php?page=cards" class="<?= is_active('cards', $page) ?>">Cards</a>
            <a href="index.php?page=customers" class="<?= is_active('customers', $page) ?>">Customers</a>
            <a href="index.php?page=passports" class="<?= is_active('passports', $page) ?>">Passports</a>
            <a href="index.php?page=deposits" class="<?= is_active('deposits', $page) ?>">Saving deposits</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>

        <main>
            <?php
            if ($page === 'home') {
                include __DIR__ . '/profile.php';
            } elseif ($page === 'accounts') {
                include __DIR__ . '/accounts.php';
            } elseif ($page === 'transactions') {
                include __DIR__ . '/transactions.php';
            } elseif ($page === 'transfers') {
                include __DIR__ . '/transfers.php';
            } elseif ($page === 'cards') {
                include __DIR__ . '/cards.php';
            } elseif ($page === 'customers') {
                include __DIR__ . '/customers.php';
            } elseif ($page === 'passports') {
                include __DIR__ . '/passports.php';
            } elseif ($page === 'deposits') {
                include __DIR__ . '/deposits.php';
            } else {
                echo "<p>Unknown page.</p>";
            }
            ?>
        </main>
    </div>
</body>

</html>