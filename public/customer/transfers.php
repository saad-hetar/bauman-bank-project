<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../classes/customer.class.php';

$customer = new customer($_SESSION['user_id'] ?? 0);
$message  = '';

$phone = $_SESSION['phone'] ?? '';

/* ===============================
   HANDLE POST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $trans_type  = $_POST['trans_type'] ?? '';
    $currency    = $_POST['currency'] ?? '';
    $amount      = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';

    if (isset($_POST['internal_card'])) {
        $message = $customer->transfer_internal_card(
            $_POST['sender_card_num'] ?? '',
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_card_num'] ?? ''
        );
    }

    if (isset($_POST['external_card'])) {
        $message = $customer->transfer_external_card(
            $_POST['sender_card_num'] ?? '',
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_card_num'] ?? '',
            $_POST['receiver_bank'] ?? ''
        );
    }

    if (isset($_POST['internal_phone'])) {
        $message = $customer->transfer_internal_phone(
            $phone,
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_phone'] ?? ''
        );
    }

    if (isset($_POST['external_phone'])) {
        $message = $customer->transfer_external_phone(
            $phone,
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_phone'] ?? '',
            $_POST['receiver_bank'] ?? ''
        );
    }

    $_SESSION['transfers_message'] = $message;
    $_SESSION['last_transfer_id']  = $customer->last_transfer_id ?? null;

    header('Location: transfer_last.php');
    exit;
}

/* ===============================
   FLASH MESSAGE
================================ */
if (isset($_SESSION['transfers_message'])) {
    $message = $_SESSION['transfers_message'];
    unset($_SESSION['transfers_message']);
}

/* ===============================
   PRELOAD DATA FOR DROPDOWNS
================================ */
$all_cards                  = $customer->read_all_card();
$customer_cards             = $customer->read_customer_cards($_SESSION['account_id'] ?? 0);
$all_central_bank_customers = $customer->read_all_central_bank_customer();
$all_customers = $customer->read_all_customer();
?>

<h2>Transfers</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

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

