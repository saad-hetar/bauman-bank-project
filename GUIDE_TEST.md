# 🧪 Guide de Test - Projet Bauman Bank

## 📋 Prérequis

### 1. Environnement requis
- ✅ PHP 7.4 ou supérieur
- ✅ MySQL/MariaDB
- ✅ Serveur web (Apache/Nginx) ou PHP built-in server
- ✅ Extension PDO MySQL activée

### 2. Configuration de la base de données

#### Étape 1 : Créer la base de données
```sql
CREATE DATABASE bauman_bank CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Étape 2 : Importer les tables
```bash
# Depuis le terminal, dans le dossier database/
mysql -u root -p bauman_bank < tables.sql
mysql -u root -p bauman_bank < foreign_keys.sql
mysql -u root -p bauman_bank < triggers.sql
```

#### Étape 3 : Vérifier la configuration
Ouvrir `database/db.php` et vérifier :
```php
$host = 'localhost';      // Votre hôte MySQL
$db   = 'bauman_bank';    // Nom de la base
$user = 'root';           // Votre utilisateur MySQL
$pass = '';               // Votre mot de passe MySQL
```

---

## 🚀 Démarrage du Serveur

### Option 1 : PHP Built-in Server (Recommandé pour tests)
```bash
# Depuis la racine du projet
php -S localhost:8000
```

Puis accéder à :
- **Page client :** http://localhost:8000/public/login_client.php
- **Page générale :** http://localhost:8000/index.php

### Option 2 : Serveur Web (Apache/Nginx)
- Configurer le serveur pour pointer vers la racine du projet
- Accéder via votre domaine ou localhost

---

## 👤 Création de Données de Test

### 1. Créer un client de test

#### Via SQL direct :
```sql
-- 1. Créer un passeport
INSERT INTO passport (last_name, first_name, middle_name, passport_num, 
                     passport_series, nationality, passport_type, birth_date, 
                     birth_place, gender, issue_date, expire_date, 
                     assuing_authority, owner)
VALUES ('Doe', 'John', 'Michael', '123456789', 'AB', 'US', 'ordinary', 
        '1990-01-15', 'New York', 'male', '2020-01-01', '2030-01-01', 
        'US Embassy', 'customer');

-- Récupérer le passport_id généré (ex: 1)

-- 2. Créer un client
INSERT INTO customer (passport_id, address, phone, email, created_by)
VALUES (1, '123 Main St', '+1234567890', 'john.doe@email.com', 1);

-- Récupérer le customer_id généré (ex: 1)

-- 3. Créer un compte
INSERT INTO account (customer_id, account_type, currency, created_by)
VALUES (1, 'personal', 'USD', 1);

-- Récupérer l'account_id généré (ex: 1)

-- 4. Créer une carte (sera créée automatiquement par trigger, mais on peut en créer une manuellement)
INSERT INTO card (account_id, card_num, cvv, expire_date, balance)
VALUES (1, '4123456789012345', '123', '2029-12-31', 1000.00);

