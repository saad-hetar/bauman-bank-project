<?php
/**
 * ÉTAPE 4 - Implémentation complète
 * 
 * Page d'historique des transactions
 * 
 * ✅ Implémenté:
 * - Vérification de la session client
 * - Affichage des transactions d'un compte sélectionné
 * - Utilisation de customer.class.php + history_transactions() méthode
 * - Filtrage par compte (account_id)
 * - Affichage: ID transaction, type, montant, date
 */

session_start();

// ÉTAPE 4: Vérification de la session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login_client.php');
    exit;
}

require_once('../database/db.php');
require_once('../classes/customer.class.php');

$user = new customer($_SESSION['user_id']);
$transactions = [];
$error = '';
$account_id = isset($_GET['account_id']) ? trim($_GET['account_id']) : null;

// ÉTAPE 4: Récupérer l'historique
if ($account_id) {
    try {
        // Définir l'account_id dans l'objet customer
        $user->get_account_id($account_id);
        
        // Récupérer l'historique
        $transactions = $user->history_transactions();
        
        // Vérifier si c'est un array (succès) ou un message d'erreur
        if (!is_array($transactions)) {
            $error = 'Impossible de charger l\'historique : ' . $transactions;
            $transactions = [];
        }
    } catch (Exception $e) {
        $error = 'Erreur : ' . $e->getMessage();
        $transactions = [];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historique des Transactions - Bauman Bank</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>🏦 Bauman Bank</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard_client.php" class="nav-link">Tableau de bord</a>
                <a href="transfer_client.php" class="nav-link">Virement</a>
                <a href="history_client.php" class="nav-link active">Historique</a>
                <a href="profile_client.php" class="nav-link">Profil</a>
                <a href="logout_client.php" class="nav-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Historique des Transactions</h1>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- TODO ÉTAPE 3: Formulaire de sélection de compte -->
        <div class="card">
            <h3>Sélectionner un compte</h3>
            <form method="GET" action="history_client.php" style="margin-bottom: 20px;">
                <div class="form-group">
                    <label for="account_id">ID du compte</label>
                    <input type="text" id="account_id" name="account_id" 
                           placeholder="Entrez l'ID de votre compte" 
                           value="<?php echo htmlspecialchars($account_id ?? ''); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Voir l'historique</button>
            </form>

            <!-- ÉTAPE 3: Tableau des transactions structuré -->
            <?php if ($account_id && count($transactions) > 0): ?>
                <div style="margin-bottom: 20px; padding: 15px; background: var(--bg-color); border-radius: 8px;">
                    <strong>Compte sélectionné :</strong> #<?php echo htmlspecialchars($account_id); ?>
                    <span style="float: right; color: var(--text-secondary);">
                        <?php echo count($transactions); ?> transaction(s) trouvée(s)
                    </span>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID Transaction</th>
                                <th>Type</th>
                                <th>Montant</th>
                                <th>Date & Heure</th>
                                <th>Détails</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_amount = 0;
                            foreach ($transactions as $transaction): 
                                $amount = floatval($transaction['amount'] ?? 0);
                                $total_amount += $amount;
                                $trans_type = $transaction['trans_type'] ?? 'N/A';
                                $is_credit = in_array(strtolower($trans_type), ['deposit', 'transfer']);
                            ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--primary-color);">
                                            #<?php echo htmlspecialchars($transaction['trans_id'] ?? 'N/A'); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $is_credit ? 'success' : 'warning'; ?>">
                                            <?php 
                                            $type_labels = [
                                                'deposit' => 'Dépôt',
                                                'withdraw' => 'Retrait',
                                                'transfer' => 'Virement',
                                                'pay' => 'Paiement'
                                            ];
                                            echo $type_labels[strtolower($trans_type)] ?? htmlspecialchars($trans_type);
                                            ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; <?php echo $is_credit ? 'color: var(--success-color);' : 'color: var(--danger-color);'; ?>">
                                        <?php echo ($is_credit ? '+' : '-'); ?>
                                        <?php echo number_format($amount, 2); ?> 
                                        <?php echo htmlspecialchars($transaction['currency'] ?? 'USD'); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $date = $transaction['trans_date'] ?? '';
                                        if ($date) {
                                            $date_obj = new DateTime($date);
                                            echo $date_obj->format('d/m/Y H:i');
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if (isset($transaction['receiver_bank'])): ?>
                                            <small style="color: var(--text-secondary);">
                                                Banque: <?php echo htmlspecialchars($transaction['receiver_bank']); ?>
                                            </small>
                                        <?php else: ?>
                                            <small style="color: var(--text-secondary);">-</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background: var(--bg-color); font-weight: 600;">
                                <td colspan="2"><strong>Total</strong></td>
                                <td style="color: var(--primary-color);">
                                    <?php echo number_format($total_amount, 2); ?> 
                                    <?php echo htmlspecialchars($transactions[0]['currency'] ?? 'USD'); ?>
                                </td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php elseif ($account_id): ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-secondary); margin-bottom: 10px;">
                        Aucune transaction trouvée pour le compte #<?php echo htmlspecialchars($account_id); ?>.
                    </p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Ce compte n'a pas encore effectué de transactions.
                    </p>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">
                        Veuillez sélectionner un compte pour voir l'historique.
                    </p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Entrez l'ID de votre compte dans le formulaire ci-dessus.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <div class="action-buttons" style="margin-top: 20px;">
            <a href="dashboard_client.php" class="btn btn-secondary">Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>

