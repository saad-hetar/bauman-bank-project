<?php
/**
 * ÉTAPE 1 - Structure de base
 * 
 * Page de profil client (optionnelle - pour plus tard)
 * 
 * À faire dans les prochaines étapes:
 * - Vérifier la session client
 * - Afficher les données personnelles du client
 * - Utiliser customer.trait.php pour les infos client
 * - Utiliser passport.trait.php pour les infos passeport
 * - Afficher: nom, prénom, email, téléphone, adresse
 * - Afficher: informations du passeport
 * - Optionnel: formulaire de modification
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
$customer_data = [];
$passport_data = [];
$error = '';

// Note: profile_client.php est optionnel - structure de base prête pour implémentation future
// Pour implémenter, utiliser:
// - customer.trait.php → read_customer() pour les infos client
// - passport.trait.php → read_passport() pour les infos passeport
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Bauman Bank</title>
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
                <a href="history_client.php" class="nav-link">Historique</a>
                <a href="profile_client.php" class="nav-link active">Profil</a>
                <a href="logout_client.php" class="nav-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Mon Profil</h1>
        </div>

        <!-- TODO ÉTAPE 3: Structurer l'affichage du profil -->
        <div class="card">
            <h3>📋 Informations Personnelles</h3>
            <p style="text-align: center; padding: 40px; color: var(--text-secondary);">
                Les informations du profil seront affichées ici.
            </p>
            <!-- 
            Structure à implémenter:
            - Nom, Prénom, Email, Téléphone, Adresse
            - Informations du passeport
            -->
        </div>

        <div class="action-buttons" style="margin-top: 20px;">
            <a href="dashboard_client.php" class="btn btn-secondary">Retour au tableau de bord</a>
        </div>
    </div>
</body>
</html>

