<?php
session_start();
require_once('database/db.php');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Only admin and employee can access
if ($role !== 'admin' && $role !== 'employee') {
    header('Location: dashboard.php');
    exit;
}

if ($role === 'admin') {
    require_once('classes/admin.class.php');
    $user = new admin($user_id);
} else {
    require_once('classes/employee.class.php');
    $user = new employee($user_id);
}

$customers = [];
$search_term = '';

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $customers = $user->search_customer($search_term);
} else {
    $customers = $user->read_all_customer();
}

if (!is_array($customers)) {
    $customers = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Bauman Bank</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>🏦 Bauman Bank</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <a href="customers.php" class="nav-link active">Customers</a>
                <a href="accounts.php" class="nav-link">Accounts</a>
                <a href="transactions.php" class="nav-link">Transactions</a>
                <a href="logout.php" class="nav-link">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Customer Management</h1>
        </div>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>All Customers</h3>
                <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            </div>

            <form method="GET" action="customers.php" style="margin-bottom: 20px;">
                <div class="form-group" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search customers..." value="<?php echo htmlspecialchars($search_term); ?>" style="flex: 1;">
                    <button type="submit" class="btn btn-secondary">Search</button>
                    <a href="customers.php" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Customer ID</th>
                            <th>User ID</th>
                            <th>Passport ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($customers) > 0): ?>
                            <?php foreach ($customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['customer_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['user_id'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($customer['passport_id'] ?? 'N/A'); ?></td>
                                    <td>
                                        <a href="customer_details.php?id=<?php echo htmlspecialchars($customer['customer_id'] ?? ''); ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 14px;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px;">
                                    No customers found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

