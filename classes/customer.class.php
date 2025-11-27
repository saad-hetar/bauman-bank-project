<?php 
require('main/db.php');

class customer
{
    private $customer_id;
    private $account_id;

    use passport
    {
        create_passport as private;
        read_all_passport as private;
        search_passport as private;
        update_passport as private;
        get_last_passport as private;
    }

    use customer_trait
    {
        create_customer as private;
        read_all_customer as private;
        search_customer as private;
        delete_customer as private;
        get_last_customer as private;
    }

    use transaction
    {
        read_all_transaction as private;
        read_transaction as private;
        search_transaction as private;
        cancel_payments as private;
        delete_card as private;
        get_last_card as private;
    }

    use card
    {
        create_card as private;
        read_all_card as private;
        read_card as private;
        search_card as private;
        delete_card as private;
        get_last_card as private;
    }

    use login
    {
        create_login as private;
        read_all_login as private;
        search_login as private;
        get_last_login as private;
    }
    
    use account
    {
        create_account as private;
        read_all_account as private;
        search_account as private;
        update_account as private;
        delete_account as private;
    }

    use transfer 
    {
        read_all_transfer as private;
        read_transfer as private;
        search_transfer as private;
        cancel_internal_transfer as private;
        cancel_external_transfer as private;
    }

    use saving_deposit
    {
        read_all_saving_deposit as private;
        search_saving_deposit as private;
        update_saving_deposit as private;
        delete_saving_deposit as private;
        delete_account as private;
    }


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

    public function history_transactions()
    {
        global $pdo;

        try
        {
            $sql = "SELECT trans_id, trans_type, amount, trans_date FROM transaction 
                        WHERE card_num IN (SELECT card_num FROM card WHERE account_id = :account_id)
                    UNION
                    SELECT trans_id, trans_type, amount, receiver_bank, trans_date FROM transfer
                        WHERE card_num IN (SELECT card_num FROM card WHERE account_id = :account_id)
                    ORDER BY trans_date";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':account_id' => $this->account_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to show history, <br>".$e->getMessage();
        }
    }
}