<?php
// public/admin/passports.php

// Start session BEFORE any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once dirname(__DIR__, 2) . '/classes/admin.class.php';

$admin_id = $_SESSION['user_id'];
$admin    = new admin($admin_id);

// Safe message
$message = isset($_GET['msg']) ? trim($_GET['msg']) : "";

// Search - Check both possible parameter names
$search = isset($_GET['search']) ? trim($_GET['search']) : (isset($_GET['q']) ? trim($_GET['q']) : "");

if ($search !== "") {
    $passports = $admin->search_passport($search);
} else {
    $passports = $admin->read_all_passport();
}

// Check if we're inside the template or standalone
// If 'page' parameter exists, we're in template
$in_template = isset($_GET['page']);
?>

<?php if (!$in_template): ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Bauman Bank – Passports</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <style>
    /* Additional styles specific to passports */
    .top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .top-bar form {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .top-bar form input {
        padding: 4px;
        width: 200px;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    th,
    td {
        border: 1px solid #ccc;
        padding: 4px 6px;
        text-align: left;
    }

    th {
        background: #e3f0ff;
    }

    .msg {
        margin: 10px 0;
        font-size: 14px;
        color: #0033cc;
        padding: 6px 8px;
        background: #eef6ff;
        border-left: 4px solid #1e70ff;
        display: inline-block;
    }
    </style>
</head>

<body>
    <header>
        <h1>Bauman Bank <small>- Admin panel</small></h1>
    </header>

    <div class="layout">
        <nav>
            <h3>Menu</h3>
            <a href="index.php?page=home">Home</a>
            <a href="index.php?page=customers">Customers</a>
            <a href="index.php?page=passports" class="active">Passports</a>
            <!-- Add other menu items as needed -->
            <a href="../auth/logout.php">Logout</a>
        </nav>

        <main>
            <?php endif; ?>

            <h2>All Passports</h2>

            <div class="top-bar">
                <?php if ($in_template): ?>
                <!-- When in template, submit to index.php with page parameter -->
                <form method="get" action="index.php">
                    <input type="hidden" name="page" value="passports">
                    <input type="text" name="search" placeholder="Search passports..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                </form>
                <?php else: ?>
                <!-- When standalone, submit to passports.php -->
                <form method="get" action="passports.php">
                    <input type="text" name="search" placeholder="Search passports..."
                        value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">Search</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- <?php if ($message): ?>
            <div class="msg"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?> -->

            <!-- <?php if ($search): ?>
            <p>Showing results for: "<strong><?= htmlspecialchars($search) ?></strong>"
                <?php if ($in_template): ?>
                <a href="index.php?page=passports">Clear search</a>
                <?php else: ?>
                <a href="passports.php">Clear search</a>
                <?php endif; ?>
            </p>
            <?php endif; ?> -->

            <table>
                <tr>
                    <th>ID</th>
                    <th>Owner</th>
                    <th>Last name</th>
                    <th>First name</th>
                    <th>Middle name</th>
                    <th>Number</th>
                    <th>Series</th>
                    <th>Nationality</th>
                    <th>Type</th>
                    <th>Birth date</th>
                    <th>Birth place</th>
                    <th>Gender</th>
                    <th>Issue date</th>
                    <th>Expire date</th>
                    <th>Issuing authority</th>
                    <th>Actions</th>
                </tr>

                <?php if (is_array($passports) && count($passports) > 0): ?>
                <?php foreach ($passports as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['passport_id']) ?></td>
                    <td><?= htmlspecialchars($p['owner']) ?></td>
                    <td><?= htmlspecialchars($p['last_name']) ?></td>
                    <td><?= htmlspecialchars($p['first_name']) ?></td>
                    <td><?= htmlspecialchars($p['middle_name']) ?></td>
                    <td><?= htmlspecialchars($p['passport_num']) ?></td>
                    <td><?= htmlspecialchars($p['passport_series']) ?></td>
                    <td><?= htmlspecialchars($p['nationality']) ?></td>
                    <td><?= htmlspecialchars($p['passport_type']) ?></td>
                    <td><?= htmlspecialchars($p['birth_date']) ?></td>
                    <td><?= htmlspecialchars($p['birth_place']) ?></td>
                    <td><?= htmlspecialchars($p['gender']) ?></td>
                    <td><?= htmlspecialchars($p['issue_date']) ?></td>
                    <td><?= htmlspecialchars($p['expire_date']) ?></td>
                    <td><?= htmlspecialchars($p['assuing_authority']) ?></td>
                    <td>
                        <?php if ($in_template): ?>
                        <a href="passport_edit.php?id=<?= (int)$p['passport_id'] ?>&page=passports">Edit</a>
                        <?php else: ?>
                        <a href="passport_edit.php?id=<?= (int)$p['passport_id'] ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr>
                    <td colspan="16">No passports found.</td>
                </tr>
                <?php endif; ?>
            </table>

            <?php if (!$in_template): ?>
        </main>
    </div>
</body>

</html>
<?php endif; ?>