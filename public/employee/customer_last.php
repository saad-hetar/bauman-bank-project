<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../classes/employee.class.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../auth/login.php');
    exit;
}

$employee_id = $_SESSION['user_id'] ?? null;
$employee = new employee($employee_id);

$customer_id = $_SESSION['last_customer_id'] ?? null;
$password = $_SESSION['password'] ?? null;
if (!$customer_id && !$password) {
    echo "No last customer found.";
    exit;
}

global $pdo;
$stmt = $pdo->prepare("
            SELECT c.*, p.last_name, p.first_name, a.account_id, a.account_type, a.currency, cr.card_num, cr.balance, l.login_id, l.role
            FROM customer c
            LEFT JOIN passport p ON p.passport_id = c.passport_id
            LEFT JOIN account a ON a.customer_id = c.customer_id
            LEFT JOIN card cr ON cr.account_id = a.account_id
            LEFT JOIN login l ON l.user_id = c.customer_id
            WHERE c.customer_id = ?
        ");
        $stmt->execute([$customer_id]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Last Created Customer</h2>

<?php if ($customer): ?>
<ul>
    <li>Customer ID: <?= htmlspecialchars($customer['customer_id']) ?></li>
    <li>Passport ID: <?= htmlspecialchars($customer['passport_id']) ?></li>
    <li>Name: <?= htmlspecialchars($customer['last_name'] . ' ' . $customer['first_name']) ?></li>
    <li>Phone: <?= htmlspecialchars($customer['phone']) ?></li>
    <li>Email: <?= htmlspecialchars($customer['email']) ?></li>
    <li>Account ID: <?= htmlspecialchars($customer['account_id']) ?></li>
    <li>Account Type: <?= htmlspecialchars($customer['account_type']) ?></li>
    <li>Currency: <?= htmlspecialchars($customer['currency']) ?></li>
    <li>Card Number: <?= htmlspecialchars($customer['card_num']) ?></li>
    <li>Balance: <?= htmlspecialchars($customer['balance']) ?></li>
    <li>Login ID: <?= htmlspecialchars($customer['login_id']) ?></li>
    <li>Role: <?= htmlspecialchars($customer['role']) ?></li>
    <li>password: <?= htmlspecialchars($password) ?></li>
</ul>
<?php else: ?>
<p>No last customer found.</p>
<?php endif; ?>