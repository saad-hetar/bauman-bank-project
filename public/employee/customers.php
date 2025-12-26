<?php
// public/employee/customers.php

// make sure session & employee exist even if this file is opened directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// only employees
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/employee.class.php';

if (!isset($employee) || !($employee instanceof employee)) {
    $employee_id = $_SESSION['user_id'] ?? null;
    if ($employee_id === null) {
        header('Location: ../auth/login.php');
        exit;
    }
    $employee = new employee($employee_id);
}

// message can come from redirect (?msg=...)
$message      = $_GET['msg'] ?? '';
$lastCustomer = null;

// ---------- HANDLE POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE customer (passport + customer + account + login)
    if (isset($_POST['create_customer'])) {

        // passport info
        $last_name   = $_POST['last_name']   ?? '';
        $first_name  = $_POST['first_name']  ?? '';
        $middle_name = $_POST['middle_name'] ?? '';
        $passport_num    = $_POST['passport_num']    ?? '';
        $passport_series = $_POST['passport_series'] ?? '';
        $nationality     = $_POST['nationality']     ?? '';
        $passport_type   = $_POST['passport_type']   ?? '';
        $birth_date      = $_POST['birth_date']      ?? '';
        $birth_place     = $_POST['birth_place']     ?? '';
        $gender          = $_POST['gender']          ?? '';
        $issue_date      = $_POST['issue_date']      ?? '';
        $expire_date     = $_POST['expire_date']     ?? '';
        $authority       = $_POST['assuing_authority'] ?? '';
        $owner           = 'customer';

        // customer + account info
        $address      = $_POST['address']      ?? '';
        $phone        = $_POST['phone']        ?? '';
        $email        = $_POST['email']        ?? '';
        $account_type = $_POST['account_type'] ?? '';
        $currency     = $_POST['currency']     ?? '';

        $msg1 = $employee->create_passport(
            $last_name, $first_name, $middle_name,
            $passport_num, $passport_series, $nationality, $passport_type,
            $birth_date, $birth_place, $gender, $issue_date, $expire_date,
            $authority, $owner
        );

        $msg2 = $employee->create_customer($address, $phone, $email, $account_type, $currency);

        $message = $msg1 . ' | ' . $msg2;

        header('Location: index.php?page=customers');

        $_SESSION['last_customer_id'] = $employee->last_customer_id;
        $_SESSION['password'] = $employee->last_password;
        header('Location: customer_last.php');
        exit;
    }

    // UPDATE customer (only address/phone/email) – done on separate page now
    if (isset($_POST['update_customer'])) {
        $cid     = $_POST['customer_id'] ?? '';
        $address = $_POST['address']     ?? '';
        $phone   = $_POST['phone']       ?? '';
        $email   = $_POST['email']       ?? '';

        $message = $employee->update_customer($cid, $address, $phone, $email);
    }

    // DELETE customer (passport + login)
    if (isset($_POST['delete_customer'])) {
        $passport_id = $_POST['passport_id'] ?? '';
        $login_id    = $_POST['login_id']    ?? '';
        $message     = $employee->delete_customer($passport_id, $login_id);
    }

    // ----- POST-REDIRECT-GET: avoid duplicate creation on refresh -----
    $redirect = $_SERVER['PHP_SELF'];
    $params   = [];

    // preserve ?page=customers if used in your employee router
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
    $rows = $employee->search_customer($q);
} else {
    $rows = $employee->read_all_customer();
}

// // always show last customer in DB (last created)
// $lastCustomer = $employee->get_last_customer();
?>

<h2>Customers</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- CREATE CUSTOMER FORM -->
<form method="post" class="simple-form">
    <h3>Create customer (passport + customer + account + login)</h3>

    <label>Last name: <input type="text" name="last_name"></label>
    <label>First name: <input type="text" name="first_name"></label>
    <label>Middle name: <input type="text" name="middle_name"></label>
    <label>Passport number: <input type="text" name="passport_num"></label>
    <label>Passport series: <input type="text" name="passport_series"></label>
    <label>Nationality: <input type="text" name="nationality"></label>
    <label>Passport type: <input type="text" name="passport_type"></label>
    <label>Birth date: <input type="date" name="birth_date"></label>
    <label>Birth place: <input type="text" name="birth_place"></label>
    <label>Gender: <input type="text" name="gender"></label>
    <label>Issue date: <input type="date" name="issue_date"></label>
    <label>Expire date: <input type="date" name="expire_date"></label>
    <label>Issuing authority: <input type="text" name="assuing_authority"></label>

    <label>Address: <input type="text" name="address"></label>
    <label>Phone: <input type="text" name="phone"></label>
    <label>Email: <input type="email" name="email"></label>
    <label>Account type
        <select name="account_type">
            <option value="">Select account type</option>
            <option value="personal">Personal</option>
            <option value="business">Business</option>
        </select>
    </label>
    <label>Currency: <select name="currency">
            <option value="">Select Currency</option>
            <option value="rub">rub</option>
            <option value="usd">usd</option>
            <option value="eur">eur</option>
        </select>
    </label>

    <button type="submit" name="create_customer">Create customer</button>
