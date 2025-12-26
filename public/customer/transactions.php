<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../classes/customer.class.php';

$customer = new customer($_SESSION['user_id'] ?? 0);
$message  = '';

/* ===============================
   HANDLE POST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* -------- TRANSFER BETWEEN CARDS -------- */
    if (isset($_POST['transfer'])) {
        $card_num_from = $_POST['card_num_from'] ?? '';
        $card_num_to   = $_POST['card_num_to'] ?? '';
        $amount_from   = (float)($_POST['amount'] ?? 0);

        try {
            $exchange = $customer->exchange_between_cards(
                $card_num_from,
                $card_num_to,
                $amount_from
            );

            $amount_from = $exchange['amount_from'];
            $amount_to   = $exchange['amount_to'];

            $message = $customer->transfer_between_cards(
                $card_num_from,
                $card_num_to,
                $amount_from,
                $amount_to
            );

        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    }

    /* -------- PAY -------- */
    if (isset($_POST['pay'])) {
        $card_num   = $_POST['card_num'] ?? '';
        $amount     = $_POST['amount'] ?? '';
        $trans_type = $_POST['trans_type'] ?? '';
        $desc       = $_POST['description'] ?? '';

        $message = $customer->pay($card_num, $amount, $trans_type, $desc);
    }

    /* -------- EXCHANGE BETWEEN CARDS -------- */
    if (isset($_POST['exchange'])) {
        $card_num_from = $_POST['card_num_from_exchange'] ?? '';
        $card_num_to   = $_POST['card_num_to_exchange'] ?? '';
        $amount_from   = (float)($_POST['amount_exchange'] ?? 0);

        try {
            $exchange = $customer->exchange_between_cards(
                $card_num_from,
                $card_num_to,
                $amount_from
            );

            $amount_from = $exchange['amount_from'];
            $amount_to   = $exchange['amount_to'];

            // Here you can call transfer logic or just display a message
            $message = $customer->apply_exchange($card_num_from, $card_num_to, $amount_from, $amount_to);


        } catch (Exception $e) {
            $message = $e->getMessage();
        }
    }

    $_SESSION['transactions_message'] = $message;
    // $_SESSION['last_transaction_id']  = $customer->last_transaction_id ?? null;

    // header('Location: transaction_last.php');
    // exit;
}

/* ===============================
   MESSAGE
================================ */
if (isset($_SESSION['transactions_message'])) {
    $message = $_SESSION['transactions_message'];
    unset($_SESSION['transactions_message']);
}

/* ===============================
   LOAD CUSTOMER CARDS ONLY
================================ */
$account_id = $_SESSION['account_id'] ?? 0;
$card_rows  = $customer->read_customer_cards($account_id);
$customer_id = $_SESSION['customer_id'] ?? 0;
$card_all_rows  = $customer->read_all_customer_cards($customer_id);

// Exchange rates (for display only)
$exchangeRates = [
    'USD' => ['RUB' => 90, 'EUS' => 0.92],
    'RUB' => ['USD' => 1/90, 'EUS' => 0.01],
    'EUS' => ['USD' => 1/0.92, 'RUB' => 1/0.01] // example
];


?>

<h2>Transactions</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<h3>Current Exchange Rates</h3>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>From</th>
            <th>To</th>
            <th>Rate</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($exchangeRates as $from => $toArray): ?>
        <?php foreach ($toArray as $to => $rate): ?>
        <tr>
            <td><?= htmlspecialchars($from) ?></td>
            <td><?= htmlspecialchars($to) ?></td>
            <td><?= htmlspecialchars($rate) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- =====================================
     TRANSFER BETWEEN CARDS
