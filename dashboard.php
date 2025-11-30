<?php
session_start();
require_once('database/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Load appropriate class based on role
switch ($role) {
    case 'admin':
        require_once('classes/admin.class.php');
        $user = new admin($user_id);
        break;
    case 'employee':
        require_once('classes/employee.class.php');
        $user = new employee($user_id);
        break;
    case 'customer':
        require_once('classes/customer.class.php');
        $user = new customer($user_id);
        break;
    default:
        header('Location: index.php');
        exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bauman Bank - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>🏦 Bauman Bank</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link active">Dashboard</a>
                <?php if ($role === 'admin' || $role === 'employee'): ?>
                    <a href="customers.php" class="nav-link">Customers</a>
                    <a href="accounts.php" class="nav-link">Accounts</a>
                    <a href="transactions.php" class="nav-link">Transactions</a>
                <?php endif; ?>
                <?php if ($role === 'customer'): ?>
                    <a href="public/dashboard_client.php" class="nav-link">My Accounts</a>
                    <a href="public/transfer_client.php" class="nav-link">Transfer</a>
                    <a href="public/history_client.php" class="nav-link">History</a>
                    <a href="public/profile_client.php" class="nav-link">Profile</a>
                <?php endif; ?>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Dashboard</h1>
            <p class="user-info">Logged in as: <strong><?php echo htmlspecialchars(ucfirst($role)); ?></strong> (ID: <?php echo htmlspecialchars($user_id); ?>)</p>
        </div>

        <div class="dashboard-grid">
            <?php if ($role === 'admin'): ?>
                <!-- Admin Dashboard -->
                <div class="card">
                    <h3>📊 System Overview</h3>
                    <div class="stat-grid">
                        <div class="stat-item">
                            <span class="stat-label">Total Customers</span>
                            <span class="stat-value">
                                <?php 
                                try {
                                    $customers = $user->read_all_customer();
                                    echo is_array($customers) ? count($customers) : '0';
                                } catch (Exception $e) {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Accounts</span>
                            <span class="stat-value">
                                <?php 
                                try {
                                    $accounts = $user->read_all_account();
                                    echo is_array($accounts) ? count($accounts) : '0';
                                } catch (Exception $e) {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Logins</span>
                            <span class="stat-value">
                                <?php 
                                try {
                                    $logins = $user->read_all_login();
                                    echo is_array($logins) ? count($logins) : '0';
                                } catch (Exception $e) {
                                    echo 'N/A';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3>⚡ Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="customers.php?action=create" class="btn btn-primary">Create Customer</a>
                        <a href="accounts.php?action=create" class="btn btn-primary">Create Account</a>
                        <a href="transactions.php" class="btn btn-secondary">View Transactions</a>
                    </div>
                </div>

            <?php elseif ($role === 'employee'): ?>
                <!-- Employee Dashboard -->
                <div class="card">
                    <h3>👥 Customer Management</h3>
                    <p>Manage customer accounts, create new accounts, and process transactions.</p>
                    <div class="action-buttons">
                        <a href="customers.php" class="btn btn-primary">View Customers</a>
                        <a href="accounts.php?action=create" class="btn btn-primary">Create Account</a>
                    </div>
                </div>

            <?php elseif ($role === 'customer'): ?>
                <!-- Customer Dashboard -->
                <div class="card">
                    <h3>💰 Account Summary</h3>
                    <?php
                    try {
                        if (method_exists($user, 'read_customer_accounts')) {
                            $accounts = $user->read_customer_accounts($user_id);
                            if (is_array($accounts) && count($accounts) > 0) {
                                echo '<div class="account-list">';
                                foreach (array_slice($accounts, 0, 3) as $account) {
                                    echo '<div class="account-item">';
                                    echo '<span class="account-number">' . htmlspecialchars($account['account_id'] ?? 'N/A') . '</span>';
                                    echo '<span class="account-balance">Balance: $' . number_format($account['balance'] ?? 0, 2) . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p>No accounts found.</p>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<p>Unable to load accounts.</p>';
                    }
                    ?>
                    <div class="action-buttons">
                        <a href="public/dashboard_client.php" class="btn btn-primary">View All Accounts</a>
                        <a href="public/transfer_client.php" class="btn btn-secondary">Make Transfer</a>
                    </div>
                </div>

                <div class="card">
                    <h3>💳 Recent Cards</h3>
                    <?php
                    try {
                        if (method_exists($user, 'read_customer_cards')) {
                            $cards = $user->read_customer_cards($user_id);
                            if (is_array($cards) && count($cards) > 0) {
                                echo '<div class="card-list">';
                                foreach (array_slice($cards, 0, 3) as $card) {
                                    echo '<div class="card-item">';
                                    echo '<span class="card-number">****' . substr($card['card_num'] ?? '0000', -4) . '</span>';
                                    echo '<span class="card-type">' . htmlspecialchars($card['card_type'] ?? 'N/A') . '</span>';
                                    echo '</div>';
                                }
                                echo '</div>';
                            } else {
                                echo '<p>No cards found.</p>';
                            }
                        }
                    } catch (Exception $e) {
                        echo '<p>Unable to load cards.</p>';
                    }
                    ?>
                    <div class="action-buttons">
                        <a href="my_cards.php" class="btn btn-primary">View All Cards</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2024 Bauman Bank. All rights reserved.</p>
    </footer>
</body>
</html>

