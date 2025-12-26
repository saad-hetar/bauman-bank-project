<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'], $_SESSION['user_id'], $_SESSION['account_id'])) {
    header('Location: select_account.php');
    exit;
}

require_once __DIR__ . '/../../classes/customer.class.php';

$customerId  = $_SESSION['user_id'];
$account_id  = $_SESSION['account_id'];
$currency = $_SESSION['currency'];

$customer    = new customer($customerId);
$customer->get_account_id($account_id); // set the current account in the customer object

$message = '';

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_num    = $_POST['card_num'] ?? '';
    $amount      = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';

    if (isset($_POST['deposit'])) {
        $message = $customer->deposit($card_num, $amount, $description);
    }

    if (isset($_POST['pay'])) {
        $trans_type = $_POST['trans_type'] ?? 'pay';
        $message    = $customer->pay($card_num, $amount, $trans_type, $description);
    }

    if (isset($_POST['withdraw'])) {
        $message = $customer->withdraw($card_num, $amount, $description);
    }

    if (isset($_POST['transfer_internal'])) {
        $currency    = $_POST['currency'] ?? '';
        $receiver    = $_POST['receiver_card_num'] ?? '';
        $message     = $customer->transfer_internal_card($card_num, 'internal', $currency, $description, $amount, $receiver);
    }

    if (isset($_POST['transfer_external'])) {
        $currency     = $_POST['currency'] ?? '';
        $receiver     = $_POST['receiver_card_num'] ?? '';
        $receiverBank = $_POST['receiver_bank'] ?? '';
        $message      = $customer->transfer_external_card($card_num, 'external', $currency, $description, $amount, $receiver, $receiverBank);
    }
}

// Fetch cards and transaction history
$cards   = $customer->read_customer_cards($account_id);
$history = $customer->history_transactions($account_id);
?>

<h2>Home - Account <?= htmlspecialchars(($account_id).' | currency: '.$currency) ?></h2>

<form action="select_account.php" method="get" style="margin-bottom:20px;">
    <button type="submit">Change Account</button>
</form>


<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- Display cards -->
<h3>Your Cards</h3>

<div class="cards-grid">
    <?php if (!empty($cards)): ?>
    <?php foreach ($cards as $card): ?>
    <div class="card-box">
        <div class="row">
            <span class="label">Card:</span>
            <span><?= htmlspecialchars($card['card_num']) ?></span>
        </div>

        <div class="row">
            <span class="label">CVV:</span>
            <span><?= htmlspecialchars($card['cvv']) ?></span>
        </div>

        <div class="row">
            <span class="label">Expires:</span>
            <span><?= htmlspecialchars($card['expire_date']) ?></span>
        </div>

        <div class="row balance">
            <span class="label">Balance:</span>
            <span><?= htmlspecialchars($card['balance']) ?> <?= htmlspecialchars($currency) ?></span>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <p>No cards for this account yet.</p>
    <?php endif; ?>
</div>




<!-- Transaction History -->
<h3>Transaction History</h3>
<?php if (is_array($history) && count($history) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Type of transaction</th>
        <th>Amount</th>
        <th>Extra</th>
        <th>description</th>
        <th>Date</th>
    </tr>
    <?php foreach ($history as $h): ?>
    <tr>
        <td><?= htmlspecialchars($h['trans_id']) ?></td>
        <td><?= htmlspecialchars($h['trans_type']) ?></td>
        <td><?= htmlspecialchars($h['amount']) ?></td>
        <td><?= htmlspecialchars($h['receiver_bank'] ?? '') ?></td>
        <td><?= htmlspecialchars($h['description'] ?? '') ?></td>
        <td><?= htmlspecialchars($h['trans_date']) ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No operations for this account yet.</p>
<?php endif; ?>

<style>
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 16px;
    margin-bottom: 30px;
}

.card-box {
    border: 1px solid #ccc;
    border-radius: 6px;
    padding: 12px 14px;
    background: #fafafa;
    font-size: 14px;
}

.card-box .row {
    display: flex;
    gap: 8px;
    /* keeps label + value close */
    margin-bottom: 6px;
}

.card-box .label {
    font-weight: bold;
    min-width: 70px;
    /* small, consistent alignment */
}

.card-box .balance {
    margin-top: 10px;
}
</style>