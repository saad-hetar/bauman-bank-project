<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

$admin = new admin($_SESSION['user_id'] ?? 0);
echo $admin->last_transfer_id;

$message = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trans_type  = $_POST['trans_type'] ?? '';
    $currency    = $_POST['currency'] ?? '';
    $amount      = $_POST['amount'] ?? '';
    $description = $_POST['description'] ?? '';

    if (isset($_POST['internal_card'])) {
        $message = $admin->transfer_internal_card(
            $_POST['sender_card_num'] ?? '',
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_card_num'] ?? ''
        );
    }

    if (isset($_POST['external_card'])) {
        $message = $admin->transfer_external_card(
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
        $message = $admin->transfer_internal_phone(
            $_POST['sender_phone'] ?? '',
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_phone'] ?? ''
        );
    }

    if (isset($_POST['external_phone'])) {
        $message = $admin->transfer_external_phone(
            $_POST['sender_phone'] ?? '',
            $trans_type,
            $currency,
            $description,
            $amount,
            $_POST['receiver_phone'] ?? '',
            $_POST['receiver_bank'] ?? ''
        );
    }

    $_SESSION['transfers_message'] = $message;
    // header('Location: index.php?page=transfers');
    $_SESSION['last_transfer_id'] = $admin->last_transfer_id;
    header('Location: transfer_last.php');
    exit;
}

// After redirect: show message once
if (isset($_SESSION['transfers_message'])) {
    $message = $_SESSION['transfers_message'];
    unset($_SESSION['transfers_message']);
}

// Search GET
$q = $_GET['q'] ?? '';
$rows = $q !== '' ? $admin->search_transfer($q) : $admin->read_all_transfer();

// Preload cards & phones for dropdowns
$all_cards = $admin->read_all_card();
$all_customers = $admin->read_all_customer();
$all_central_bank_customers = $admin->read_all_central_bank_customer();

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
</style>

<!-- =====================================================
     INTERNAL TRANSFER (CARD)
