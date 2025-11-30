<?php
/**
 * ÉTAPE 1 - Structure de base
 * 
 * Script de déconnexion client
 * 
 * À faire:
 * - Détruire la session
 * - Rediriger vers login_client.php
 */

session_start();
session_destroy();
header('Location: login_client.php');
exit;
?>

