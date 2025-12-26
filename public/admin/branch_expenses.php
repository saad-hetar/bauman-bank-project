<?php
// public/admin/branch_expenses.php

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

// ---------- POST: create + delete ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CREATE
    if (isset($_POST['create_expense'])) {
        $branch_id     = $_POST['branch_id']     ?? '';
        $expenses_type = $_POST['expenses_type'] ?? '';
        $cost          = $_POST['cost']          ?? '';

        $message = $admin->create_expenses($branch_id, $expenses_type, $cost);

        // PRG redirect after create
        header('Location: index.php?page=branch_expenses&msg=' . urlencode($message));
        exit;
    }

    // DELETE
    if (isset($_POST['delete_expense'])) {
        $id = $_POST['expenses_id'] ?? '';

        $message = $admin->delete_expenses($id);

        // PRG redirect after delete
        header('Location: index.php?page=branch_expenses&msg=' . urlencode($message));
        exit;
    }
}

// ---------- SEARCH / LIST ----------
$q = $_GET['q'] ?? '';
if ($q !== '') {
    $rows = $admin->search_expenses($q);
} else {
    $rows = $admin->read_all_expenses();
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


<h2>Branch expenses</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="post" class="simple-form">
    <h3>Create expense</h3>
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

    <label>Type:
        <select name="expenses_type">
            <option value="">Select expenses type</option>
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
    <label>Cost:
        <input type="text" name="cost">
    </label>
    <button type="submit" name="create_expense">Create</button>
</form>

<form method="get" class="simple-form">
    <h3>Search expenses</h3>
    <input type="hidden" name="page" value="branch_expenses">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<?php if (is_array($rows) && count($rows) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Branch</th>
        <th>Type</th>
        <th>Cost</th>
        <th>Paid by</th>
        <th>Paid at</th>
        <th>Edit</th>
        <th>Delete</th>
    </tr>
    <?php foreach ($rows as $r): ?>
    <tr>
        <td><?= htmlspecialchars($r['expenses_id']) ?></td>
        <td><?= htmlspecialchars($r['branch_id']) ?></td>
        <td><?= htmlspecialchars($r['expenses_type']) ?></td>
        <td><?= htmlspecialchars($r['cost']) ?></td>
        <td><?= htmlspecialchars($r['paid_by']) ?></td>
        <td><?= htmlspecialchars($r['paid_at']) ?></td>
        <td>
            <a href="branch_expenses_edit.php?expenses_id=<?= urlencode($r['expenses_id']) ?>">
                Edit
            </a>
        </td>
        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="expenses_id" value="<?= htmlspecialchars($r['expenses_id']) ?>">
                <button type="submit" name="delete_expense" onclick="return confirm('Delete this expense?');">
                    Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
<p>No expenses found.</p>
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