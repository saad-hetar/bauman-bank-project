<?php
// public/customer/deposits.php

// make sure session & customer exist even if this file is opened directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// only customers
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/customer.class.php';

if (!isset($customer) || !($customer instanceof customer)) {
    $customer_id = $_SESSION['user_id'] ?? null;
    if ($customer_id === null) {
        header('Location: ../auth/login.php');
        exit;
    }
    $customer = new customer($customer_id);
}

$account_id = $_SESSION['account_id'];


// message can come from redirect (?msg=...)
$message = $_GET['msg'] ?? '';

// ---------- HANDLE POST ----------
// Only CREATE saving deposit is allowed now
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['create_deposit'])) {
        $card_num      = $_POST['card_num'] ?? '';
        $amount        = $_POST['amount'] ?? '';
        $currency      = $_POST['currency'] ?? '';
        $deposit_type  = $_POST['deposit_type'] ?? '';
        $period_months = $_POST['period_months'] ?? '';

        $message = $customer->create_saving_deposit(
            $card_num, $account_id, $amount, $currency, $deposit_type, $period_months
        );
    }

    // WITHDRAW saving deposit (keep inline as requested)
    if (isset($_POST['withdraw_deposit'])) {
        $id       = $_POST['deposit_id'] ?? '';
        $card_num = $_POST['card_num'] ?? '';
        $message  = $customer->withdraw_saving_deposit($id, $card_num);
    }

    // ----- POST-REDIRECT-GET: avoid duplicate creation on refresh -----
    $redirect = $_SERVER['PHP_SELF'];
    $params   = [];

    // preserve ?page=deposits if used in your customer router
    if (isset($_GET['page'])) {
        $params['page'] = $_GET['page'];
    }

    if ($message !== '') {
        $params['msg'] = $message;
    }

    if (!empty($params)) {
        $redirect .= '?' . http_build_query($params);
    }

    header('Location: ' . $redirect);
    exit;
}


// ---------- SEARCH / LIST (GET) ----------
$q = $_GET['q'] ?? '';
if ($q !== '') {
    $rows = $customer->search_saving_deposit($q);
} else {
    $rows = $customer->read_customer_saving_deposit($account_id);
}

// CARD SEARCH
$search_card = $_GET['search_card'] ?? '';
if ($search_card !== '') {
    $card_rows = $customer->search_card($search_card);
} else {
    $card_rows = $customer->read_customer_cards($account_id);
}

// Selected card for forms
$selected_card_num = $_GET['selected_card'] ?? '';
?>

<style>
.dropdown {
    display: none;
    border: 1px solid #ccc;
    padding: 10px;
    max-height: 200px;
    overflow-y: auto;
    background: #fff;
    position: absolute;
    z-index: 1000;
}

.card_option {
    padding: 5px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
</style>

<h2>Saving deposits</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- CREATE DEPOSIT FORM -->
<form method="post" class="simple-form">
    <h3>Create saving deposit</h3>
    <label>Card number:
        <input type="text" name='card_num' id="card_input_deposit" placeholder="Search card..." autocomplete="off"
            value="<?= htmlspecialchars($selected_card_num) ?>">
        <div id="card_dropdown_deposit"
            style="display:none; border:1px solid #ccc; padding:10px; max-height:200px; overflow-y:auto;">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>"
                style="padding:5px; cursor:pointer; border-bottom:1px solid #eee;">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> -
                Account: <?= htmlspecialchars($c['account_id']) ?> -
                Balance: <?= htmlspecialchars($c['balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Amount:
        <input type="text" name="amount">
    </label>
    <label>Currency:
        <select name="currency">
            <option value="">Select Currency</option>
            <option value="rub">rub</option>
            <option value="usd">usd</option>
            <option value="eur">eur</option>
        </select>
    </label>
    <label>Deposit type:
        <select name="deposit_type">
            <option value="">Select deposit type</option>
            <option value="term">term</option>
            <option value="demand">demand</option>
            <option value="savings">savings</option>
        </select>
    </label>
    <label>Period (months):
        <input type="text" name="period_months">
    </label>
    <button type="submit" name="create_deposit">Create deposit</button>
</form>

<!-- SEARCH FORM -->
<form method="get" class="simple-form">
    <h3>Search deposits</h3>
    <input type="hidden" name="page" value="deposits">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Account</th>
        <th>Amount</th>
        <th>Currency</th>
        <th>Type</th>
        <th>Status</th>
        <th>Period</th>
        <th>Start date</th>
        <th>End date</th>
        <th>withdraw</th>
    </tr>
    <?php foreach ($rows as $d): ?>
    <tr>
        <td><?= htmlspecialchars($d['deposit_id']) ?></td>
        <td><?= htmlspecialchars($d['account_id'] ?? '') ?></td>
        <td><?= htmlspecialchars($d['amount']) ?></td>
        <td><?= htmlspecialchars($d['currency']) ?></td>
        <td><?= htmlspecialchars($d['deposit_type']) ?></td>
        <td><?= htmlspecialchars($d['status']) ?></td>
        <td><?= htmlspecialchars($d['period_months']) ?></td>
        <td><?= htmlspecialchars($d['start_date']) ?></td>
        <td><?= htmlspecialchars($d['end_date']) ?></td>

        <!-- WITHDRAW on separate column (keep inline as requested) -->
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="deposit_id" value="<?= htmlspecialchars($d['deposit_id']) ?>">
                <input type="text" name="card_num" placeholder="card num" size="10">
                <button type="submit" name="withdraw_deposit">Withdraw</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No deposits found.</p>
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
            input.value = option.dataset.num; // set the card number
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
</script>

<script>
function setupPreloadedDropdown(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.card_option');

    input.addEventListener('click', () => {
        dropdown.style.display = 'block';
        filterOptions();
    });

    input.addEventListener('input', filterOptions);

    function filterOptions() {
        const q = input.value.toLowerCase();
        options.forEach(op => {
            op.style.display = op.textContent.toLowerCase().includes(q) ?
                'block' :
                'none';
        });
    }

    options.forEach(op => {
        op.addEventListener('click', () => {
            input.value = op.dataset.id; // account_id
            dropdown.style.display = 'none';
        });
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

setupPreloadedDropdown('account_id_input', 'account_id_dropdown');
</script>