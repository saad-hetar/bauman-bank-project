<?php
/**
 * ÉTAPE 4 - Implémentation complète
 * 
 * Tableau de bord client (accessible uniquement après login)
 * 
 * ✅ Implémenté:
 * - Vérification de la session client
 * - Affichage des comptes du client (numéro, type, solde)
 * - Requête SQL directe pour obtenir toutes les infos des comptes
 * - Boutons vers: "Effectuer un virement", "Voir l'historique"
 * - Résumé financier avec statistiques
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
$accounts = [];
$error = '';

// ÉTAPE 4: Récupérer les comptes avec toutes les informations
// Note: read_customer_accounts() ne retourne que currency, donc on utilise une requête SQL directe
try {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            account.account_id, 
            account.account_type, 
            account.currency, 
            account.account_status,
            COALESCE(SUM(card.balance), 0) as balance
        FROM account 
        LEFT JOIN card ON card.account_id = account.account_id
        WHERE account.customer_id = :customer_id
        GROUP BY account.account_id, account.account_type, account.currency, account.account_status, account.created_at
        ORDER BY account.created_at ASC
    ");
    
    $stmt->execute([':customer_id' => $_SESSION['user_id']]);
    $accounts = $stmt->fetchAll();
    
    if (!is_array($accounts)) {
        $accounts = [];
    }
} catch (PDOException $e) {
    $error = 'Impossible de charger les comptes : ' . $e->getMessage();
    $accounts = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Client - Bauman Bank</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h2>🏦 Bauman Bank</h2>
            </div>
            <div class="nav-menu">
                <a href="dashboard_client.php" class="nav-link active">Tableau de bord</a>
                <a href="transfer_client.php" class="nav-link">Virement</a>
                <a href="history_client.php" class="nav-link">Historique</a>
                <a href="profile_client.php" class="nav-link">Profil</a>
                <a href="logout_client.php" class="nav-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Tableau de Bord Client</h1>
            <p class="user-info">Bienvenue, Client #<?php echo isset($_SESSION['user_id']) ? htmlspecialchars($_SESSION['user_id']) : 'N/A'; ?></p>
        </div>

        <!-- ÉTAPE 3: Structure HTML complète pour l'affichage des comptes -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>💰 Mes Comptes</h3>
                <span class="stat-value" style="font-size: 1rem; color: var(--text-secondary);">
                    <?php echo isset($accounts) && is_array($accounts) ? count($accounts) : 0; ?> compte(s)
                </span>
            </div>
            
            <?php if (isset($accounts) && is_array($accounts) && count($accounts) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Numéro de compte</th>
                                <th>Type</th>
                                <th>Statut</th>
                                <th>Solde</th>
                                <th>Devise</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($accounts as $account): ?>
                                <tr>
                                    <td>
                                        <strong style="color: var(--primary-color);">
                                            #<?php echo htmlspecialchars($account['account_id'] ?? 'N/A'); ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo ($account['account_type'] ?? '') === 'business' ? 'warning' : 'primary'; ?>">
                                            <?php 
                                            $type = $account['account_type'] ?? 'N/A';
                                            echo $type === 'business' ? 'Entreprise' : ($type === 'personal' ? 'Personnel' : $type);
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo ($account['account_status'] ?? '') === 'active' ? 'success' : 'secondary'; ?>">
                                            <?php 
                                            $status = $account['account_status'] ?? 'N/A';
                                            echo $status === 'active' ? 'Actif' : ($status === 'closed' ? 'Fermé' : ($status === 'frozen' ? 'Gelé' : $status));
                                            ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 600; color: var(--success-color);">
                                        <?php echo number_format($account['balance'] ?? 0, 2); ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($account['currency'] ?? 'N/A'); ?></strong>
                                    </td>
                                    <td>
                                        <a href="history_client.php?account_id=<?php echo htmlspecialchars($account['account_id'] ?? ''); ?>" 
                                           class="btn btn-primary" 
                                           style="padding: 6px 12px; font-size: 14px;">
                                            Voir historique
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif (isset($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <p style="color: var(--text-secondary); margin-bottom: 20px;">
                        Aucun compte trouvé.
                    </p>
                    <p style="color: var(--text-secondary); font-size: 0.9rem;">
                        Contactez votre agence pour ouvrir un compte.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- ÉTAPE 3: Résumé financier -->
        <?php if (isset($accounts) && is_array($accounts) && count($accounts) > 0): ?>
            <div class="card">
                <h3>📊 Résumé Financier</h3>
                <div class="stat-grid">
                    <?php 
                    $total_balance = 0;
                    $active_accounts = 0;
                    foreach ($accounts as $account) {
                        $total_balance += floatval($account['balance'] ?? 0);
                        if (($account['account_status'] ?? '') === 'active') {
                            $active_accounts++;
                        }
                    }
                    ?>
                    <div class="stat-item">
                        <span class="stat-label">Solde Total</span>
                        <span class="stat-value" style="color: var(--success-color);">
                            <?php echo number_format($total_balance, 2); ?> 
                            <?php echo htmlspecialchars($accounts[0]['currency'] ?? 'USD'); ?>
                        </span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Comptes Actifs</span>
                        <span class="stat-value"><?php echo $active_accounts; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Comptes</span>
                        <span class="stat-value"><?php echo count($accounts); ?></span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ÉTAPE 3: Actions rapides avec icônes -->
        <div class="card">
            <h3>⚡ Actions rapides</h3>
            <div class="action-buttons">
                <a href="transfer_client.php" class="btn btn-primary">
                    💸 Effectuer un virement
                </a>
                <a href="history_client.php" class="btn btn-secondary">
                    📜 Voir l'historique
                </a>
                <a href="profile_client.php" class="btn btn-secondary">
                    👤 Mon profil
                </a>
            </div>
        </div>
    </div>
</body>
</html>

