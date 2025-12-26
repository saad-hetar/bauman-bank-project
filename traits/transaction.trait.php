<?php 
require_once __DIR__ . '/card.trait.php';

trait transaction
{
    private $transacted_user_id;
    public $last_transaction_id;
    private const COMMISSION = 0.03;
    use card{
        card::read_customer_cards as private;
        card::create_card as private _hidden_create_card;
    }

    private function get_transacted_user_id($id)
    {
        $this->transacted_user_id = $id;
    }

    public function deposit($card_num, $amount, $description)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $this->add_money($card_num, $amount);

            $sql = "INSERT INTO transaction (card_num, amount, transacted_by, trans_type, description)
                    VALUES (:card_num, :amount, :transacted_by, :trans_type, :description)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':card_num' => $card_num,
            ':amount' => $amount,
            ':transacted_by' => $this->transacted_user_id,
            ':trans_type' => "deposit",
            ':description' => $description
            ]);

            $this->last_transaction_id = $pdo->lastInsertId();

            $pdo->commit();
            return "deposit done successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to deposit, <br>".$e->getMessage();
        }
    }

    public function pay($card_num, $amount, $trans_type, $description, $commission = self::COMMISSION)
    {
        global $pdo;
        $commi = $amount * $commission;

        try
        {
            $pdo->beginTransaction();

            $this->remove_money($card_num, $amount + $commi);

            $sql = "INSERT INTO transaction (card_num, amount, transacted_by, trans_type, description, commission)
                    VALUES (:card_num, :amount, :transacted_by, :trans_type, :description, :commission)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':card_num' => $card_num,
            ':amount' => $amount,
            ':transacted_by' => $this->transacted_user_id,
            ':trans_type' => $trans_type,
            ':description' => $description,
            ':commission' => $commi
            ]);

            $this->last_transaction_id = $pdo->lastInsertId();

            $pdo->commit();
            return "you have paid successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to pay, <br>".$e->getMessage();
        }
    }

    public function withdraw($card_num, $amount, $description)
    {
        $this->pay($card_num, $amount, "withdraw", $description, 0);
        return "you have withdrawn successfully";
    }

    public function read_all_transaction()
    {
        global $pdo; 
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM transaction ORDER BY trans_date DESC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all transactions, <br>".$e->getMessage();
        }
    }

    public function transfer_between_cards($card_num_from, $card_num_to, $amount)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $this->add_money($card_num_to, $amount);
            $this->remove_money($card_num_from, $amount);

            $sql = "INSERT INTO transaction (card_num, amount, transacted_by, trans_type, description)
                    VALUES (:card_num, :amount, :transacted_by, 'deposit', 'deposit between cards')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':card_num' => $card_num_to,
            ':amount' => $amount,
            ':transacted_by' => $this->transacted_user_id
            ]);

            $this->last_transaction_id = $pdo->lastInsertId();

            $pdo->commit();
            return "deposit done successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to deposit, <br>".$e->getMessage();
        }
    }

    public function exchange_between_cards($card_num_from, $card_num_to, $amount_from, $amount_to)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $this->add_money($card_num_to, $amount_to);
            $this->remove_money($card_num_from, $amount_from);

            $sql = "INSERT INTO transaction (card_num, amount, transacted_by, trans_type, description)
                    VALUES (:card_num, :amount, :transacted_by, 'deposit', 'exchange between cards')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':card_num' => $card_num_to,
            ':amount' => $amount_to,
            ':transacted_by' => $this->transacted_user_id
            ]);

            $this->last_transaction_id = $pdo->lastInsertId();

            $pdo->commit();
            return "deposit done successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to deposit, <br>".$e->getMessage();
        }
    }

    public function read_transaction($trans_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM transaction
                    WHERE trans_id = :trans_id
                    ORDER BY trans_date DESC";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":trans_id" => $trans_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read the transaction, <br>".$e->getMessage();
        }
    }

    public function search_transaction($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM transaction
                    WHERE card_num LIKE :s1 OR trans_id LIKE :s2
                    OR trans_date LIKE :s3 OR amount LIKE :s4
                    ORDER BY trans_date DESC";

            $stmt = $pdo->prepare($sql);
            
            $like = "%{$search}%";

            $stmt->execute([
                ':s1' => $like,
                ':s2' => $like,
                ':s3' => $like,
                ':s4' => $like
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return "failed to search for transaction, <br>".$e->getMessage();
        }
    }


    public function cancel_payments($trans_id)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $sql = "UPDATE card
                    JOIN transaction ON transaction.card_num = card.card_num
                    SET card.balance = card.balance + transaction.amount + transaction.commission
                    WHERE transaction.trans_id = :trans_id
                    AND transaction.trans_type NOT IN ('deposit', 'withdraw')";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":trans_id" => $trans_id
            ]);

            if($stmt->rowCount() === 0)
            {
                $pdo->commit();
                return "you can't cancel 'deposit', 'withdraw'";
            } 
            else
            {
                $stmt = $pdo->prepare("DELETE FROM transaction WHERE trans_id = :trans_id");

                $stmt->execute([
                    "trans_id" => $trans_id
                ]);

                $pdo->commit();
                return "payment got canceled successfully";
            }
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to cancel the payment, <br>".$e->getMessage();
        }
    }

    public function get_last_transaction()
    {
        $this->read_transaction($this->last_transaction_id);
    }
}