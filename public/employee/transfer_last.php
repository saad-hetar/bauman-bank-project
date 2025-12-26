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

$transaction_id = $_SESSION['last_transfer_id'] ?? null;
if (!$transaction_id) {
    echo "No last transfer found.";
    exit;
}

global $pdo;
$stmt = $pdo->prepare("
            SELECT * FROM transfer
            WHERE trans_id = ?
        ");
        $stmt->execute([$transaction_id]);
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<h2>Last Created transfer</h2>

<?php if ($transaction): ?>
<ul>
    <li>transfer ID: <?= htmlspecialchars($transaction['trans_id']) ?></li>
    <li>sender card_num: <?= htmlspecialchars($transaction['sender_card_num']) ?></li>
    <li>sender phone: <?= htmlspecialchars($transaction['sender_phone']) ?></li>
    <li>trans type: <?= htmlspecialchars($transaction['trans_type']) ?></li>
    <li>amount: <?= htmlspecialchars($transaction['amount']) ?></li>
    <li>commission: <?= htmlspecialchars($transaction['commission']) ?></li>
    <li>receiver card num: <?= htmlspecialchars($transaction['receiver_card_num']) ?></li>
    <li>receiver phone: <?= htmlspecialchars($transaction['receiver_phone']) ?></li>
    <li>Currency: <?= htmlspecialchars($transaction['currency']) ?></li>
    <li>receiver bank: <?= htmlspecialchars($transaction['receiver_bank']) ?></li>
    <li>trans date: <?= htmlspecialchars($transaction['trans_date']) ?></li>
    <li>description: <?= htmlspecialchars($transaction['description']) ?></li>
</ul>
<?php else: ?>
<p>No last transfer found.</p>
<?php endif; ?>