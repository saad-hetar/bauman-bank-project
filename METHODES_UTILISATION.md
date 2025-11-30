# Guide d'utilisation des méthodes PHP - Pages Client

## 📋 Table des matières

1. [login_client.php](#login_clientphp)
2. [dashboard_client.php](#dashboard_clientphp)
3. [transfer_client.php](#transfer_clientphp)
4. [history_client.php](#history_clientphp)

---

## a) login_client.php

### Méthode à utiliser : `read_login()`

**Fichier :** `traits/login.trait.php`

**Signature :**
```php
public function read_login($login_id)
```

**Paramètres :**
- `$login_id` (string) - L'identifiant de connexion

**Retourne :**
- Array avec les données du login : `user_id`, `role`, `password_hash`, `login_id`
- Ou message d'erreur en cas d'échec

**Code d'implémentation :**

```php
<?php
session_start();
require_once('../database/db.php');

// Si déjà connecté, rediriger
if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'customer') {
    header('Location: dashboard_client.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_id']) && isset($_POST['password'])) {
    $login_id = $_POST['login_id'];
    $password = $_POST['password'];
    
    try {
        // Créer une classe temporaire pour utiliser le trait
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
?>
```

---

## b) dashboard_client.php

### Méthode à utiliser : `read_customer_accounts()`

**Fichier :** `classes/customer.class.php` (via `account.trait.php`)

**Signature :**
```php
public function read_customer_accounts()
```

**Paramètres :** Aucun (utilise `$this->account_user_id` défini dans le constructeur)

**⚠️ PROBLÈME IDENTIFIÉ :** Cette méthode ne retourne que `currency`, pas `account_id`, `account_type`, `balance`.

**Solution recommandée : Requête SQL directe**

```php
<?php
session_start();
require_once('../database/db.php');

// Vérifier la session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login_client.php');
    exit;
}

require_once('../classes/customer.class.php');
$user = new customer($_SESSION['user_id']);

// Requête SQL directe pour obtenir toutes les infos des comptes
global $pdo;
try {
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
        GROUP BY account.account_id, account.account_type, account.currency, account.account_status
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
```

**Alternative : Utiliser la méthode existante + requête complémentaire**

```php
// Option 1 : Utiliser read_customer_accounts() (retourne seulement currency)
$accounts_currency = $user->read_customer_accounts();

// Option 2 : Requête SQL complète (recommandée)
// Voir code ci-dessus
```

---

## c) transfer_client.php

### Méthodes à utiliser : Méthodes de transfer

**Fichier :** `classes/customer.class.php` (via `transfer.trait.php`)

**Méthodes disponibles :**

1. **`transfer_internal_card()`** - Virement interne par carte
2. **`transfer_external_card()`** - Virement externe par carte
3. **`transfer_internal_phone()`** - Virement interne par téléphone
4. **`transfer_external_phone()`** - Virement externe par téléphone

**Signatures :**

```php
// Virement interne par carte
public function transfer_internal_card(
    $sender_card_num,      // string - Numéro de carte expéditeur
    $trans_type,           // string - "internal"
    $currency,             // string - Devise (USD, EUR, RUB)
    $description,          // string - Description
    $amount,               // float - Montant
    $receiver_card_num     // string - Numéro de carte destinataire
)

// Virement externe par carte
public function transfer_external_card(
    $sender_card_num,      // string
    $trans_type,           // string - "external"
    $currency,             // string
    $description,          // string
    $amount,               // float
    $receiver_card_num,    // string
    $receiver_bank         // string - Nom de la banque destinataire
)

// Virement interne par téléphone
public function transfer_internal_phone(
    $sender_phone,         // string - Téléphone expéditeur
    $trans_type,           // string - "internal"
    $currency,             // string
    $description,          // string
    $amount,               // float
    $receiver_phone        // string - Téléphone destinataire
)

// Virement externe par téléphone
public function transfer_external_phone(
    $sender_phone,         // string
    $trans_type,           // string - "external"
    $currency,             // string
    $description,          // string
    $amount,               // float
    $receiver_phone,       // string
    $receiver_bank         // string
)
```

**Retourne :**
- `"transfered successfully"` en cas de succès
- Message d'erreur en cas d'échec

**Code d'implémentation :**

```php
<?php
session_start();
require_once('../database/db.php');

// Vérifier la session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login_client.php');
    exit;
}

require_once('../classes/customer.class.php');
$user = new customer($_SESSION['user_id']);

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    $transfer_type = $_POST['transfer_type'];
    $sender = $_POST['sender_card_num']; // Carte ou téléphone selon le type
    $receiver = $_POST['receiver'];
    $amount = floatval($_POST['amount']);
    $currency = $_POST['currency'];
    $description = $_POST['description'] ?? '';
    $receiver_bank = $_POST['receiver_bank'] ?? '';
    
    try {
        switch($transfer_type) {
            case 'internal_card':
                $result = $user->transfer_internal_card(
                    $sender,
                    'internal',
                    $currency,
                    $description,
                    $amount,
                    $receiver
                );
                break;
                
            case 'external_card':
                if (empty($receiver_bank)) {
                    throw new Exception('Le nom de la banque destinataire est requis pour un virement externe');
                }
                $result = $user->transfer_external_card(
                    $sender,
                    'external',
                    $currency,
                    $description,
                    $amount,
                    $receiver,
                    $receiver_bank
                );
                break;
                
            case 'internal_phone':
                $result = $user->transfer_internal_phone(
                    $sender, // Téléphone expéditeur
                    'internal',
                    $currency,
                    $description,
                    $amount,
                    $receiver // Téléphone destinataire
                );
                break;
                
            case 'external_phone':
                if (empty($receiver_bank)) {
                    throw new Exception('Le nom de la banque destinataire est requis pour un virement externe');
                }
                $result = $user->transfer_external_phone(
                    $sender, // Téléphone expéditeur
                    'external',
                    $currency,
                    $description,
                    $amount,
                    $receiver, // Téléphone destinataire
                    $receiver_bank
                );
                break;
                
            default:
                throw new Exception('Type de virement invalide');
        }
        
        // Vérifier le résultat
        if (strpos($result, 'successfully') !== false) {
            $message = 'Virement effectué avec succès !';
            $message_type = 'alert-success';
        } else {
            $message = $result;
            $message_type = 'alert-error';
        }
    } catch (Exception $e) {
        $message = 'Erreur : ' . $e->getMessage();
        $message_type = 'alert-error';
    }
}
?>
```

**Notes importantes :**
- Les méthodes utilisent automatiquement les transactions PDO (`beginTransaction()`, `commit()`, `rollBack()`)
- Les triggers SQL mettent à jour les soldes automatiquement
- Les commissions sont calculées automatiquement (3% interne, 8% externe)

---

## d) history_client.php

### Méthodes à utiliser : `get_account_id()` et `history_transactions()`

**Fichier :** `classes/customer.class.php`

**Signatures :**

```php
// Définir l'account_id
public function get_account_id($account_id)

// Récupérer l'historique
public function history_transactions()
```

**Paramètres :**
- `get_account_id($account_id)` : `$account_id` (int/string) - ID du compte
- `history_transactions()` : Aucun (utilise `$this->account_id`)

**Retourne :**
- `history_transactions()` retourne un array avec :
  - `trans_id` - ID de la transaction
  - `trans_type` - Type de transaction
  - `amount` - Montant
  - `trans_date` - Date
  - (Pour les transfers : `receiver_bank`)

**Code d'implémentation :**

```php
<?php
session_start();
require_once('../database/db.php');

// Vérifier la session
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header('Location: login_client.php');
    exit;
}

require_once('../classes/customer.class.php');
$user = new customer($_SESSION['user_id']);

$transactions = [];
$error = '';
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : null;

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
```

**Utilisation dans le HTML :**

```php
<?php if ($account_id && count($transactions) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID Transaction</th>
                    <th>Type</th>
                    <th>Montant</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['trans_id'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($transaction['trans_type'] ?? 'N/A'); ?></td>
                        <td><?php echo number_format($transaction['amount'] ?? 0, 2); ?></td>
                        <td><?php echo htmlspecialchars($transaction['trans_date'] ?? 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
```

---

## 📝 Notes générales

1. **Sessions :** Toutes les pages (sauf login) doivent vérifier la session
2. **Sécurité :** Toujours utiliser `htmlspecialchars()` pour l'affichage
3. **Gestion d'erreurs :** Vérifier si les méthodes retournent un array ou un message d'erreur
4. **Transactions :** Les méthodes de transfer gèrent automatiquement les transactions PDO
5. **Triggers SQL :** Les soldes sont mis à jour automatiquement via les triggers

---

## 🔄 Prochaine étape

**ÉTAPE 3 :** Esquisser la structure HTML de chaque page
- Finaliser les formulaires
- Structurer les tableaux de données
- Ajouter les éléments d'interface manquants

