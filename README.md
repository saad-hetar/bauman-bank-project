# Pages Front-End Client - Bauman Bank

## ÉTAPE 1 : Structure des fichiers créés ✅

### Fichiers créés :

1. **`login_client.php`**
   - Page de connexion pour les clients
   - Formulaire avec login_id et password
   - À connecter avec `login.trait.php` et `customer.class.php`

2. **`dashboard_client.php`**
   - Tableau de bord client (accessible après login)
   - Affichera les comptes du client
   - Boutons vers virement et historique
   - À connecter avec `account.trait.php` (méthode `read_customer_accounts`)

3. **`transfer_client.php`**
   - Formulaire pour effectuer un virement
   - Supporte virements internes et externes
   - Par numéro de carte ou par téléphone
   - À connecter avec `transfer.trait.php` :
     - `transfer_internal_card()`
     - `transfer_external_card()`
     - `transfer_internal_phone()`
     - `transfer_external_phone()`

4. **`history_client.php`**
   - Liste les transactions d'un compte
   - Sélection de compte par account_id
   - À connecter avec `customer.class.php` → méthode `history_transactions()`

5. **`profile_client.php`** (optionnel)
   - Affichage des données personnelles
   - Informations client et passeport
   - À connecter avec `customer.trait.php` et `passport.trait.php`

6. **`logout_client.php`**
   - Script de déconnexion
   - Détruit la session et redirige

---

## Prochaines étapes

### ÉTAPE 2 : Choisir les méthodes PHP à appeler
- Identifier les méthodes exactes à utiliser dans chaque page
- Vérifier les paramètres nécessaires
- Documenter les appels de méthodes

### ÉTAPE 3 : Esquisser la structure HTML de chaque page
- Finaliser les formulaires
- Structurer les tableaux de données
- Ajouter les éléments d'interface manquants

### ÉTAPE 4 : Lier les formulaires aux méthodes
- Implémenter le traitement POST
- Appeler les méthodes PHP appropriées
- Gérer les erreurs et les messages de succès

### ÉTAPE 5 : Résumer + idées d'amélioration
- Documenter les fonctionnalités
- Proposer des améliorations UX
- Suggestions de sécurité

---

## Notes

- Tous les fichiers sont dans le dossier `public/`
- Les fichiers utilisent le CSS existant dans `../assets/css/style.css`
- Les sessions PHP sont utilisées pour la gestion de l'authentification
- Tous les fichiers ont des commentaires TODO pour les prochaines étapes

