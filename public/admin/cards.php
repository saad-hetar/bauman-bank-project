<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// message from redirect (PRG)
$message = $_GET['msg'] ?? '';

// POST: create & delete, then REDIRECT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['create_card'])) {
        $account_id = $_POST['account_id'] ?? '';
        $message    = $admin->create_card($account_id);

        // redirect so refresh doesn't resubmit the form
        header('Location: index.php?page=cards&msg=' . urlencode($message));
        exit;
    }

    if (isset($_POST['delete_card'])) {
        $card_id = $_POST['card_id'] ?? '';
        $message = $admin->delete_card($card_id);

        // redirect after delete too
        header('Location: index.php?page=cards&msg=' . urlencode($message));
        exit;
    }
}

// GET: search / list
$q = $_GET['q'] ?? '';
if ($q !== '') {
    $cards = $admin->search_card($q);
} else {
    $accounts = $admin->read_all_account();
}

$cards = $admin->read_all_card();

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


<h2>Cards</h2>
<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" class="simple-form">
    <h3>Create card</h3>
    <label>account ID:
        <input type="text" name="account_id" id="account_id_input" placeholder="Search card..." autocomplete="off">

        <div id="account_id_dropdown" class="dropdown">
            <?php foreach ($accounts as $c): ?>
            <div class="card_option" data-id="<?= htmlspecialchars($c['account_id']) ?>">
                account id: <?= htmlspecialchars($c['account_id']) ?> |
                name: <?= htmlspecialchars($c['last_name'] . ' ' . $c['first_name']) ?> |
                account type: <?= htmlspecialchars($c['account_type']) ?> |
                currency: <?= htmlspecialchars($c['currency']) ?> |
                status: <?= htmlspecialchars($c['account_status']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <button type="submit" name="create_card">Create card</button>
</form>

<form method="get" class="simple-form">
    <h3>Search cards</h3>
    <input type="hidden" name="page" value="cards">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<?php if (is_array($cards) && count($cards) > 0): ?>
<table>
    <tr>
        <th>Card num</th>
        <th>Balance</th>
        <th>Currency</th>
        <th>Account</th>
        <th>Customer</th>
        <th>CVV</th>
        <th>Expire</th>
        <th>Action</th>
    </tr>
    <?php foreach ($cards as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['card_num']) ?></td>
        <td><?= htmlspecialchars($c['balance']) ?></td>
        <td><?= htmlspecialchars($c['currency']) ?></td>
        <td><?= htmlspecialchars($c['account_id']) ?></td>
        <td><?= htmlspecialchars($c['last_name'] . ' ' . $c['first_name']) ?></td>
        <td><?= htmlspecialchars($c['cvv']) ?></td>
        <td><?= htmlspecialchars($c['expire_date']) ?></td>
        <td>
            <form method="post">
                <input type="hidden" name="card_id" value="<?= htmlspecialchars($c['card_num']) ?>">
                <button type="submit" name="delete_card">Delete card</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No cards found.</p>
<?php endif; ?>

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