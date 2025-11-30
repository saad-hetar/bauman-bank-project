<?php
/**
 * ÉTAPE 4 - Implémentation complète
 * 
 * Page de virement entre comptes
 * 
 * ✅ Implémenté:
 * - Vérification de la session client
 * - Formulaire pour faire un virement (montant, compte destinataire, type: interne/externe)
 * - Utilisation de transfer.trait.php:
 *   - transfer_internal_card() pour virement interne
 *   - transfer_external_card() pour virement externe
 *   - transfer_internal_phone() pour virement par téléphone
 *   - transfer_external_phone() pour virement externe par téléphone
 * - Validation du formulaire
 * - Affichage du résultat (succès/erreur)
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
$message = '';
$message_type = '';

// ÉTAPE 4: Traitement du formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transfer'])) {
    $transfer_type = $_POST['transfer_type'] ?? '';
    $sender = trim($_POST['sender_card_num'] ?? '');
    $receiver = trim($_POST['receiver'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $currency = $_POST['currency'] ?? 'USD';
    $description = trim($_POST['description'] ?? '');
    $receiver_bank = trim($_POST['receiver_bank'] ?? '');
    
    // Validation
    if (empty($transfer_type) || empty($sender) || empty($receiver) || $amount <= 0) {
        $message = 'Veuillez remplir tous les champs obligatoires avec des valeurs valides';
        $message_type = 'alert-error';
    } else {
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
                        $sender,
                        'internal',
                        $currency,
                        $description,
                        $amount,
                        $receiver
                    );
                    break;
                    
                case 'external_phone':
                    if (empty($receiver_bank)) {
                        throw new Exception('Le nom de la banque destinataire est requis pour un virement externe');
                    }
                    $result = $user->transfer_external_phone(
                        $sender,
                        'external',
                        $currency,
                        $description,
                        $amount,
                        $receiver,
                        $receiver_bank
                    );
                    break;
                    
                default:
                    throw new Exception('Type de virement invalide');
            }
            
            // Vérifier le résultat
            if (strpos($result, 'successfully') !== false || strpos($result, 'transfered successfully') !== false) {
                $message = 'Virement effectué avec succès !';
                $message_type = 'alert-success';
                
                // Réinitialiser le formulaire après succès
                $_POST = [];
            } else {
                $message = 'Erreur : ' . $result;
                $message_type = 'alert-error';
            }
        } catch (Exception $e) {
            $message = 'Erreur : ' . $e->getMessage();
            $message_type = 'alert-error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virement - Bauman Bank</title>
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
                <a href="transfer_client.php" class="nav-link active">Virement</a>
                <a href="history_client.php" class="nav-link">Historique</a>
                <a href="profile_client.php" class="nav-link">Profil</a>
                <a href="logout_client.php" class="nav-link">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="dashboard-header">
            <h1>Effectuer un Virement</h1>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $message_type; ?>"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- TODO ÉTAPE 3: Structurer le formulaire de virement -->
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <h3>Nouveau Virement</h3>
            <form method="POST" action="transfer_client.php">
                
                <!-- ÉTAPE 3: Formulaire structuré avec validation -->
                <div class="form-group">
                    <label for="transfer_type">
                        Type de virement <span style="color: var(--danger-color);">*</span>
                    </label>
                    <select id="transfer_type" name="transfer_type" required>
                        <option value="">-- Sélectionner un type --</option>
                        <option value="internal_card">💳 Interne (par numéro de carte)</option>
                        <option value="external_card">🏦 Externe (par numéro de carte)</option>
                        <option value="internal_phone">📱 Interne (par téléphone)</option>
                        <option value="external_phone">🌐 Externe (par téléphone)</option>
                    </select>
                    <small class="form-help">Choisissez le type de virement selon votre besoin</small>
                </div>

                <div class="form-group" id="sender_group">
                    <label for="sender_card_num" id="sender_label">
                        Expéditeur <span style="color: var(--danger-color);">*</span>
                    </label>
                    <input type="text" 
                           id="sender_card_num" 
                           name="sender_card_num" 
                           placeholder="Numéro de carte ou téléphone"
                           required
                           pattern="[0-9]+"
                           title="Entrez uniquement des chiffres">
                    <small class="form-help" id="sender_help">Votre numéro de carte ou téléphone</small>
                </div>

                <div class="form-group">
                    <label for="receiver">
                        Destinataire <span style="color: var(--danger-color);">*</span>
                    </label>
                    <input type="text" 
                           id="receiver" 
                           name="receiver" 
                           placeholder="Numéro de carte ou téléphone du destinataire"
                           required
                           pattern="[0-9]+"
                           title="Entrez uniquement des chiffres">
                    <small class="form-help" id="receiver_help">Numéro de carte ou téléphone du destinataire</small>
                </div>

                <div class="form-group">
                    <label for="amount">
                        Montant <span style="color: var(--danger-color);">*</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" 
                               id="amount" 
                               name="amount" 
                               step="0.01" 
                               min="0.01" 
                               placeholder="0.00" 
                               required
                               style="flex: 1;">
                        <select id="currency" name="currency" required style="width: 100px;">
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="RUB">RUB</option>
                        </select>
                    </div>
                    <small class="form-help">
                        Montant minimum : 0.01 | 
                        Commission interne : 3% | 
                        Commission externe : 8%
                    </small>
                    <div id="commission_info" style="margin-top: 8px; padding: 8px; background: var(--bg-color); border-radius: 4px; display: none;">
                        <small style="color: var(--warning-color);">
                            <strong>Commission estimée :</strong> <span id="commission_amount">0.00</span> 
                            <span id="commission_currency">USD</span>
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description (optionnel)</label>
                    <textarea id="description" 
                              name="description" 
                              rows="3" 
                              placeholder="Ajouter une note pour ce virement (ex: Remboursement, Achat, etc.)"
                              maxlength="100"></textarea>
                    <small class="form-help">Maximum 100 caractères</small>
                </div>

                <div class="form-group" id="receiver_bank_group" style="display: none;">
                    <label for="receiver_bank">
                        Banque destinataire <span style="color: var(--danger-color);">*</span>
                    </label>
                    <input type="text" 
                           id="receiver_bank" 
                           name="receiver_bank" 
                           placeholder="Nom de la banque (ex: Bauman Bank, Sberbank, etc.)"
                           style="text-transform: capitalize;">
                    <small class="form-help">Requis uniquement pour les virements externes</small>
                </div>
                
                <div class="form-group" style="padding: 15px; background: var(--bg-color); border-radius: 8px; margin-top: 20px;">
                    <strong style="display: block; margin-bottom: 10px;">Récapitulatif :</strong>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Montant :</span>
                        <span id="summary_amount">0.00</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                        <span>Commission :</span>
                        <span id="summary_commission" style="color: var(--warning-color);">0.00</span>
                    </div>
                    <hr style="margin: 10px 0; border-color: var(--border-color);">
                    <div style="display: flex; justify-content: space-between; font-weight: 600;">
                        <span>Total débité :</span>
                        <span id="summary_total" style="color: var(--danger-color);">0.00</span>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="submit" name="transfer" class="btn btn-primary btn-block">Effectuer le virement</button>
                    <a href="dashboard_client.php" class="btn btn-secondary btn-block">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- ÉTAPE 3: JavaScript amélioré pour la gestion dynamique du formulaire -->
    <script>
        const transferType = document.getElementById('transfer_type');
        const bankGroup = document.getElementById('receiver_bank_group');
        const receiverBank = document.getElementById('receiver_bank');
        const senderLabel = document.getElementById('sender_label');
        const senderHelp = document.getElementById('sender_help');
        const receiverHelp = document.getElementById('receiver_help');
        const amountInput = document.getElementById('amount');
        const currencySelect = document.getElementById('currency');
        const commissionInfo = document.getElementById('commission_info');
        const commissionAmount = document.getElementById('commission_amount');
        const commissionCurrency = document.getElementById('commission_currency');
        const summaryAmount = document.getElementById('summary_amount');
        const summaryCommission = document.getElementById('summary_commission');
        const summaryTotal = document.getElementById('summary_total');
        
        // Gestion du type de virement
        transferType.addEventListener('change', function() {
            const isExternal = this.value === 'external_card' || this.value === 'external_phone';
            const isPhone = this.value === 'internal_phone' || this.value === 'external_phone';
            
            // Afficher/masquer le champ banque
            if (isExternal) {
                bankGroup.style.display = 'block';
                receiverBank.required = true;
            } else {
                bankGroup.style.display = 'none';
                receiverBank.required = false;
                receiverBank.value = '';
            }
            
            // Mettre à jour les labels et placeholders
            if (isPhone) {
                senderLabel.innerHTML = 'Téléphone expéditeur <span style="color: var(--danger-color);">*</span>';
                senderHelp.textContent = 'Votre numéro de téléphone';
                receiverHelp.textContent = 'Numéro de téléphone du destinataire';
                document.getElementById('sender_card_num').placeholder = 'Numéro de téléphone';
                document.getElementById('receiver').placeholder = 'Numéro de téléphone du destinataire';
            } else {
                senderLabel.innerHTML = 'Ma carte (expéditeur) <span style="color: var(--danger-color);">*</span>';
                senderHelp.textContent = 'Votre numéro de carte';
                receiverHelp.textContent = 'Numéro de carte du destinataire';
                document.getElementById('sender_card_num').placeholder = 'Numéro de carte';
                document.getElementById('receiver').placeholder = 'Numéro de carte du destinataire';
            }
            
            updateSummary();
        });
        
        // Calcul de la commission et du total
        function updateSummary() {
            const amount = parseFloat(amountInput.value) || 0;
            const currency = currencySelect.value;
            const isExternal = transferType.value === 'external_card' || transferType.value === 'external_phone';
            
            if (amount > 0 && transferType.value) {
                const commissionRate = isExternal ? 0.08 : 0.03;
                const commission = amount * commissionRate;
                const total = amount + commission;
                
                commissionInfo.style.display = 'block';
                commissionAmount.textContent = commission.toFixed(2);
                commissionCurrency.textContent = currency;
                summaryAmount.textContent = amount.toFixed(2) + ' ' + currency;
                summaryCommission.textContent = commission.toFixed(2) + ' ' + currency;
                summaryTotal.textContent = total.toFixed(2) + ' ' + currency;
            } else {
                commissionInfo.style.display = 'none';
                summaryAmount.textContent = '0.00';
                summaryCommission.textContent = '0.00';
                summaryTotal.textContent = '0.00';
            }
        }
        
        amountInput.addEventListener('input', updateSummary);
        currencySelect.addEventListener('change', updateSummary);
        
        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const amount = parseFloat(amountInput.value);
            if (amount <= 0) {
                e.preventDefault();
                alert('Le montant doit être supérieur à 0');
                return false;
            }
            
            if (transferType.value === 'external_card' || transferType.value === 'external_phone') {
                if (!receiverBank.value.trim()) {
                    e.preventDefault();
                    alert('Le nom de la banque destinataire est requis pour un virement externe');
                    return false;
                }
            }
            
            // Confirmation avant envoi
            if (!confirm('Confirmez-vous ce virement ?\n\nMontant: ' + amount.toFixed(2) + ' ' + currencySelect.value)) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>

