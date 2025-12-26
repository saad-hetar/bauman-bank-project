<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

$admin = new admin($_SESSION['user_id'] ?? 0); // make sure $admin exists

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $card_num    = $_POST['card_num'] ?? '';
    $amount      = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';

    if (isset($_POST['deposit'])) {
        $message = $admin->deposit($card_num, $amount, $description);
    }

    if (isset($_POST['pay'])) {
        $trans_type = $_POST['trans_type'] ?? 'pay';
        $message = $admin->pay($card_num, $amount, $trans_type, $description);
    }

    if (isset($_POST['withdraw'])) {
        $message = $admin->withdraw($card_num, $amount, $description);
    }

    if (isset($_POST['cancel_payment'])) {
        $trans_id = $_POST['trans_id'] ?? '';
        $message  = $admin->cancel_payments($trans_id);
    }

    $_SESSION['transactions_message'] = $message;
    // header('Location: index.php?page=transactions');
    $_SESSION['last_transaction_id'] = $admin->last_transaction_id;
    header('Location: transaction_last.php');
    exit;
}

if (isset($_SESSION['transactions_message'])) {
    $message = $_SESSION['transactions_message'];
    unset($_SESSION['transactions_message']);
}

// SEARCH (GET)
$q = $_GET['q'] ?? '';
if ($q !== '') {
    $rows = $admin->search_transaction($q);
} else {
    $rows = $admin->read_all_transaction();
}

// CARD SEARCH
$search_card = $_GET['search_card'] ?? '';
if ($search_card !== '') {
    $card_rows = $admin->search_card($search_card);
} else {
    $card_rows = $admin->read_all_card();
}

// Selected card for forms
$selected_card_num = $_GET['selected_card'] ?? '';

?>

<h2>Transactions</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- =====================================================
     DEPOSIT FORM
======================================================= -->
<form method="post" class="simple-form">
    <h3>Deposit</h3>
    <label>Card number:
        <input type="text" id="card_input_deposit" placeholder="Search card..." autocomplete="off" name="card_num"
            value="<?= htmlspecialchars($selected_card_num) ?>">
        <div id="card_dropdown_deposit"
            style="display:none; border:1px solid #ccc; padding:10px; max-height:200px; overflow-y:auto;">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>"
                style="padding:5px; cursor:pointer; border-bottom:1px solid #eee;">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> -
                Account: <?= htmlspecialchars($c['account_id']) ?> -
                Balance: <?= htmlspecialchars($c['balance']) ?> -
                Currency: <?= htmlspecialchars($c['currency']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>
    <label>Amount:
        <input type="text" name="amount">
    </label>
    <label>Description:
        <input type="text" name="description">
    </label>
    <button type="submit" name="deposit">Deposit</button>
</form>

<!-- =====================================================
     PAY FORM
======================================================= -->
<form method="post" class="simple-form">
    <h3>Pay</h3>
    <label>Card number:
        <input type="text" id="card_input_pay" placeholder="Search card..." autocomplete="off">
        <input type="hidden" name="card_num" id="card_num_pay">
        <div id="card_dropdown_pay"
            style="display:none; border:1px solid #ccc; padding:10px; max-height:200px; overflow-y:auto;">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>"
                style="padding:5px; cursor:pointer; border-bottom:1px solid #eee;">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> -
                Account: <?= htmlspecialchars($c['account_id']) ?> -
                Balance: <?= htmlspecialchars($c['balance']) ?> -
                Currency: <?= htmlspecialchars($c['currency']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>
    <label>Amount:
        <input type="text" name="amount">
    </label>
    <label>Trans type:
        <select name="trans_type">
            <option value="">Select trans type</option>
            <option value="education">education</option>
            <option value="health">health</option>
            <option value="utility">utility</option>
            <option value="internet">internet</option>
            <option value="water">water</option>
            <option value="gas">gas</option>
            <option value="electricity">electricity</option>
            <option value="goverments' tax">goverments' tax</option>
        </select>
    </label>
    <label>Description:
        <input type="text" name="description">
    </label>
    <button type="submit" name="pay">Pay</button>
</form>

<!-- =====================================================
     WITHDRAW FORM
======================================================= -->
<form method="post" class="simple-form">
    <h3>Withdraw</h3>
    <label>Card number:
        <input type="text" name="card_num" id="card_input_withdraw" placeholder="Search card..." autocomplete="off">
        <div id="card_dropdown_withdraw"
            style="display:none; border:1px solid #ccc; padding:10px; max-height:200px; overflow-y:auto;">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>"
                style="padding:5px; cursor:pointer; border-bottom:1px solid #eee;">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> -
                Account: <?= htmlspecialchars($c['account_id']) ?> -
                Balance: <?= htmlspecialchars($c['balance']) ?> -
                Currency: <?= htmlspecialchars($c['currency']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>
    <label>Amount:
        <input type="text" name="amount">
    </label>
    <label>Description:
        <input type="text" name="description">
    </label>
    <button type="submit" name="withdraw">Withdraw</button>
</form>

<!-- =====================================================
     SEARCH TRANSACTIONS
======================================================= -->
<form method="get" class="simple-form">
    <h3>Search transactions</h3>
    <input type="hidden" name="page" value="transactions">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<!-- =====================================================
     TRANSACTIONS TABLE
======================================================= -->
<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Card</th>
        <th>Transacted by</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Date</th>
        <th>Description</th>
        <th>Commission</th>
        <th>Action</th>
    </tr>
    <?php foreach ($rows as $tr): ?>
    <tr>
        <td><?= htmlspecialchars($tr['trans_id']) ?></td>
        <td><?= htmlspecialchars($tr['card_num']) ?></td>
        <td><?= htmlspecialchars($tr['transacted_by']) ?></td>
        <td><?= htmlspecialchars($tr['trans_type']) ?></td>
        <td><?= htmlspecialchars($tr['amount']) ?></td>
        <td><?= htmlspecialchars($tr['trans_date']) ?></td>
        <td><?= htmlspecialchars($tr['description']) ?></td>
        <td><?= htmlspecialchars($tr['commission']) ?></td>
        <td>
            <?php if ($tr['trans_type'] !== 'deposit' && $tr['trans_type'] !== 'withdraw'): ?>
            <form method="post">
                <input type="hidden" name="trans_id" value="<?= htmlspecialchars($tr['trans_id']) ?>">
                <button type="submit" name="cancel_payment">Cancel</button>
            </form>
            <?php else: ?>
            -
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No transactions found.</p>
<?php endif; ?>

<script>
// Reusable function for card input dropdown
function setupCardDropdown(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.card_option');

    input.addEventListener('click', () => {
        dropdown.style.display = 'block';
        filterOptions();
    });

    input.addEventListener('input', filterOptions);

    function filterOptions() {
        const query = input.value.toLowerCase();
        options.forEach(option => {
            const text = option.textContent.toLowerCase();
            option.style.display = text.includes(query) ? 'block' : 'none';
        });
    }

    options.forEach(option => {
        option.addEventListener('click', () => {
            input.value = option.dataset.num;
            document.getElementById(
                inputId.replace('card_input', 'card_num')
            ).value = option.dataset.num;

            dropdown.style.display = 'none';
        });
    });

    document.addEventListener('click', (event) => {
        if (!input.contains(event.target) && !dropdown.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
}

setupCardDropdown('card_input_deposit', 'card_dropdown_deposit');
setupCardDropdown('card_input_pay', 'card_dropdown_pay');
setupCardDropdown('card_input_withdraw', 'card_dropdown_withdraw');
</script>