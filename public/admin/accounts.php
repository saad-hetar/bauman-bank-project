<?php
// public/admin/accounts.php

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

// message from redirect (PRG)
$message = $_GET['msg'] ?? '';

/* -------------------------------------------------------
   POST: CREATE + DELETE
------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE account
    if (isset($_POST['create_account'])) {
        $customer_id  = $_POST['customer_id']  ?? '';
        $account_type = $_POST['account_type'] ?? '';
        $currency     = $_POST['currency']     ?? '';

        $message = $admin->create_account($customer_id, $account_type, $currency);

        // header('Location: index.php?page=accounts&msg=' . urlencode($message));
        $_SESSION['last_account_id'] = $admin->last_account_id;
        header('Location: account_last.php');
        exit;
    }

    // DELETE account
    if (isset($_POST['delete_account'])) {
        $account_id = $_POST['account_id'] ?? '';

        $message = $admin->delete_account($account_id);

        header('Location: index.php?page=accounts&msg=' . urlencode($message));
        exit;
    }
}

/* -------------------------------------------------------
   SEARCH ACCOUNTS
------------------------------------------------------- */
$q = $_GET['q'] ?? '';
if ($q !== '') {
    $rows = $admin->search_account($q);
} else {
    $rows = $admin->read_all_account();
}

/* -------------------------------------------------------
   EMBEDDED CUSTOMER SEARCH
------------------------------------------------------- */
$search_customer = $_GET['search_customer'] ?? '';

if ($search_customer !== '') {
    $customer_rows = $admin->search_customer($search_customer);
} else {
    $customer_rows = $admin->read_all_customer();
}

// Selected customer (fills the create form)
$selected_customer_id = $_GET['selected_customer'] ?? '';

?>

<h2>Accounts</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>


<!-- =====================================================
     CREATE ACCOUNT FORM
======================================================= -->
<form method="post" class="simple-form">
    <h3>Create account</h3>

    <label>Customer id:
        <input type="text" name="customer_id" id="customer_input" placeholder="Search customer..." autocomplete="off"
            value="<?= htmlspecialchars($selected_customer_id) ?>">

        <div id="customer_dropdown"
            style="display:none; border:1px solid #ccc; padding:10px; max-height:200px; overflow-y:auto;">
            <?php foreach ($customer_rows as $c): ?>
            <div class="customer_option" data-id="<?= htmlspecialchars($c['customer_id']) ?>"
                style="padding:5px; cursor:pointer; border-bottom:1px solid #eee;">
                cusomer id: <strong><?= htmlspecialchars($c['customer_id']) ?></strong> -
                name: <?= htmlspecialchars($c['last_name']) ?> <?= htmlspecialchars($c['first_name']) ?> |
                passport id: <?= htmlspecialchars($c['passport_id']) ?> |
                email: <?= htmlspecialchars($c['email']) ?> |
                phone: <?= htmlspecialchars($c['phone']) ?>
            </div>
            <?php endforeach; ?>
        </div>

    </label>

    <label>Account type:
        <select name="account_type">
            <option value="">Select account type</option>
            <option value="personal">Personal</option>
            <option value="business">Business</option>
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

    <button type="submit" name="create_account">Create</button>
</form>


<!-- =====================================================
     ACCOUNT SEARCH FORM
======================================================= -->
<form method="get" class="simple-form">
    <h3>Search accounts</h3>
    <input type="hidden" name="page" value="accounts">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>


<!-- =====================================================
     ACCOUNT LIST TABLE
======================================================= -->
<?php if (is_array($rows) && count($rows) > 0): ?>

<table>
    <tr>
        <th>Account ID</th>
        <th>Customer ID</th>
        <th>Passport ID</th>
        <th>Last name</th>
        <th>First name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Customer created at</th>
        <th>Customer updated by</th>
        <th>Account type</th>
        <th>Account status</th>
        <th>Currency</th>
        <th>Account created by</th>
        <th>Account updated by</th>
        <th>Account created at</th>
        <th>Closed at</th>
        <th>Update</th>
        <th>Delete</th>
    </tr>

    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['account_id']) ?></td>
        <td><?= htmlspecialchars($r['customer_id']) ?></td>
        <td><?= htmlspecialchars($r['passport_id']) ?></td>
        <td><?= htmlspecialchars($r['last_name']) ?></td>
        <td><?= htmlspecialchars($r['first_name']) ?></td>
        <td><?= htmlspecialchars($r['email']) ?></td>
        <td><?= htmlspecialchars($r['phone']) ?></td>
        <td><?= htmlspecialchars($r['created_at']) ?></td>
        <td><?= htmlspecialchars($r['updated_by']) ?></td>
        <td><?= htmlspecialchars($r['account_type']) ?></td>
        <td><?= htmlspecialchars($r['account_status']) ?></td>
        <td><?= htmlspecialchars($r['currency']) ?></td>
        <td><?= htmlspecialchars($r['account_created_by'] ?? $r['created_by']) ?></td>
        <td><?= htmlspecialchars($r['account_updated_by'] ?? $r['updated_by']) ?></td>
        <td><?= htmlspecialchars($r['account_created_at'] ?? $r['created_at']) ?></td>
        <td><?= htmlspecialchars(substr((string)$r['closed_at'], 0, 10)) ?></td>

        <td>
            <a href="accounts_edit.php?account_id=<?= urlencode($r['account_id']) ?>">
                Edit
            </a>
        </td>

        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="account_id" value="<?= htmlspecialchars($r['account_id']) ?>">
                <button type="submit" name="delete_account" onclick="return confirm('Delete this account?');">
                    Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php else: ?>
<p>No accounts found.</p>
<?php endif; ?>

<script>
const input = document.getElementById('customer_input');
const dropdown = document.getElementById('customer_dropdown');
const options = dropdown.querySelectorAll('.customer_option');

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

// Handle selecting a customer
options.forEach(option => {
    option.addEventListener('click', () => {
        input.value = option.dataset.id; // just the ID is set in input
        dropdown.style.display = 'none';
    });
});

// Hide dropdown if clicking outside
document.addEventListener('click', (event) => {
    if (!input.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.style.display = 'none';
    }
});
</script>