.card_option,
.customer_option {
    padding: 5px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.card_option:hover,
.customer_option:hover {
    background: #f2f2f2;
}
</style>

<!-- =====================================================
     INTERNAL TRANSFER (CARD)
======================================================= -->
<form method="post" class="simple-form">
    <h3>Internal Transfer (Card)</h3>

    <label>Sender card:
        <input type="text" id="sender_card_internal" placeholder="select card..." autocomplete="off"
            name="sender_card_num">
        <div id="sender_card_internal_dropdown" class="dropdown">
            <?php foreach ($customer_cards as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                card number: <?= htmlspecialchars($c['card_num']) ?> |
                balance: <?= htmlspecialchars($c['balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver card:
        <input type="text" id="receiver_card_internal" placeholder="select card..." autocomplete="off"
            name="receiver_card_num">
        <div id="receiver_card_internal_dropdown" class="dropdown">
            <?php foreach ($all_cards as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                <?= htmlspecialchars($c['card_num']) ?> |
                <?= htmlspecialchars($c['last_name'].' '.$c['first_name']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Currency:
        <select name="currency">
            <option value="">Select Currency</option>
            <option value="rub">rub</option>
            <option value="usd">usd</option>
            <option value="eur">eur</option>
        </select>
    </label>

    <label>Amount: <input type="text" name="amount"></label>
    <label>Description: <input type="text" name="description"></label>

    <input type="hidden" name="trans_type" value="internal">
    <button type="submit" name="internal_card">Send</button>
</form>

<!-- =====================================================
     EXTERNAL TRANSFER (CARD)
======================================================= -->
<form method="post" class="simple-form">
    <h3>External Transfer (Card)</h3>

    <label>Sender card:
        <input type="text" id="sender_card_external" placeholder="select card..." autocomplete="off"
            name="sender_card_num">
        <div id="sender_card_external_dropdown" class="dropdown">
            <?php foreach ($customer_cards as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                card number: <?= htmlspecialchars($c['card_num']) ?> |
                balance: <?= htmlspecialchars($c['balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver card:
        <input type="text" id="receiver_card_external" placeholder="select card..." autocomplete="off"
            name="receiver_card_num">
        <div id="receiver_card_external_dropdown" class="dropdown">
            <?php foreach ($all_central_bank_customers as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                <?= htmlspecialchars($c['card_num']) ?> |
                <?= htmlspecialchars($c['bank_name']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver bank:
        <select name="receiver_bank">
            <option value="">Select bank</option>
            <option value="sber">sber</option>
            <option value="global finance">global finance</option>
            <option value="eurotrust">eurotrust</option>
        </select>
    </label>

    <label>Currency:
        <select name="currency">
            <option value="">Select Currency</option>
            <option value="rub">rub</option>
            <option value="usd">usd</option>
            <option value="eur">eur</option>
        </select>
    </label>

    <label>Amount: <input type="text" name="amount"></label>
    <label>Description: <input type="text" name="description"></label>

    <input type="hidden" name="trans_type" value="external">
    <button type="submit" name="external_card">Send</button>
</form>

<!-- =====================================================
     INTERNAL TRANSFER (PHONE)
======================================================= -->
<form method="post" class="simple-form">
    <h3>Internal Transfer (Phone)</h3>

    <label>Receiver phone:
        <input type="text" id="receiver_phone_internal" name="receiver_phone">
        <div id="receiver_phone_internal_dropdown" class="dropdown">
            <?php foreach ($all_customers as $c): ?>
            <div class="customer_option" data-id="<?= htmlspecialchars($c['phone']) ?>">
                <?= htmlspecialchars($c['phone'].' | '.$c['last_name'].' '.$c['first_name']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Currency:
        <select name="currency">
            <option value="">Select Currency</option>
            <option value="rub">rub</option>
            <option value="usd">usd</option>
            <option value="eur">eur</option>
        </select>
    </label>

    <label>Amount: <input type="text" name="amount"></label>
    <label>Description: <input type="text" name="description"></label>

    <input type="hidden" name="trans_type" value="internal">
    <button type="submit" name="internal_phone">Send</button>
</form>

<!-- =====================================================
     EXTERNAL TRANSFER (PHONE)
======================================================= -->
<form method="post" class="simple-form">
    <h3>External Transfer (Phone)</h3>


    <label>Receiver phone:
        <input type="text" id="receiver_phone_external" name="receiver_phone">
        <div id="receiver_phone_external_dropdown" class="dropdown">
            <?php foreach ($all_customers as $c): ?>
            <div class="customer_option" data-id="<?= htmlspecialchars($c['phone']) ?>">
                <?= htmlspecialchars($c['phone'].' | '.$c['last_name'].' '.$c['first_name']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver bank:
        <select name="receiver_bank">
            <option value="">Select bank</option>
            <option value="sber">sber</option>
            <option value="global finance">global finance</option>
            <option value="eurotrust">eurotrust</option>
        </select>
    </label>

    <label>Currency:
        <select name="currency">
            <option value="">Select Currency</option>
            <option value="rub">rub</option>
            <option value="usd">usd</option>
            <option value="eur">eur</option>
        </select>
    </label>

    <label>Amount: <input type="text" name="amount"></label>
    <label>Description: <input type="text" name="description"></label>

    <input type="hidden" name="trans_type" value="external">
    <button type="submit" name="external_phone">Send</button>
</form>

<script>
function setupPreloadedDropdown(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.card_option, .customer_option');

    input.addEventListener('focus', () => dropdown.style.display = 'block');
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase();
        options.forEach(o => {
            o.style.display = o.textContent.toLowerCase().includes(q) ?
                'block' :
                'none';
        });
    });

    options.forEach(o => {
        o.addEventListener('click', () => {
            input.value = o.dataset.num || o.dataset.id || '';
            dropdown.style.display = 'none';
        });
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

['receiver_phone_internal', 'receiver_phone_external'].forEach(id =>
    setupPreloadedDropdown(id, id + '_dropdown')
);

// Setup dropdowns for all sender and receiver fields
['sender_card_internal', 'receiver_card_internal', 'sender_card_external', 'receiver_card_external',
    'sender_phone_internal', 'receiver_phone_internal', 'sender_phone_external', 'receiver_phone_external'
]

.forEach(id => setupPreloadedDropdown(id, id + '_dropdown'));
</script>