===================================== -->
<form method="post" class="simple-form">
    <h3>Transfer Between Cards</h3>

    <label>From card:
        <input type="text" id="card_input_from" placeholder="select card..." autocomplete="off">
        <input type="hidden" name="card_num_from" id="card_num_from">
        <div id="card_dropdown_from" class="card_dropdown">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong>
                | Balance: <?= htmlspecialchars($c['balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>To card:
        <input type="text" id="card_input_to" placeholder="select card..." autocomplete="off">
        <input type="hidden" name="card_num_to" id="card_num_to">
        <div id="card_dropdown_to" class="card_dropdown">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong>
                | Balance: <?= htmlspecialchars($c['balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Amount:
        <input type="text" name="amount">
    </label>

    <button type="submit" name="transfer">Transfer</button>
</form>

<!-- =====================================
     PAY FORM
===================================== -->
<form method="post" class="simple-form">
    <h3>Pay</h3>

    <label>Card number:
        <input type="text" id="card_input_pay" placeholder="select card..." autocomplete="off">
        <input type="hidden" name="card_num" id="card_num_pay">
        <div id="card_dropdown_pay" class="card_dropdown">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong>
                | Balance: <?= htmlspecialchars($c['balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Amount:
        <input type="text" name="amount">
    </label>

    <label>Trans type:
        <select name="trans_type">
            <option value="">Select type</option>
            <option value="education">education</option>
            <option value="health">health</option>
            <option value="utility">utility</option>
            <option value="internet">internet</option>
            <option value="water">water</option>
            <option value="gas">gas</option>
            <option value="electricity">electricity</option>
            <option value="government tax">government tax</option>
        </select>
    </label>

    <label>Description:
        <input type="text" name="description">
    </label>

    <button type="submit" name="pay">Pay</button>
</form>

<!-- =====================================
     EXCHANGE BETWEEN CARDS
===================================== -->
<form method="post" class="simple-form">
    <h3>Exchange Between Cards</h3>

    <label>From card:
        <input type="text" id="card_input_from_exchange" placeholder="select card..." autocomplete="off">
        <input type="hidden" name="card_num_from_exchange" id="card_num_from_exchange">
        <div id="card_dropdown_from_exchange" class="card_dropdown">
            <?php foreach ($card_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong>
                | Balance: <?= htmlspecialchars($c['balance']) ?> <?= htmlspecialchars($c['currency']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>To card:
        <input type="text" id="card_input_to_exchange" placeholder="select card..." autocomplete="off">
        <input type="hidden" name="card_num_to_exchange" id="card_num_to_exchange">
        <div id="card_dropdown_to_exchange" class="card_dropdown">
            <?php foreach ($card_all_rows as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong>
                | Balance: <?= htmlspecialchars($c['balance']) ?> <?= htmlspecialchars($c['currency']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Amount:
        <input type="text" name="amount_exchange">
    </label>

    <button type="submit" name="exchange">Exchange</button>
</form>

<script>
function setupCardDropdown(inputId, dropdownId, hiddenId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const hidden = document.getElementById(hiddenId);
    const options = dropdown.querySelectorAll('.card_option');

    input.addEventListener('focus', () => dropdown.style.display = 'block');
    input.addEventListener('input', filter);

    function filter() {
        const q = input.value.toLowerCase();
        options.forEach(o => {
            o.style.display = o.textContent.toLowerCase().includes(q) ? 'block' : 'none';
        });
    }

    options.forEach(o => {
        o.addEventListener('click', () => {
            input.value = o.dataset.num;
            hidden.value = o.dataset.num;
            dropdown.style.display = 'none';
        });
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Initialize all dropdowns
setupCardDropdown('card_input_from', 'card_dropdown_from', 'card_num_from');
setupCardDropdown('card_input_to', 'card_dropdown_to', 'card_num_to');
setupCardDropdown('card_input_pay', 'card_dropdown_pay', 'card_num_pay');
setupCardDropdown('card_input_from_exchange', 'card_dropdown_from_exchange', 'card_num_from_exchange');
setupCardDropdown('card_input_to_exchange', 'card_dropdown_to_exchange', 'card_num_to_exchange');
</script>

<style>
.card_dropdown {
    display: none;
    border: 1px solid #ccc;
    max-height: 200px;
    overflow-y: auto;
}

.card_option {
    padding: 6px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.card_option:hover {
    background: #f2f2f2;
}
</style>