<?php
session_start();
if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

// Force account selection if not set
if (!isset($_SESSION['account_id'])) {
    header('Location: select_account.php');
    exit;
}

require_once __DIR__ . '/../../classes/customer.class.php';

$customerId = $_SESSION['user_id'];
$customer   = new customer($customerId);
$accountId  = $_SESSION['account_id']; // selected account

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
    <title>Bauman Bank - customer</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>
    <header>
        <h1>Bauman Bank <small>- customer panel</small></h1>
    </header>

    <div class="layout">
        <nav>
            <h3>Menu</h3>
            <a href="index.php?page=profile" class="<?= is_active('profile', $page) ?>">profile</a>
            <a href="index.php?page=home" class="<?= is_active('home', $page) ?>">home</a>
            <a href="index.php?page=transactions" class="<?= is_active('transactions', $page) ?>">Transactions</a>
            <a href="index.php?page=transfers" class="<?= is_active('transfers', $page) ?>">Transfers</a>
            <a href="index.php?page=deposits" class="<?= is_active('deposits', $page) ?>">Saving deposits</a>
            <a href="../auth/logout.php">Logout</a>
        </nav>

        <main>
            <?php
        switch ($page) {
            case 'profile':
                include __DIR__ . '/profile.php';
                break;
            case 'home':
                include __DIR__ . '/home.php';
                break;
            case 'transactions':
                include __DIR__ . '/transactions.php';
                break;
            case 'transfers':
                include __DIR__ . '/transfers.php';
                break;
            case 'deposits':
                include __DIR__ . '/deposits.php';
                break;
            default:
                echo "<p>Unknown page.</p>";
        }
        ?>
        </main>
    </div>
</body>

</html>