======================================================= -->
<form method="post" class="simple-form">
    <h3>Internal Transfer (Card)</h3>
    <label>Sender card:
        <input type="text" id="sender_card_internal" placeholder="Search card..." autocomplete="off"
            name="sender_card_num">
        <div id="sender_card_internal_dropdown" class="dropdown">
            <?php foreach ($all_cards as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> |
                Account: <?= htmlspecialchars($c['account_id'] ?? '') ?> |
                Balance: <?= htmlspecialchars($c['balance'] ?? '') ?> |
                Currency: <?= htmlspecialchars($c['currency'] ?? '') ?> |
                name: <?= htmlspecialchars($c['last_name'] ?? '') ?> <?= htmlspecialchars($c['first_name'] ?? '') ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver card:
        <input type="text" id="receiver_card_internal" placeholder="Search card..." autocomplete="off"
            name="receiver_card_num">
        <div id="receiver_card_internal_dropdown" class="dropdown">
            <?php foreach ($all_cards as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> |
                Account: <?= htmlspecialchars($c['account_id'] ?? '') ?> |
                Balance: <?= htmlspecialchars($c['balance'] ?? '') ?> |
                Currency: <?= htmlspecialchars($c['currency'] ?? '') ?> |
                name: <?= htmlspecialchars($c['last_name'] ?? '') ?> <?= htmlspecialchars($c['first_name'] ?? '') ?>
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
    <button type="submit" name="internal_card">Send Internal (Card)</button>
</form>

<!-- =====================================================
     EXTERNAL TRANSFER (CARD)
======================================================= -->
<form method="post" class="simple-form">
    <h3>External Transfer (Card)</h3>
    <label>Sender card:
        <input type="text" id="sender_card_external" placeholder="Search card..." autocomplete="off"
            name="sender_card_num">
        <div id="sender_card_external_dropdown" class="dropdown">
            <?php foreach ($all_cards as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> |
                Account: <?= htmlspecialchars($c['account_id'] ?? '') ?> |
                Balance: <?= htmlspecialchars($c['balance'] ?? '') ?> |
                Currency: <?= htmlspecialchars($c['currency'] ?? '') ?> |
                name: <?= htmlspecialchars($c['last_name'] ?? '') ?> <?= htmlspecialchars($c['first_name'] ?? '') ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver card:
        <input type="text" id="receiver_card_external" placeholder="Search central bank customer..." autocomplete="off"
            name="receiver_card_num">
        <div id="receiver_card_external_dropdown" class="dropdown">
            <?php foreach ($all_central_bank_customers as $c): ?>
            <div class="card_option" data-num="<?= htmlspecialchars($c['card_num']) ?>">
                Card: <strong><?= htmlspecialchars($c['card_num']) ?></strong> |
                Customer: <?= htmlspecialchars($c['last_name'].' '.$c['first_name']) ?> |
                Bank: <?= htmlspecialchars($c['bank_name']) ?> |
                Balance: <?= htmlspecialchars($c['customer_balance']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver bank: <select name="receiver_bank">
            <option value="">Select bank</option>
            <option value="sber">sber</option>
            <option value="global finance">global finance</option>
            <option value="eurotrust">eurotrust</option>
        </select></label>
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
    <button type="submit" name="external_card">Send External (Card)</button>
</form>

<!-- =====================================================
     INTERNAL TRANSFER (PHONE)
======================================================= -->
<form method="post" class="simple-form">
    <h3>Internal Transfer (Phone)</h3>
    <label>Sender phone:
        <input type="text" id="sender_phone_internal" placeholder="Search customer..." autocomplete="off"
            name="sender_phone">
        <div id="sender_phone_internal_dropdown" class="dropdown">
            <?php foreach ($all_customers as $c): ?>
            <div class="customer_option" data-id="<?= htmlspecialchars($c['phone']) ?>">
                phone:
                <?= htmlspecialchars($c['phone'].' | name: '.$c['last_name'].' '.$c['first_name']).' | customer id: '.$c['customer_id'] ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver phone:
        <input type="text" id="receiver_phone_internal" placeholder="Search customer..." autocomplete="off"
            name="receiver_phone">
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
    <button type="submit" name="internal_phone">Send Internal (Phone)</button>
</form>

<!-- =====================================================
     EXTERNAL TRANSFER (PHONE)
======================================================= -->
<form method="post" class="simple-form">
    <h3>External Transfer (Phone)</h3>
    <label>Sender phone:
        <input type="text" id="sender_phone_external" placeholder="Search customer..." autocomplete="off"
            name="sender_phone">
        <div id="sender_phone_external_dropdown" class="dropdown">
            <?php foreach ($all_customers as $c): ?>
            <div class="customer_option" data-id="<?= htmlspecialchars($c['phone']) ?>">
                phone:
                <?= htmlspecialchars($c['phone'].' | name: '.$c['last_name'].' '.$c['first_name']).' | customer id: '.$c['customer_id'] ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver phone:
        <input type="text" id="receiver_phone_external" placeholder="Search central bank customer..." autocomplete="off"
            name="receiver_phone">
        <div id="receiver_phone_external_dropdown" class="dropdown">
            <?php foreach ($all_central_bank_customers as $c): ?>
            <div class="customer_option" data-id="<?= htmlspecialchars($c['phone']) ?>">
                <?= htmlspecialchars($c['phone'].' | '.$c['last_name'].' '.$c['first_name'].' | '.$c['bank_name']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Receiver bank: <select name="receiver_bank">
            <option value="">Select bank</option>
            <option value="sber">sber</option>
            <option value="global finance">global finance</option>
            <option value="eurotrust">eurotrust</option>
        </select></label>
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
    <button type="submit" name="external_phone">Send External (Phone)</button>
</form>

<!-- =====================================================
     SEARCH TRANSFERS
======================================================= -->
<form method="get" class="simple-form">
    <h3>Search transfers</h3>
    <input type="hidden" name="page" value="transfers">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<!-- =====================================================
     TRANSFERS TABLE
======================================================= -->
<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>Transfer ID</th>
        <th>Sender Card</th>
        <th>Sender Phone</th>
        <th>Transfer Type</th>
        <th>Amount</th>
        <th>Currency</th>
        <th>Commission</th>
        <th>Receiver Card</th>
        <th>Receiver Phone</th>
        <th>Transferred By</th>
        <th>Receiver Bank</th>
        <th>Date</th>
        <th>Description</th>
    </tr>
    <?php foreach ($rows as $tr): ?>
    <tr>
        <td><?= htmlspecialchars($tr['trans_id'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['sender_card_num'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['sender_phone'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['trans_type'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['amount'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['currency'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['commission'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['receiver_card_num'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['receiver_phone'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['transfered_by'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['receiver_bank'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['trans_date'] ?? '') ?></td>
        <td><?= htmlspecialchars($tr['description'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No transfers found.</p>
<?php endif; ?>

<script>
function setupPreloadedDropdown(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.card_option, .customer_option');

    // show dropdown on click
    input.addEventListener('click', () => {
        dropdown.style.display = 'block';
        filterOptions();
    });

    // live filter while typing
    input.addEventListener('input', filterOptions);

    function filterOptions() {
        const query = input.value.toLowerCase();
        options.forEach(option => {
            option.style.display = option.textContent.toLowerCase().includes(query) ? 'block' : 'none';
        });
    }

    // click on option sets input value
    options.forEach(option => {
        option.addEventListener('click', () => {
            input.value = option.dataset.num || option.dataset.id || '';
            dropdown.style.display = 'none';
        });
    });

    // hide dropdown if clicking outside
    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Setup all dropdowns
setupPreloadedDropdown('sender_card_internal', 'sender_card_internal_dropdown');
setupPreloadedDropdown('receiver_card_internal', 'receiver_card_internal_dropdown');
setupPreloadedDropdown('sender_card_external', 'sender_card_external_dropdown');
setupPreloadedDropdown('receiver_card_external', 'receiver_card_external_dropdown');
setupPreloadedDropdown('sender_phone_internal', 'sender_phone_internal_dropdown');
setupPreloadedDropdown('receiver_phone_internal', 'receiver_phone_internal_dropdown');
setupPreloadedDropdown('sender_phone_external', 'sender_phone_external_dropdown');
setupPreloadedDropdown('receiver_phone_external', 'receiver_phone_external_dropdown');
</script>