-- 5. Créer un login pour le client
-- Le mot de passe sera hashé avec password_hash()
INSERT INTO login (login_id, user_id, role, password_hash)
VALUES ('CUST001', 1, 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Mot de passe par défaut: "password" (à changer en production)
```

### 2. Script SQL de test complet

Créer un fichier `database/test_data.sql` :

```sql
-- Nettoyer les données existantes (ATTENTION : Supprime tout !)
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE transaction;
TRUNCATE TABLE transfer;
TRUNCATE TABLE card;
TRUNCATE TABLE account;
TRUNCATE TABLE customer;
TRUNCATE TABLE passport;
TRUNCATE TABLE login;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Passeport
INSERT INTO passport (last_name, first_name, middle_name, passport_num, 
                     passport_series, nationality, passport_type, birth_date, 
                     birth_place, gender, issue_date, expire_date, 
                     assuing_authority, owner)
VALUES 
('Doe', 'John', 'Michael', '123456789', 'AB', 'US', 'ordinary', 
 '1990-01-15', 'New York', 'male', '2020-01-01', '2030-01-01', 
 'US Embassy', 'customer'),
('Smith', 'Jane', 'Elizabeth', '987654321', 'CD', 'US', 'ordinary', 
 '1992-05-20', 'Los Angeles', 'female', '2021-01-01', '2031-01-01', 
 'US Embassy', 'customer');

-- 2. Clients
INSERT INTO customer (passport_id, address, phone, email, created_by)
VALUES 
(1, '123 Main St, New York', '+1234567890', 'john.doe@email.com', 1),
(2, '456 Oak Ave, Los Angeles', '+1987654321', 'jane.smith@email.com', 1);

-- 3. Comptes
INSERT INTO account (customer_id, account_type, currency, created_by)
VALUES 
(1, 'personal', 'USD', 1),
(1, 'business', 'EUR', 1),
(2, 'personal', 'USD', 1);

-- 4. Cartes
INSERT INTO card (account_id, card_num, cvv, expire_date, balance)
VALUES 
(1, '4123456789012345', '123', '2029-12-31', 5000.00),
(2, '4123456789012346', '456', '2029-12-31', 3000.00),
(3, '4123456789012347', '789', '2029-12-31', 2000.00);

-- 5. Logins (mot de passe: "password123" pour tous)
INSERT INTO login (login_id, user_id, role, password_hash)
VALUES 
('CUST001', 1, 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'),
('CUST002', 2, 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');
-- Note: Le hash ci-dessus correspond au mot de passe "password"
-- Pour un vrai mot de passe, utiliser: password_hash('votre_mot_de_passe', PASSWORD_DEFAULT)
```

---

## 🧪 Tests à Effectuer

### Test 1 : Connexion Client ✅

**URL :** http://localhost:8000/public/login_client.php

**Données de test :**
- Login ID : `CUST001`
- Password : `password` (ou le mot de passe que vous avez défini)

**Résultat attendu :**
- ✅ Redirection vers `dashboard_client.php`
- ✅ Session créée avec `user_id` et `role = 'customer'`

**Tests à faire :**
- [ ] Connexion avec identifiants valides
- [ ] Connexion avec identifiants invalides (doit afficher erreur)
- [ ] Connexion avec mauvais rôle (doit refuser)
- [ ] Vérifier que la session est créée

---

### Test 2 : Dashboard Client ✅

**URL :** http://localhost:8000/public/dashboard_client.php

**Résultat attendu :**
- ✅ Affichage des comptes du client connecté
- ✅ Tableau avec : Numéro, Type, Statut, Solde, Devise
- ✅ Résumé financier (solde total, comptes actifs)
- ✅ Boutons d'action fonctionnels

**Tests à faire :**
- [ ] Vérifier l'affichage des comptes
- [ ] Vérifier les statistiques (solde total)
- [ ] Cliquer sur "Voir historique" d'un compte
- [ ] Cliquer sur "Effectuer un virement"
- [ ] Tester l'accès sans session (doit rediriger vers login)

---

### Test 3 : Virement ✅

**URL :** http://localhost:8000/public/transfer_client.php

**Scénarios de test :**

#### Test 3.1 : Virement interne par carte
- Type : `internal_card`
- Expéditeur : `4123456789012345` (carte du client 1)
- Destinataire : `4123456789012347` (carte du client 2)
- Montant : `100.00`
- Devise : `USD`
- Description : `Test virement interne`

**Résultat attendu :**
- ✅ Message de succès
- ✅ Solde de la carte expéditeur diminué (montant + commission 3%)
- ✅ Solde de la carte destinataire augmenté (montant)
- ✅ Transaction enregistrée dans la base

#### Test 3.2 : Virement externe par carte
- Type : `external_card`
- Expéditeur : `4123456789012345`
- Destinataire : `4123456789012347`
- Montant : `50.00`
- Devise : `USD`
- Banque destinataire : `Sberbank`
- Description : `Test virement externe`

**Résultat attendu :**
- ✅ Message de succès
- ✅ Commission de 8% appliquée
- ✅ Transaction enregistrée

#### Test 3.3 : Virement interne par téléphone
- Type : `internal_phone`
- Expéditeur : `+1234567890` (téléphone du client 1)
- Destinataire : `+1987654321` (téléphone du client 2)
- Montant : `75.00`
- Devise : `USD`

**Résultat attendu :**
- ✅ Message de succès
- ✅ Virement effectué via numéro de téléphone

#### Test 3.4 : Tests de validation
- [ ] Montant négatif (doit refuser)
- [ ] Montant = 0 (doit refuser)
- [ ] Champs vides (doit refuser)
- [ ] Virement externe sans banque (doit refuser)
- [ ] Solde insuffisant (doit afficher erreur)

---

### Test 4 : Historique des Transactions ✅

**URL :** http://localhost:8000/public/history_client.php

**Tests à faire :**
- [ ] Sélectionner un account_id valide
- [ ] Vérifier l'affichage des transactions
- [ ] Vérifier le formatage des dates
- [ ] Vérifier le formatage des montants
- [ ] Tester avec un account_id invalide
- [ ] Tester avec un compte sans transactions

**Résultat attendu :**
- ✅ Liste des transactions du compte
- ✅ Tableau avec : ID, Type, Montant, Date
- ✅ Total des transactions affiché

---

### Test 5 : Déconnexion ✅

**URL :** http://localhost:8000/public/logout_client.php

**Tests à faire :**
- [ ] Cliquer sur "Déconnexion"
- [ ] Vérifier que la session est détruite
- [ ] Vérifier la redirection vers login_client.php
- [ ] Essayer d'accéder à dashboard_client.php après déconnexion (doit rediriger)

---

## 🔍 Vérifications dans la Base de Données

### Après un virement, vérifier :

```sql
-- Vérifier les soldes des cartes
SELECT card_num, balance FROM card ORDER BY card_num;

-- Vérifier les transactions
SELECT * FROM transaction ORDER BY trans_date DESC LIMIT 10;

-- Vérifier les transfers
SELECT * FROM transfer ORDER BY trans_date DESC LIMIT 10;
```

---

## 📝 Checklist de Test Complète

### Fonctionnalités Client
- [ ] Connexion avec identifiants valides
- [ ] Connexion avec identifiants invalides
- [ ] Affichage du dashboard
- [ ] Affichage des comptes
- [ ] Virement interne par carte
- [ ] Virement externe par carte
- [ ] Virement interne par téléphone
- [ ] Virement externe par téléphone
- [ ] Validation des formulaires
- [ ] Affichage de l'historique
- [ ] Déconnexion

### Sécurité
- [ ] Accès sans session (redirection)
- [ ] Accès avec mauvais rôle (refus)
- [ ] Protection XSS (tester avec <script>)
- [ ] Validation des montants
- [ ] Vérification des soldes avant virement

### Interface
- [ ] Responsive design (mobile)
- [ ] Navigation entre pages
- [ ] Messages d'erreur clairs
- [ ] Messages de succès
- [ ] Formatage des données

---

## 🐛 Dépannage

### Problème : Erreur de connexion à la base de données
**Solution :**
- Vérifier les paramètres dans `database/db.php`
- Vérifier que MySQL est démarré
- Vérifier que la base `bauman_bank` existe

### Problème : Page blanche
**Solution :**
- Activer l'affichage des erreurs PHP :
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
- Vérifier les logs d'erreur PHP

### Problème : Erreur "Class not found"
**Solution :**
- Vérifier les chemins dans les require
- Vérifier que les fichiers existent
- Vérifier les noms de classes (case-sensitive)

### Problème : Session non persistante
**Solution :**
- Vérifier que `session_start()` est appelé
- Vérifier les permissions d'écriture pour les sessions
- Vérifier la configuration PHP pour les sessions

---

## 📊 Tests de Performance (Optionnel)

### Test de charge
- Tester avec plusieurs utilisateurs simultanés
- Vérifier les temps de réponse
- Vérifier l'utilisation de la mémoire

### Test de sécurité avancé
- Tests d'injection SQL (déjà protégé avec PDO)
- Tests CSRF (à ajouter si nécessaire)
- Tests de session hijacking

---

## ✅ Résultat Attendu

Après tous les tests, vous devriez avoir :
- ✅ Système de connexion fonctionnel
- ✅ Dashboard affichant les comptes
- ✅ Virements fonctionnels (4 types)
- ✅ Historique des transactions
- ✅ Sécurité en place
- ✅ Interface utilisateur responsive

---

## 📝 Notes Importantes

1. **Mots de passe de test :** Utilisez des mots de passe forts en production
2. **Données de test :** Ne pas utiliser en production
3. **Sécurité :** Ajouter `.htaccess` et `.gitignore` avant production
4. **Backup :** Faire un backup de la base avant les tests

---

## 🎯 Prochaines Étapes Après Tests

1. Corriger les bugs trouvés
2. Améliorer les messages d'erreur si nécessaire
3. Ajouter des fonctionnalités manquantes
4. Optimiser les performances
5. Préparer pour la production

---

**Bon test ! 🚀**

