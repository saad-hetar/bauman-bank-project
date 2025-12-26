<?php 
require_once(__DIR__ . '/../database/db.php');
require_once(__DIR__ . '/../traits/passport.trait.php');
require_once(__DIR__ . '/../traits/transaction.trait.php');
require_once(__DIR__ . '/../traits/customer_tr.trait.php');
require_once(__DIR__ . '/../traits/transfer.trait.php');
require_once(__DIR__ . '/../traits/login.trait.php');
require_once(__DIR__ . '/../traits/card.trait.php');
require_once(__DIR__ . '/../traits/account.trait.php');
require_once(__DIR__ . '/../traits/saving_deposit.trait.php');
require_once(__DIR__ . '/../traits/central_bank_customer.trait.php');

class customer
{
    private $customer_id;
    private $account_id;

    use passport;
    use central_bank_customer;

    use customer_tr, card, transfer, transaction, account, login
    {
        // read_customer_cards
        card::read_customer_cards insteadof customer_tr;
        card::read_customer_cards insteadof transfer;
        card::read_customer_cards insteadof transaction;
        card::read_customer_cards as private _hidden_read_customer_cards;

        customer_tr::read_customer_cards as read_card_customer_cards;
        transfer::read_customer_cards    as read_transfer_customer_cards;
        transaction::read_customer_cards as read_transaction_customer_cards;

        //read_customer_accounts
        account::read_customer_accounts insteadof customer_tr;
        account::read_customer_accounts as private _hidden_read_customer_accounts;
        customer_tr::read_customer_accounts as read_account_customer_accounts;

        // Hide sensitive customer methods
        customer_tr::delete_customer as private _hidden_delete_customer;

        // Hide card methods
        card::read_all_card as private _hidden_read_all_card;
        card::search_card   as private _hidden_search_card;
        card::delete_card   as private _hidden_delete_card;

        // Hide transaction methods 
        transaction::cancel_payments as private _hidden_cancel_payments;

        // Hide account methods
        account::delete_account as private _hidden_delete_account;

        // Hide login methods not needed 
        login::read_all_login as private _hidden_read_all_login;
        login::search_login   as private _hidden_search_login;

        login::create_login as private _hidden_login_create_login;
        customer_tr::create_login as private _hidden_customer_create_login;
    }

    use saving_deposit
    {
        delete_saving_deposit as private;
    }

    use customer_tr{
        customer_tr::create_customer as private;
        customer_tr::search_customer as private;
        customer_tr::delete_customer as private;
        customer_tr::get_last_customer as private;
    }

    // use passport, customer_tr, transaction
    // {
    //     passport::create_passport insteadof customer_tr;
    //     customer_tr::delete_card insteadof transaction;
    //     transaction::create_card insteadof customer_tr;
        
    //     passport::create_passport as private;
    //     passport::read_all_passport as private;
    //     passport::search_passport as private;
    //     passport::update_passport as private;
    //     passport::get_last_passport as private;

    //     customer_tr::create_customer as private;
    //     customer_tr::read_all_customer as private;
    //     customer_tr::search_customer as private;
    //     customer_tr::delete_customer as private;
    //     customer_tr::get_last_customer as private;

    //     transaction::read_all_transaction as private;
    //     transaction::read_transaction as private;
    //     transaction::search_transaction as private;
    //     transaction::cancel_payments as private;
    //     transaction::delete_card as private;
    //     transaction::get_last_card as private;
    // }

    // use card
    // {
    //     create_card as private;
    //     read_all_card as private;
    //     read_card as private;
    //     search_card as private;
    //     delete_card as private;
    //     get_last_card as private;
    // }

    // use login
    // {
    //     create_login as private;
    //     read_all_login as private;
    //     search_login as private;
    //     get_last_login as private;
    // }
    
    // use account
    // {
    //     create_account as private;
    //     read_all_account as private;
    //     search_account as private;
    //     update_account as private;
    //     delete_account as private;
    // }

    // use transfer 
    // {
    //     read_all_transfer as private;
    //     read_transfer as private;
    //     search_transfer as private;
    // }

    // use saving_deposit
    // {
    //     read_all_saving_deposit as private;
    //     search_saving_deposit as private;
    //     update_saving_deposit as private;
    //     delete_saving_deposit as private;
    //     delete_account as private;
    // }


    function __construct($id)
    {
        $this->customer_id = $id;

        $this->get_customer_user_id($id);
        $this->get_account_user_id($id);
        $this->get_transacted_user_id($id);
        $this->get_transfer_user_id($id);
    }

    public function get_account_id($account_id)
    {
        $this->account_id = $account_id;
    }

