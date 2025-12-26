<?php
session_start();
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/admin.class.php';

$adminId = $_SESSION['user_id'];
$admin   = new admin($adminId);

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
    <title>Bauman Bank - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Bauman Bank <small>- Admin panel</small></h1>
    </header>

    <div class="layout">
        <nav>
            <h3>Menu</h3>
            <a href="index.php?page=home" class="<?= is_active('home', $page) ?>">profile</a>
            <a href="index.php?page=accounts" class="<?= is_active('accounts', $page) ?>">Accounts</a>
            <a href="index.php?page=transactions" class="<?= is_active('transactions', $page) ?>">Transactions</a>
            <a href="index.php?page=transfers" class="<?= is_active('transfers', $page) ?>">Transfers</a>
            <a href="index.php?page=branches" class="<?= is_active('branches', $page) ?>">Branches</a>
            <a href="index.php?page=branch_expenses" class="<?= is_active('branch_expenses', $page) ?>">Branch
                expenses</a>
            <a href="index.php?page=bank_money" class="<?= is_active('bank_money', $page) ?>">Bank money</a>
            <a href="index.php?page=cards" class="<?= is_active('cards', $page) ?>">Cards</a>
            <a href="index.php?page=customers" class="<?= is_active('customers', $page) ?>">Customers</a>
            <a href="index.php?page=employees" class="<?= is_active('employees', $page) ?>">Employees</a>
            <a href="index.php?page=logins" class="<?= is_active('logins', $page) ?>">Logins</a>
            <a href="index.php?page=passports" class="<?= is_active('passports', $page) ?>">Passports</a>
            <a href="index.php?page=deposits" class="<?= is_active('deposits', $page) ?>">Saving deposits</a>
            <a href="index.php?page=central_bank_customers"
                class="<?= is_active('central_bank_customers', $page) ?>">Central Bank Customers</a>
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
            } elseif ($page === 'branches') {
                include __DIR__ . '/branches.php';
            } elseif ($page === 'branch_expenses') {
                include __DIR__ . '/branch_expenses.php';
            } elseif ($page === 'bank_money') {
                include __DIR__ . '/bank_money.php';
            } elseif ($page === 'cards') {
                include __DIR__ . '/cards.php';
            } elseif ($page === 'customers') {
                include __DIR__ . '/customers.php';
            } elseif ($page === 'employees') {
                include __DIR__ . '/employees.php';
            } elseif ($page === 'logins') {
                include __DIR__ . '/logins.php';
            } elseif ($page === 'passports') {
                include __DIR__ . '/passports.php';
            } elseif ($page === 'deposits') {
                include __DIR__ . '/deposits.php';
            } elseif ($page === 'central_bank_customers') {
                include __DIR__ . '/central_bank_customers.php';
            } else {
                echo "<p>Unknown page.</p>";
            }
            ?>
        </main>
    </div>
</body>

</html>