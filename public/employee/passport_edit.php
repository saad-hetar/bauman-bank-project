<?php
// public/employee/passport_edit.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/employee.class.php';

$employee_id = $_SESSION['user_id'];
$employee    = new employee($employee_id);

$message     = "";
$passport    = null;
$passport_id = null;

// ----- when form is submitted -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['passport_id'])) {

    $passport_id       = (int)$_POST['passport_id'];
    $last_name         = $_POST['last_name'] ?? '';
    $first_name        = $_POST['first_name'] ?? '';
    $middle_name       = $_POST['middle_name'] ?? '';
    $passport_num      = $_POST['passport_num'] ?? '';
    $passport_series   = $_POST['passport_series'] ?? '';
    $nationality       = $_POST['nationality'] ?? '';
    $passport_type     = $_POST['passport_type'] ?? '';
    $birth_date        = $_POST['birth_date'] ?? '';
    $birth_place       = $_POST['birth_place'] ?? '';
    $gender            = $_POST['gender'] ?? '';
    $issue_date        = $_POST['issue_date'] ?? '';
    $expire_date       = $_POST['expire_date'] ?? '';
    $assuing_authority = $_POST['assuing_authority'] ?? '';
    $owner             = $_POST['owner'] ?? '';

    $message = $employee->update_passport(
        $passport_id,
        $last_name,
        $first_name,
        $middle_name,
        $passport_num,
        $passport_series,
        $nationality,
        $passport_type,
        $birth_date,
        $birth_place,
        $gender,
        $issue_date,
        $expire_date,
        $assuing_authority,
        $owner
    );

    if (strpos($message, 'failed') === false) {
    // Check if coming from template or direct
    $from_template = isset($_GET['from_template']) || isset($_POST['from_template']);
    
    if ($from_template) {
        header('Location: index.php?page=passports&msg=' . urlencode($message));
    } else {
        header('Location: passports.php?msg=' . urlencode($message));
    }
    exit;
    }
}

// ----- first time open (GET) -----
if ($passport_id === null) {
    if (!isset($_GET['id'])) {
        die('No passport id given.');
    }
    $passport_id = (int)$_GET['id'];
}

// we try to load that passport
// if read_passport() is already fixed, you can use it instead
$all = $employee->read_all_passport();
if (is_array($all)) {
    foreach ($all as $row) {
        if ((int)$row['passport_id'] === $passport_id) {
            $passport = $row;
            break;
        }
    }
}

if ($passport === null) {
    die('Passport not found.');
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Edit Passport – Bauman Bank</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 13px;
    }

    .container {
        max-width: 700px;
        margin: 20px auto;
    }

    label {
        display: block;
        margin-top: 8px;
    }

    /* MATCH customers_edit.php INPUT STYLES */
    input[type="text"],
    input[type="date"] {
        width: 250px;
        /* Changed from 100% to match customers_edit.php */
        box-sizing: border-box;
        padding: 4px;
        /* Removed: font-size: 16px; */
    }

    .msg {
        margin: 10px 0;
        color: blue;
    }

    .buttons {
        margin-top: 12px;
    }
    </style>
</head>

<body>
    <div class="container">
        <h2>Edit Passport #<?= htmlspecialchars($passport['passport_id']) ?></h2>

        <?php if ($message): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="passport_id" value="<?= (int)$passport['passport_id'] ?>">

            <label>Owner:
                <input type="text" name="owner" value="<?= htmlspecialchars($passport['owner']) ?>">
            </label>

            <label>Last name:
                <input type="text" name="last_name" value="<?= htmlspecialchars($passport['last_name']) ?>">
            </label>

            <label>First name:
                <input type="text" name="first_name" value="<?= htmlspecialchars($passport['first_name']) ?>">
            </label>

            <label>Middle name:
                <input type="text" name="middle_name" value="<?= htmlspecialchars($passport['middle_name']) ?>">
            </label>

            <label>Passport number:
                <input type="text" name="passport_num" value="<?= htmlspecialchars($passport['passport_num']) ?>">
            </label>

            <label>Passport series:
                <input type="text" name="passport_series" value="<?= htmlspecialchars($passport['passport_series']) ?>">
            </label>

            <label>Nationality:
                <input type="text" name="nationality" value="<?= htmlspecialchars($passport['nationality']) ?>">
            </label>

            <label>Passport type:
                <input type="text" name="passport_type" value="<?= htmlspecialchars($passport['passport_type']) ?>">
            </label>

            <label>Birth date:
                <input type="date" name="birth_date" value="<?= htmlspecialchars($passport['birth_date']) ?>">
            </label>

            <label>Birth place:
                <input type="text" name="birth_place" value="<?= htmlspecialchars($passport['birth_place']) ?>">
            </label>

            <label>Gender:
                <input type="text" name="gender" value="<?= htmlspecialchars($passport['gender']) ?>">
            </label>

            <label>Issue date:
                <input type="date" name="issue_date" value="<?= htmlspecialchars($passport['issue_date']) ?>">
            </label>

            <label>Expire date:
                <input type="date" name="expire_date" value="<?= htmlspecialchars($passport['expire_date']) ?>">
            </label>

            <label>Assuing authority:
                <input type="text" name="assuing_authority"
                    value="<?= htmlspecialchars($passport['assuing_authority']) ?>">
            </label>

            <div class="buttons">
                <button type="submit">Save changes</button>
                <a href="passports.php">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>