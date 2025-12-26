<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../classes/customer.class.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

$customer_id = $_SESSION['user_id'] ?? null;
$customer = new customer($customer_id);

$transaction_id = $_SESSION['last_transaction_id'] ?? null;
if (!$transaction_id) {
    echo "No last transaction found.";
    exit;
}

global $pdo;
$stmt = $pdo->prepare("
            SELECT * FROM transaction
            WHERE trans_id = ?
        ");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Last Created transaction</h2>

<?php if ($transaction): ?>
<ul>
    <li>transaction ID: <?= htmlspecialchars($transaction['trans_id']) ?></li>
    <li>card num: <?= htmlspecialchars($transaction['card_num']) ?></li>
    <li>transacted by: <?= htmlspecialchars($transaction['transacted_by']) ?></li>
    <li>trans type: <?= htmlspecialchars($transaction['trans_type']) ?></li>
    <li>amount: <?= htmlspecialchars($transaction['amount']) ?></li>
    <li>commission: <?= htmlspecialchars($transaction['commission']) ?></li>
    <li>trans date: <?= htmlspecialchars($transaction['trans_date']) ?></li>
    <li>description: <?= htmlspecialchars($transaction['description']) ?></li>
</ul>
<?php else: ?>
<p>No last transaction found.</p>
<?php endif; ?>