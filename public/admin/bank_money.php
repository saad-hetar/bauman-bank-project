<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin    = new admin($admin_id);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_capital'])) {
    $capital_id = $_POST['capital_id'] ?? '';  // Get capital_id from the form input
    $amount     = $_POST['capital_amount'] ?? '';
    $currency   = $_POST['currency'] ?? '';
    
    // Pass capital_id, amount, and currency to the update_capital function
    $message = $admin->update_capital($capital_id, $amount, $currency);

    header('Location: index.php?page=bank_money&msg=' . urlencode($message));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_capital'])) {
    $amount   = $_POST['capital_amount'] ?? '';
    $currency = $_POST['currency'] ?? '';

    $message = $admin->create_capital($amount, $currency);

    header('Location: index.php?page=bank_money&msg=' . urlencode($message));
    exit;
}

$q = $_GET['q'] ?? '';
if ($q !== '') {
    $bankMoney = $admin->search_bank_money($q);
} else {
    $bankMoney = $admin->read_bank_money();
}

$capital = $admin->read_capital();
?>

<h2>Bank money & capital</h2>
<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<h3>Capital</h3>

<?php if (is_array($capital) && count($capital) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Currency</th>
        <th>Capital Amount</th>
        <th>Action</th>
    </tr>

    <?php foreach ($capital as $c): ?>
    <tr>
        <form method="post">
            <td><?= htmlspecialchars($c['capital_id']) ?></td>

            <td>
                <?= htmlspecialchars($c['currency']) ?>
                <input type="hidden" name="currency" value="<?= htmlspecialchars($c['currency']) ?>">
            </td>

            <td>
                <input type="text" name="capital_amount" value="<?= htmlspecialchars($c['capital_amount']) ?>"
                    style="width:120px;">
            </td>

            <td style="text-align:right;">
                <input type="hidden" name="capital_id" value="<?= htmlspecialchars($c['capital_id']) ?>">
                <button type="submit" name="update_capital">
                    Update
                </button>
            </td>
        </form>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No capital rows found.</p>
<?php endif; ?>

<h3>Create Capital</h3>

<form method="post" class="simple-form" style="margin-bottom:20px;">
    <label>Currency:
        <input type="text" name="currency" placeholder="..." required>
    </label>

    <label>Capital amount:
        <input type="number" step="0.01" name="capital_amount" required>
    </label>

    <button type="submit" name="create_capital">
        Create Capital
    </button>
</form>



<form method="get" class="simple-form">
    <h3>Search bank money</h3>
    <input type="hidden" name="page" value="bank_money">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<?php if (is_array($bankMoney) && count($bankMoney) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Currency</th>
        <th>Expenses Sum</th>
        <th>Commission Sum</th>
        <th>Deposit Sum</th>
        <th>Customer Money Sum</th>
        <th>Interest Deposits Sum</th>
        <th>Total Money</th>
        <th>Calc Date</th>
    </tr>
    <?php foreach ($bankMoney as $bm): ?>
    <tr>
        <td><?= htmlspecialchars($bm['cacula_id'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['currency'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['expenses_sum'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['commission_sum'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['deposit_sum'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['customer_money_sum'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['interest_deposits_sum'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['total_money'] ?? '') ?></td>
        <td><?= htmlspecialchars($bm['cacula_date'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No bank money rows found.</p>
<?php endif; ?>