<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

$admin_id = $_SESSION['user_id'] ?? null;
$admin = new admin($admin_id);

$account_id = $_SESSION['last_account_id'] ?? null;
if (!$account_id) {
    echo "No last account found.";
    exit;
}

global $pdo;
$stmt = $pdo->prepare("
            SELECT a.*, p.last_name, p.first_name, ca.card_num
            FROM account a
            JOIN card ca ON ca.account_id = a.account_id
            JOIN customer c ON c.customer_id = a.customer_id
            JOIN passport p ON p.passport_id = c.passport_id
            WHERE a.account_id = ?
        ");
        $stmt->execute([$account_id]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Last Created account</h2>

<?php if ($account): ?>
<ul>
    <li>account ID: <?= htmlspecialchars($account['account_id']) ?></li>
    <li>customer id: <?= htmlspecialchars($account['customer_id']) ?></li>
    <li>Name: <?= htmlspecialchars($account['last_name'] . ' ' . $account['first_name']) ?></li>
    <li>account type: <?= htmlspecialchars($account['account_type']) ?></li>
    <li>account status: <?= htmlspecialchars($account['account_status']) ?></li>
    <li>currency: <?= htmlspecialchars($account['currency']) ?></li>
    <li>created at: <?= htmlspecialchars($account['created_at']) ?></li>
    <li>card num: <?= htmlspecialchars($account['card_num']) ?></li>
</ul>
<?php else: ?>
<p>No last account found.</p>
<?php endif; ?>