<?php
session_start();

if (!isset($_SESSION['role'], $_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../../classes/customer.class.php';
$customer = new customer($_SESSION['user_id']);
$accounts = $customer->read_customer_accounts();

// Handle account selection POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['account_id'])) {
    $accountId = (int)$_POST['account_id'];

    // Validate ownership
    $valid = false;
    $currency = null;

    foreach ($accounts as $acc) {
        if ((int)$acc['account_id'] === $accountId) {
            $valid = true;
            $currency = $acc['currency']; // get currency from DB result
            break;
        }
    }


    if ($valid) {
        $_SESSION['account_id'] = $accountId;
        $_SESSION['currency']   = $currency;

        header('Location: index.php');
        exit;
    } else {
        $error = "Invalid account selected.";
    }

}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Select Account</title>
    <link rel="stylesheet" href="../css/style.css">
</head>

<body>

    <div class="card">
        <div class="card-header">
            <h3>Select Account</h3>
        </div>
        <div class="card-body">
            <p>Please select an account to work with:</p>

            <form method="POST">
                <div class="account-selector">
                    <?php if (is_array($accounts) && count($accounts) > 0): ?>
                    <div class="account-grid">
                        <?php foreach ($accounts as $account): ?>
                        <?php
                            $account_id = isset($account['account_id']) ? (int)$account['account_id'] : 0;
                            $currency   = isset($account['currency']) ? htmlspecialchars($account['currency']) : 'Unknown';
                            if ($account_id === 0) continue;
                            ?>
                        <button type="submit" name="account_id" value="<?= $account_id ?>" class="account-option">
                            <div class="account-content">
                                <div class="currency"><?= $currency ?></div>
                                <h5>Account #<?= $account_id ?></h5>
                                <p><?= $currency ?> Account</p>
                            </div>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <p>No accounts found.</p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>


    <style>
    .account-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .account-option {
        display: block;
        background: #f8f9fa;
        border: 2px solid #ccc;
        border-radius: 12px;
        padding: 30px;
        cursor: pointer;
        text-align: center;
        font-family: Arial, sans-serif;
    }

    .account-content .currency {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 10px;
    }

    .account-content h5 {
        font-size: 22px;
        margin: 5px 0;
    }

    .account-content p {
        font-size: 16px;
        color: #555;
    }
    </style>



</body>

</html>