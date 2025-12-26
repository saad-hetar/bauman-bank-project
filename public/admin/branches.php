<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$admin_id = $_SESSION['user_id'];
$admin = new admin($admin_id);

$message = $_GET['msg'] ?? '';

// ---------------------------------
// POST: create + delete
// ---------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['create_branch'])) {
        $name       = $_POST['branch_name'] ?? '';
        $manager_id = $_POST['manager_id'] ?? '';
        $address    = $_POST['address'] ?? '';

        $message = $admin->create_branch($name, $manager_id, $address);

        header('Location: index.php?page=branches&msg=' . urlencode($message));
    }

    if (isset($_POST['delete_branch'])) {
        $id      = $_POST['branch_id'] ?? '';
        $message = $admin->delete_branch($id);

        header('Location: index.php?page=branches&msg=' . urlencode($message));
    }
}

// ---------------------------------
// SEARCH / LIST
// ---------------------------------
$q = $_GET['q'] ?? '';
if ($q !== '') {
    $branches = $admin->search_branch($q);
} else {
    $branches = $admin->read_all_branch();
}

// ---------------------------------
// PRELOAD EMPLOYEES (for manager dropdown)
// ---------------------------------
$employees = $admin->read_all_emp();
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

.card_option,
.customer_option {
    padding: 5px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}
</style>

<h2>Branches</h2>

<?php if ($message): ?>
<div class="msg"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<!-- CREATE BRANCH FORM -->
<form method="post" class="simple-form">
    <h3>Create branch</h3>

    <label>Branch name:
        <input type="text" name="branch_name">
    </label>

    <label>Manager ID:
        <input type="text" name="manager_id" id="manager_id_input" placeholder="Search employee..." autocomplete="off">
        <div id="manager_id_dropdown" class="dropdown">
            <?php foreach ($employees as $e): ?>
            <div class="emp_option" data-id="<?= htmlspecialchars($e['emp_id']) ?>">
                employee id: <?= htmlspecialchars($e['emp_id']) ?> |
                name: <?= htmlspecialchars($e['first_name'] . ' ' . $e['last_name']) ?> |
                role: <?= htmlspecialchars($e['lore']) ?> |
                branch id: <?= htmlspecialchars($e['branch_id']) ?>
            </div>
            <?php endforeach; ?>
        </div>
    </label>

    <label>Address:
        <input type="text" name="address">
    </label>

    <button type="submit" name="create_branch">Create</button>
</form>

<!-- SEARCH FORM -->
<form method="get" class="simple-form">
    <h3>Search branches</h3>
    <input type="hidden" name="page" value="branches">
    <label>Search:
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>">
    </label>
    <button type="submit">Search</button>
</form>

<!-- BRANCH TABLE -->
<?php if (is_array($branches) && count($branches) > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Manager ID</th>
        <th>Address</th>
        <th>update</th>
        <th>delete</th>
    </tr>

    <?php foreach ($branches as $b): ?>
    <tr>
        <td><?= htmlspecialchars($b['branch_id']) ?></td>
        <td><?= htmlspecialchars($b['branch_name']) ?></td>
        <td><?= htmlspecialchars($b['manager_id'] ?? '') ?></td>
        <td><?= htmlspecialchars($b['address']) ?></td>

        <td>
            <a href="branches_edit.php?branch_id=<?= urlencode($b['branch_id']) ?>">Edit</a>
        </td>

        <td>
            <form method="post" style="display:inline;">
                <input type="hidden" name="branch_id" value="<?= htmlspecialchars($b['branch_id']) ?>">
                <button type="submit" name="delete_branch" onclick="return confirm('Delete this branch?');">
                    Delete
                </button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php else: ?>
<p>No branches found.</p>
<?php endif; ?>

<script>
// ---------- Option 2 Dropdown (Preloaded) ----------
function setupPreloadedDropdown(inputId, dropdownId) {
    const input = document.getElementById(inputId);
    const dropdown = document.getElementById(dropdownId);
    const options = dropdown.querySelectorAll('.emp_option');

    input.addEventListener('click', () => {
        dropdown.style.display = 'block';
        filterOptions();
    });

    input.addEventListener('input', filterOptions);

    function filterOptions() {
        const q = input.value.toLowerCase();
        options.forEach(op => {
            op.style.display = op.textContent.toLowerCase().includes(q) ? 'block' : 'none';
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

setupPreloadedDropdown('manager_id_input', 'manager_id_dropdown');
</script>