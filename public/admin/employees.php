<?php
// public/admin/employees.php

// make sure session & admin exist even if this file is opened directly
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// only admins
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

if (!isset($admin) || !($admin instanceof admin)) {
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id === null) {
        header('Location: ../auth/login.php');
        exit;
    }
    $admin = new admin($admin_id);
}

// message can come from redirect (?msg=...)
$message = $_GET['msg'] ?? '';

// ---------- HANDLE POST ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE employee (passport + employee + login)
    if (isset($_POST['create_employee'])) {
        // passport info
        $last_name   = $_POST['last_name'] ?? '';
        $first_name  = $_POST['first_name'] ?? '';
        $middle_name = $_POST['middle_name'] ?? '';
        $passport_num    = $_POST['passport_num'] ?? '';
        $passport_series = $_POST['passport_series'] ?? '';
        $nationality     = $_POST['nationality'] ?? '';
        $passport_type   = $_POST['passport_type'] ?? '';
        $birth_date      = $_POST['birth_date'] ?? '';
        $birth_place     = $_POST['birth_place'] ?? '';
        $gender          = $_POST['gender'] ?? '';
        $issue_date      = $_POST['issue_date'] ?? '';
        $expire_date     = $_POST['expire_date'] ?? '';
        $authority       = $_POST['assuing_authority'] ?? '';
        $owner           = $_POST['lore'];

        // employee info
        $branch_id = $_POST['branch_id'] ?? '';
        $email     = $_POST['email'] ?? '';
        $phone     = $_POST['phone'] ?? '';
        $lore      = $_POST['lore'] ?? ''; // role

        $msg1 = $admin->create_passport(
            $last_name, $first_name, $middle_name,
            $passport_num, $passport_series, $nationality, $passport_type,
            $birth_date, $birth_place, $gender, $issue_date, $expire_date,
            $authority, $owner
        );
        $msg2 = $admin->create_emp($branch_id, $email, $phone, $lore);

        $message = $msg1 . ' | ' . $msg2;

        $_SESSION['last_emp_id'] = $admin->last_emp_id;
        $_SESSION['password'] = $admin->last_password;
        header('Location: employee_last.php');
        exit;
    }

    // DELETE employee (passport + login)
    if (isset($_POST['delete_employee'])) {
        // var_dump($_POST['passport_id'], $_POST['login_id']);
        // exit;
    
        $passport_id = $_POST['passport_id'] ?? '';
        $login_id    = $_POST['login_id'] ?? '';
        $message     = $admin->delete_emp($passport_id, $login_id);
    }

    // ----- POST-REDIRECT-GET: avoid duplicate creation on refresh -----
    $redirect = $_SERVER['PHP_SELF'];
    $params   = [];

    // preserve ?page=employees if used in your admin router
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
    $rows = $admin->search_emp($q);
} else {
    $rows = $admin->read_all_emp();
}

// ---------- PRELOAD BRANCHES (for branch dropdown) ----------
$branches = $admin->read_all_branch();
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

.branch_option {
    padding: 5px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
</style>

<h2>Employees</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- CREATE EMPLOYEE FORM -->
<form method="post" class="simple-form">
    <h3>Create employee (passport + employee + login)</h3>

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

    <label>Branch ID:
        <input type="text" name="branch_id" id="branch_id_input" placeholder="Search branch..." autocomplete="off">

        <div id="branch_id_dropdown" class="dropdown">
            <?php foreach ($branches as $b): ?>
            <div class="branch_option" data-id="<?= htmlspecialchars($b['branch_id']) ?>">
                branch id: <?= htmlspecialchars($b['branch_id']) ?> |
                name: <?= htmlspecialchars($b['branch_name']) ?> |
                manager: <?= htmlspecialchars($b['manager_id'] ?? '') ?> |
                address: <?= htmlspecialchars($b['address']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>
    <label>Email: <input type="text" name="email"></label>
    <label>Phone: <input type="text" name="phone"></label>
    <label>Role: <select name="lore">
            <option value="">Select role</option>
            <option value="admin">admin</option>
            <option value="employee">employee</option>
        </select></label>

    <button type="submit" name="create_employee">Create employee</button>
</form>
<!-- (lore) -->
<!-- SEARCH FORM -->
<form method="get" class="simple-form">
    <h3>Search employees</h3>
    <input type="hidden" name="page" value="employees">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>Emp ID</th>
        <th>Passport ID</th>
        <th>Login ID</th>
        <th>Name</th>
        <th>Gender</th>
        <th>Branch</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Role</th>
        <th>Hire date</th>
        <th>Quit date</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
    <?php foreach ($rows as $e): ?>
    <tr>
        <td><?= htmlspecialchars($e['emp_id']) ?></td>
        <td><?= htmlspecialchars($e['passport_id']) ?></td>
        <td><?= htmlspecialchars($e['login_id'] ?? '') ?></td>
        <td><?= htmlspecialchars(($e['last_name'] ?? '') . ' ' . ($e['first_name'] ?? '')) ?></td>
        <td><?= htmlspecialchars($e['gender'] ?? '') ?></td>
        <td><?= htmlspecialchars($e['branch_id'] ?? '') ?></td>
        <td><?= htmlspecialchars($e['email'] ?? '') ?></td>
        <td><?= htmlspecialchars($e['phone'] ?? '') ?></td>
        <td><?= htmlspecialchars($e['lore'] ?? '') ?></td>
        <td><?= htmlspecialchars($e['hire_date'] ?? '') ?></td>
        <td><?= htmlspecialchars($e['quit_date'] ?? '') ?></td>

        <!-- EDIT on separate column -->
        <td>
            <a href="employees_edit.php?emp_id=<?= urlencode($e['emp_id']) ?>">Edit</a>
        </td>

        <!-- DELETE using delete_emp($passport_id, $login_id) on separate column -->
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="passport_id" value="<?= htmlspecialchars($e['passport_id']) ?>">
                <input type="hidden" name="login_id" value="<?= htmlspecialchars($e['login_id'] ?? '') ?>">
                <button type="submit" name="delete_employee"
                    onclick="return confirm('Delete this employee (passport + login)?');">
                    Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No employees found.</p>
<?php endif; ?>

<script>
function setupPreloadedDropdown(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.branch_option');

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
            input.value = op.dataset.id;
            dropdown.style.display = 'none';
        });
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

setupPreloadedDropdown('branch_id_input', 'branch_id_dropdown');
</script>