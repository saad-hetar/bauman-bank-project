<?php
/**
 * ÉTAPE 4 - Implémentation complète
 * 
 * Page de connexion pour les clients
 * 
 * ✅ Implémenté:
 * - Formulaire de connexion (login_id + password)
 * - Validation des données
 * - Utilisation de login.trait.php pour vérifier les credentials
 * - Création de session client
 * - Redirection vers dashboard_client.php après connexion réussie
 */

session_start();

// Si déjà connecté, rediriger vers le dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    header('Location: dashboard_client.php');
    exit;
}

$error = '';

// ÉTAPE 4: Traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_id']) && isset($_POST['password'])) {
    require_once('../database/db.php');
    require_once('../traits/login.trait.php');
    
    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];
    
    if (empty($login_id) || empty($password)) {
        $error = 'Veuillez remplir tous les champs';
    } else {
        try {
            // Créer une classe temporaire pour utiliser le trait login
            class LoginHelper {
                use login;
            }
            $loginHelper = new LoginHelper();
            
            // Récupérer les données de connexion
            $login_data = $loginHelper->read_login($login_id);
            
            if ($login_data && count($login_data) > 0) {
                $login = $login_data[0];
                
                // Vérifier le mot de passe et le rôle
                if (password_verify($password, $login['password_hash']) && $login['role'] === 'customer') {
                    // Connexion réussie
                    $_SESSION['user_id'] = $login['user_id'];
                    $_SESSION['role'] = 'customer';
                    $_SESSION['login_id'] = $login_id;
                    
                    // Redirection vers le dashboard
                    header('Location: dashboard_client.php');
                    exit;
                } else {
                    $error = 'Identifiants invalides ou vous n\'êtes pas un client';
                }
            } else {
                $error = 'Identifiant de connexion introuvable';
            }
        } catch (Exception $e) {
            $error = 'Erreur de connexion : ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Client - Bauman Bank</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <div class="bank-logo">
                <h1>🏦 Bauman Bank</h1>
            </div>
            <h2>Connexion Client</h2>
            <p class="subtitle">Connectez-vous à votre espace client</p>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- ÉTAPE 3: Formulaire HTML structuré -->
            <form method="POST" action="login_client.php" class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="login_id">Identifiant de connexion</label>
                    <input type="text" 
                           id="login_id" 
                           name="login_id" 
                           placeholder="Entrez votre identifiant"
                           required 
                           autofocus
                           autocomplete="username">
                    <small class="form-help">Votre identifiant unique fourni par la banque</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Entrez votre mot de passe"
                           required
                           autocomplete="current-password">
                    <small class="form-help">Votre mot de passe confidentiel</small>
                </div>
                
                <div class="form-group" style="margin-top: 10px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="remember" style="width: auto; margin-right: 8px;">
                        <span style="font-size: 0.9rem;">Se souvenir de moi</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                    <span id="submitText">Se connecter</span>
                    <span id="submitLoading" style="display: none;">Connexion en cours...</span>
                </button>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="#" style="color: var(--text-secondary); font-size: 0.9rem; text-decoration: none;">
                        Mot de passe oublié ?
                    </a>
                </div>
            </form>
            
            <script>
                // Validation côté client
                document.getElementById('loginForm').addEventListener('submit', function(e) {
                    const loginId = document.getElementById('login_id').value.trim();
                    const password = document.getElementById('password').value;
                    
                    if (!loginId || !password) {
                        e.preventDefault();
                        alert('Veuillez remplir tous les champs');
                        return false;
                    }
                    
                    // Afficher le loading
                    document.getElementById('submitText').style.display = 'none';
                    document.getElementById('submitLoading').style.display = 'inline';
                    document.getElementById('submitBtn').disabled = true;
                });
            </script>
        </div>
    </div>
</body>
</html>

