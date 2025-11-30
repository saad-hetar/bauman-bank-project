<?php
/**
 * Script de configuration et vérification pour les tests
 * 
 * Utilisation: php test_setup.php
 */

echo "=== Configuration et Vérification du Projet Bauman Bank ===\n\n";

// 1. Vérifier PHP
echo "1. Vérification PHP...\n";
$php_version = phpversion();
echo "   Version PHP: $php_version\n";
if (version_compare($php_version, '7.4.0', '>=')) {
    echo "   ✅ Version PHP OK\n";
} else {
    echo "   ⚠️  Version PHP recommandée: 7.4+\n";
}

// 2. Vérifier les extensions
echo "\n2. Vérification des extensions...\n";
$extensions = ['pdo', 'pdo_mysql', 'session', 'mbstring'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ Extension $ext chargée\n";
    } else {
        echo "   ❌ Extension $ext manquante\n";
    }
}

// 3. Vérifier la base de données
echo "\n3. Vérification de la base de données...\n";
require_once('database/db.php');

try {
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $db = $stmt->fetch();
    echo "   ✅ Connexion à la base réussie\n";
    echo "   Base de données: " . $db['db'] . "\n";
    
    // Vérifier les tables
    $tables = ['passport', 'customer', 'account', 'card', 'login', 'transaction', 'transfer'];
    echo "\n   Tables présentes:\n";
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $result = $stmt->fetch();
            echo "   ✅ $table (" . $result['count'] . " enregistrements)\n";
        } catch (PDOException $e) {
            echo "   ❌ $table - Table manquante\n";
        }
    }
} catch (PDOException $e) {
    echo "   ❌ Erreur de connexion: " . $e->getMessage() . "\n";
    echo "   Vérifiez database/db.php\n";
}

// 4. Vérifier les fichiers
echo "\n4. Vérification des fichiers...\n";
$files = [
    'classes/admin.class.php',
    'classes/customer.class.php',
    'classes/employee.class.php',
    'public/login_client.php',
    'public/dashboard_client.php',
    'public/transfer_client.php',
    'public/history_client.php',
    'assets/css/style.css'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file\n";
    } else {
        echo "   ❌ $file manquant\n";
    }
}

// 5. Générer un hash de mot de passe
echo "\n5. Génération de hash de mot de passe...\n";
$test_password = 'password123';
$hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "   Mot de passe de test: $test_password\n";
echo "   Hash généré: $hash\n";
echo "   (Utilisez ce hash dans votre base de données)\n";

// 6. Instructions
echo "\n=== Instructions pour les tests ===\n";
echo "1. Importer les données de test:\n";
echo "   mysql -u root -p bauman_bank < database/test_data.sql\n\n";
echo "2. Démarrer le serveur:\n";
echo "   php -S localhost:8000\n\n";
echo "3. Accéder à l'application:\n";
echo "   http://localhost:8000/public/login_client.php\n\n";
echo "4. Identifiants de test:\n";
echo "   Login ID: CUST001\n";
echo "   Password: password\n\n";

echo "=== Vérification terminée ===\n";
?>