    public function history_transactions($account_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM (
                                -- Transactions from your cards
                                SELECT
                                    t.trans_id,
                                    t.card_num              AS card_num,
                                    t.trans_type,
                                    t.amount,
                                    t.description,
                                    t.commission,
                                    NULL                    AS receiver_bank,
                                    'transaction'           AS source,
                                    t.trans_date
                                FROM `transaction` t
                                JOIN card c ON c.card_num = t.card_num
                                WHERE c.account_id = :account_id1

                                UNION ALL

                                -- Transfers sent from your account
                                SELECT
                                    tr.trans_id,
                                    tr.sender_card_num      AS card_num,
                                    tr.trans_type,
                                    tr.amount,
                                    tr.description,
                                    tr.commission,
                                    tr.receiver_bank,
                                    'transfer_sent'         AS source,
                                    tr.trans_date
                                FROM transfer tr
                                JOIN card c ON c.card_num = tr.sender_card_num
                                WHERE c.account_id = :account_id2

                                UNION ALL

                                -- Transfers received by your account
                                SELECT
                                    tr.trans_id,
                                    tr.receiver_card_num    AS card_num,
                                    tr.trans_type,
                                    tr.amount,
                                    tr.description,
                                    tr.commission,
                                    tr.receiver_bank,
                                    'transfer_received'     AS source,
                                    tr.trans_date
                                FROM transfer tr
                                JOIN card c ON c.card_num = tr.receiver_card_num
                                WHERE c.account_id = :account_id3
                            ) AS history
                            ORDER BY trans_date DESC;";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':account_id1' => $account_id,
                ':account_id2' => $account_id,
                ':account_id3' => $account_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to show history, <br>".$e->getMessage();
        }
    }


    private function exchange(float $amount, string $from, string $to): float
    {
        $rates = [
            'usd' => 1.0,
            'rub' => 90.0,
            'eus' => 0.92
        ];

        $from = strtolower($from);
        $to   = strtolower($to);

        if (!isset($rates[$from], $rates[$to])) {
            throw new Exception("Unsupported currency");
        }

        // convert via USD
        $usdAmount = $amount / $rates[$from];
        return round($usdAmount * $rates[$to], 2);
    }

    public function exchange_between_cards(string $card_num_from, string $card_num_to, float $amount_from): array
    {
        // Ensure card numbers are clean
        $card_num_from = trim($card_num_from);
        $card_num_to   = trim($card_num_to);

        // Fetch all cards of the customer
        $user_id = $_SESSION['user_id'] ?? 0;
        if (!$user_id) {
            throw new Exception("User session not found");
        }

        $allCards = $this->read_all_customer_cards($user_id);

        $fromCard = null;
        $toCard   = null;

        foreach ($allCards as $c) {
            // Make sure to compare exact trimmed card numbers
            if (trim($c['card_num']) === $card_num_from) {
                $fromCard = $c;
            }
            if (trim($c['card_num']) === $card_num_to) {
                $toCard = $c;
            }
        }

        if (!$fromCard) {
            throw new Exception("Source card not found");
        }

        if (!$toCard) {
            throw new Exception("Destination card not found");
        }

        $currencyFrom = $fromCard['currency'];
        $currencyTo   = $toCard['currency'];

        // Convert amount
        if ($currencyFrom === $currencyTo) {
            $amount_to = round($amount_from, 2);
        } else {
            $amount_to = $this->exchange($amount_from, $currencyFrom, $currencyTo);
        }

        return [
            'amount_from'   => round($amount_from, 2),
            'amount_to'     => $amount_to,
            'currency_from' => $currencyFrom,
            'currency_to'   => $currencyTo
        ];
    }


public function apply_exchange(string $card_num_from, string $card_num_to, float $amount_from, float $amount_to): string
{
    global $pdo;

    try {
        $pdo->beginTransaction();

        // Deduct from source card
        $sql1 = "UPDATE card SET balance = balance - :amount WHERE card_num = :card_num";
        $stmt1 = $pdo->prepare($sql1);
        $stmt1->execute([
            ':amount'   => $amount_from,
            ':card_num' => $card_num_from
        ]);

        // Add to destination card
        $sql2 = "UPDATE card SET balance = balance + :amount WHERE card_num = :card_num";
        $stmt2 = $pdo->prepare($sql2);
        $stmt2->execute([
            ':amount'   => $amount_to,
            ':card_num' => $card_num_to
        ]);

        $pdo->commit();
        return "Successfully exchanged {$amount_from} to {$amount_to}";
    } catch (Exception $e) {
        $pdo->rollBack();
        return "Exchange failed: " . $e->getMessage();
    }
}


}