</form>

<!-- SEARCH FORM -->
<form method="get" class="simple-form">
    <h3>Search customers</h3>
    <input type="hidden" name="page" value="customers">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<!-- SHOW LAST CREATED CUSTOMER (now near search) -->
<!-- <?php if (is_array($lastCustomer) && count($lastCustomer) > 0): ?>
<?php $lc = $lastCustomer[0]; ?>
<div class="msg">
    <strong>Last created customer:</strong>
    ID <?= htmlspecialchars($lc['customer_id'] ?? '') ?>,
    Passport <?= htmlspecialchars($lc['passport_id'] ?? '') ?>,
    <?= htmlspecialchars(($lc['last_name'] ?? '') . ' ' . ($lc['first_name'] ?? '')) ?>,
    phone <?= htmlspecialchars($lc['phone'] ?? '') ?>,
    email <?= htmlspecialchars($lc['email'] ?? '') ?>
</div>
<?php endif; ?> -->

<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>Customer ID</th>
        <th>Passport ID</th>
        <!-- <th>Login ID</th> -->
        <!-- <th>password</th> -->
        <th>Name</th>
        <th>Gender</th>
        <!-- <th>Nationality</th> -->
        <!-- <th>Passport type</th>
        <th>Birth date</th>
        <th>Birth place</th> -->
        <th>Address</th>
        <th>Phone</th>
        <th>Email</th>
        <!-- <th>Role</th> -->
        <th>Customer created by</th>
        <th>Customer created at</th>
        <th>Customer updated by</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
    <?php foreach ($rows as $c): ?>
    <tr>
        <td><?= htmlspecialchars($c['customer_id']) ?></td>
        <td><?= htmlspecialchars($c['passport_id']) ?></td>
        <!-- <td><?= htmlspecialchars($c['login_id']   ?? '') ?></td>
        <td><?= htmlspecialchars($c['password_hash']   ?? '') ?></td> -->

        <td><?= htmlspecialchars(($c['last_name'] ?? '') . ' ' . ($c['first_name'] ?? '') . ' ' . ($c['middle_name'] ?? '')) ?>
        </td>
        <td><?= htmlspecialchars($c['gender']        ?? '') ?></td>
        <!-- <td><?= htmlspecialchars($c['nationality']   ?? '') ?></td>
        <td><?= htmlspecialchars($c['passport_type'] ?? '') ?></td>
        <td><?= htmlspecialchars($c['birth_date']    ?? '') ?></td>
        <td><?= htmlspecialchars($c['birth_place']   ?? '') ?></td> -->

        <td><?= htmlspecialchars($c['address'] ?? $c['adress'] ?? '') ?></td>
        <td><?= htmlspecialchars($c['phone']   ?? '') ?></td>
        <td><?= htmlspecialchars($c['email']   ?? '') ?></td>
        <!-- <td><?= htmlspecialchars($c['role']    ?? '') ?></td> -->

        <td><?= htmlspecialchars($c['created_by']   ?? '') ?></td>
        <td><?= htmlspecialchars($c['created_at']   ?? '') ?></td>
        <td><?= htmlspecialchars($c['updated_by']   ?? '') ?></td>

        <!-- EDIT on separate column -->
        <td>
            <a href="customers_edit.php?customer_id=<?= urlencode($c['customer_id']) ?>">Edit</a>
        </td>

        <!-- DELETE using delete_customer($passport_id, $login_id) on separate column -->
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="passport_id" value="<?= htmlspecialchars($c['passport_id']) ?>">
                <input type="hidden" name="login_id" value="<?= htmlspecialchars($c['login_id'] ?? '') ?>">
                <button type="submit" name="delete_customer"
                    onclick="return confirm('Delete this customer (passport + login)?');">
                    Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No customers found.</p>
<?php endif; ?>