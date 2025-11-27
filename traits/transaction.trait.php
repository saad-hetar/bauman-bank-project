<?php 

trait transaction
{
    private $transacted_user_id;
    private $last_transaction_id;
    private const COMMISSION = 0.03;
    use card;

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

            $sql = "INSERT INTO transaction (card_num, amount, transacted_by, trans_type, description)
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
    }

    public function read_all_transaction()
    {
        global $pdo; 
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM transaction");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all transactions, <br>".$e->getMessage();
        }
    }

    public function read_transaction($trans_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM transaction
                    WHERE trans_id = :trans_id
                    ORDER BY trans_date";

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
                    WHERE card_num LIKE :search OR trans_id LIKE :search
                    OR trans_date LIKE :search OR amount LIKE :search";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
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