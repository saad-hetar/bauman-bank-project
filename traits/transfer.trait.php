<?php 
require_once __DIR__ . '/card.trait.php';

trait transfer
{
    private $transfer_user_id;
    public $last_transfer_id;
    private const COMMISSION_INTERNAL = 0.03;
    private const COMMISSION_EXTERNAL = 0.08;
    use card{card::read_customer_cards as private;}

    private function get_transfer_user_id($id)
    {
        $this->transfer_user_id = $id;
    }

    // transfer money from bank to customer to another bank by card
    private function add_money_central_bank_card($receiver_card_num, $amount, $receiver_bank)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE central_bank_customer
                    JOIN bank_account ON central_bank_customer.bank_id = bank_account.bank_id
                    SET bank_account.balance = bank_account.balance - :amount1, 
                        central_bank_customer.balance = central_bank_customer.balance + :amount2
                    WHERE central_bank_customer.card_num = :receiver_card_num
                    AND bank_account.bank_name = :receiver_bank";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":receiver_card_num" => $receiver_card_num,
                ":amount1"            => $amount,
                ":amount2"            => $amount,
                ":receiver_bank"     => $receiver_bank,
            ]);
        }
        catch(PDOException $e)
        {
            throw new PDOException(
                "Error in add_money_central_bank_card: " . $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }
    }


    // for internal and external
    private function get_phone_by_card($card_num, $trans_type)
    {
        global $pdo;

        try
        {
            if($trans_type === "internal")
            {
                $sql = "SELECT customer.phone FROM card
                        JOIN account ON account.account_id = card.account_id
                        JOIN customer ON customer.customer_id = account.customer_id
                        WHERE card.card_num = :card_num";
            }
            elseif($trans_type === "external")
            {
                $sql = "SELECT phone FROM central_bank_customer
                        WHERE card_num = :card_num";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":card_num" => $card_num
            ]);

            $phone = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($phone === false) {
                throw new PDOException("Card '$card_num' not found for trans_type '$trans_type'");
            }

            return $phone['phone'];
        }
        catch(PDOException $e)
        {
            throw new PDOException("Error in get_phone_by_card: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    // for internal and external
    private function get_card_by_phone($phone, $trans_type, $currency)
    {
        global $pdo;

        try
        {
            if($trans_type === "internal")
            {
                $sql = "SELECT card.card_num FROM card
                    JOIN account ON account.account_id = card.account_id
                    JOIN customer ON customer.customer_id = account.customer_id
                    WHERE customer.phone = :phone AND account.currency = :currency
                    ORDER BY card.balance DESC
                    LIMIT 1";
            }
            elseif($trans_type === "external")
            {
                $sql = "SELECT central_bank_customer.card_num FROM central_bank_customer
                        JOIN bank_account ON central_bank_customer.bank_id = bank_account.bank_id
                        WHERE central_bank_customer.phone = :phone AND bank_account.currency = :currency
                        ORDER BY central_bank_customer.balance
                        LIMIT 1";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":phone" => $phone,
                ":currency" => $currency
            ]);

            $card_num = $stmt->fetch();
            return $card_num['card_num'];
        }
        catch(PDOException $e)
        {
            throw new PDOException("Error in get_card_by_phone: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function transfer_internal_card($sender_card_num, $trans_type, $currency, $description,
                                           $amount, $receiver_card_num)
    {
        global $pdo;
        $commi = $amount * self::COMMISSION_INTERNAL;

        try
        {
            $pdo->beginTransaction();

            $sender_phone = $this->get_phone_by_card($sender_card_num, $trans_type);
            $receiver_phone = $this->get_phone_by_card($receiver_card_num, $trans_type);

            $this->remove_money($sender_card_num, $amount +  $commi);

            $sql = "INSERT INTO transfer (sender_card_num, trans_type, currency, 
                        description, amount, receiver_card_num, commission, sender_phone, 
                        receiver_phone, transfered_by)
                    VALUES (:sender_card_num, :trans_type, :currency, :description,
                        :amount, :receiver_card_num, :commission, :sender_phone, 
                        :receiver_phone, :transfered_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':sender_card_num' => $sender_card_num,
            ':trans_type' => $trans_type,
            ':currency' => $currency,
            ':description' => $description,
            ':amount' => $amount,
            ':receiver_card_num' => $receiver_card_num,
            ':commission' =>  $commi,
            ':sender_phone' => $sender_phone,
            ':receiver_phone' => $receiver_phone,
            ':transfered_by' => $this->transfer_user_id
            ]);

            $this->last_transfer_id = $pdo->lastInsertId();
            $this->add_money($receiver_card_num, $amount);

            $pdo->commit();
            return "transfered successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to transfer 'internal_card', <br>".$e->getMessage();
        }
    }

    public function transfer_external_card($sender_card_num, $trans_type, $currency, $description,
                                           $amount,$receiver_card_num, $receiver_bank)
    {
        global $pdo;
        $commi = $amount * self::COMMISSION_EXTERNAL;

        try
        {
            $pdo->beginTransaction();

            $sender_phone = $this->get_phone_by_card($sender_card_num, "internal");
            $receiver_phone = $this->get_phone_by_card($receiver_card_num, "external");

            $this->remove_money($sender_card_num, $amount +  $commi);

            $sql = "INSERT INTO transfer (sender_card_num, trans_type, currency, 
                        description, amount, receiver_card_num, commission, sender_phone, 
                        receiver_phone, transfered_by, receiver_bank)
                    VALUES (:sender_card_num, :trans_type, :currency, :description,
                        :amount, :receiver_card_num, :commission, :sender_phone, 
                        :receiver_phone, :transfered_by, :receiver_bank)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':sender_card_num' => $sender_card_num,
                ':trans_type' => $trans_type,
                ':currency' => $currency,
                ':description' => $description,
                ':amount' => $amount,
                ':receiver_card_num' => $receiver_card_num,
                ':commission' =>  $commi,
                ':receiver_bank' =>  $receiver_bank,
                ':sender_phone' => $sender_phone,
                ':receiver_phone' => $receiver_phone,
                ':transfered_by' => $this->transfer_user_id
                ]);
                
            $amount = (float)$amount;
                
            $this->last_transfer_id = $pdo->lastInsertId();
            $this->add_money_central_bank_card($receiver_card_num, $amount, $receiver_bank);

            $pdo->commit();
            return "transfered successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to transfer 'external_card', <br>".$e->getMessage();
        }
    }


    public function transfer_internal_phone($sender_phone, $trans_type, $currency, $description,
                                            $amount, $receiver_phone)
    {
        global $pdo;
        $commi = $amount * self::COMMISSION_INTERNAL;

        try
        {
            $pdo->beginTransaction();

            $sender_card = $this->get_card_by_phone($sender_phone, $trans_type, $currency);
            $receiver_card = $this->get_card_by_phone($receiver_phone, $trans_type, $currency);

            $this->remove_money($sender_card, $amount +  $commi);

            $sql = "INSERT INTO transfer (sender_card_num, trans_type, currency, 
                        description, amount, receiver_card_num, commission, sender_phone, 
                        receiver_phone, transfered_by)
                    VALUES (:sender_card_num, :trans_type, :currency, :description,
                        :amount, :receiver_card_num, :commission, :sender_phone, 
                        :receiver_phone, :transfered_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':sender_card_num' => $sender_card,
            ':trans_type' => $trans_type,
            ':currency' => $currency,
            ':description' => $description,
            ':amount' => $amount,
            ':receiver_card_num' => $receiver_card,
            ':commission' =>  $commi,
            ':sender_phone' => $sender_phone,
            ':receiver_phone' => $receiver_phone,
            ':transfered_by' => $this->transfer_user_id
            ]);

            $this->last_transfer_id = $pdo->lastInsertId();
            $this->add_money($receiver_card, $amount);

            $pdo->commit();
            return "transfered successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to transfer 'internal_phone', <br>".$e->getMessage();
        }
    }

    public function transfer_external_phone($sender_phone, $trans_type, $currency, $description, 
                                            $amount, $receiver_phone, $receiver_bank)
    {
        global $pdo;
        $commi = $amount * self::COMMISSION_EXTERNAL;

        try 
        {
            $pdo->beginTransaction();

            $sender_card = $this->get_card_by_phone($sender_phone, "internal", $currency);
            $receiver_card = $this->get_card_by_phone($receiver_phone, "external", $currency);

            $this->remove_money($sender_card, $amount + $commi);

            $sql = "INSERT INTO transfer (sender_card_num, trans_type, currency, 
                        description, amount, receiver_card_num, commission, sender_phone, 
                        receiver_phone, transfered_by, receiver_bank)
                    VALUES (:sender_card_num, :trans_type, :currency, :description,
                        :amount, :receiver_card_num, :commission, :sender_phone, 
                        :receiver_phone, :transfered_by, :receiver_bank)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
            ':sender_card_num' => $sender_card,
            ':trans_type' => $trans_type,
            ':currency' => $currency,
            ':description' => $description,
            ':amount' => $amount,
            ':receiver_card_num' => $receiver_card,
            ':commission' =>  $commi,
            ':receiver_bank' =>  $receiver_bank,
            ':sender_phone' => $sender_phone,
            ':receiver_phone' => $receiver_phone,
            ':transfered_by' => $this->transfer_user_id
            ]);

            $this->last_transfer_id = $pdo->lastInsertId();
            $this->add_money_central_bank_card($receiver_card, $amount, $receiver_bank);

            $pdo->commit();
            return "transfered successfully";
        } 
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to transfer 'external_phone', <br>".$e->getMessage();
        }
    }

    public function read_all_transfer()
    {
        global $pdo; 
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM transfer ORDER BY trans_date DESC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all transfer, <br>".$e->getMessage();
        }
    }

    public function read_transfer($trans_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM transfer
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
            return "failed to read the ransfer, <br>".$e->getMessage();
        }
    }

    public function search_transfer($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM transfer
                    WHERE trans_id LIKE :s1 OR sender_phone LIKE :s2
                    OR receiver_phone LIKE :s3
                    ORDER BY trans_date DESC";

            $stmt = $pdo->prepare($sql);
            
            $like = "%{$search}%";

            $stmt->execute([
                ':s1' => $like,
                ':s2' => $like,
                ':s3' => $like
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return "failed to search for trnasfer, <br>".$e->getMessage();
        }
    }


    /*public function cancel_internal_transfer($trans_id)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $sql = "SELECT 
                        CASE 
                            WHEN card.balance >= transfer.amount THEN TRUE
                            ELSE FALSE
                        END AS is_enough
                    FROM card
                    JOIN transfer ON transfer.receiver_card_num = card.card_num
                    WHERE transfer.trans_id = :trans_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([":trans_id" => $trans_id]);

            if($stmt->fetchColumn() === "1")
            {
                // sender_card_num
                $sql1 = "UPDATE card 
                        JOIN transfer ON transfer.sender_card_num = card.card_num
                        SET card.balance = card.balance + transfer.amount + transfer.commission
                        WHERE transfer.trans_id = :trans_id";

                $stmt = $pdo->prepare($sql1);
                $stmt->execute([":trans_id" => $trans_id]);

                // receiver_card_num
                $sql2 = "UPDATE card 
                        JOIN transfer ON transfer.receiver_card_num = card.card_num
                        SET card.balance = card.balance - transfer.amount
                        WHERE transfer.trans_id = :trans_id";

                $stmt = $pdo->prepare($sql2);
                $stmt->execute([":trans_id" => $trans_id]);

                // to delete from transfer
                $stmt = $pdo->prepare("DELETE FROM transfer WHERE trans_id = :trans_id");
                $stmt->execute([
                    "trans_id" => $trans_id
                ]);

                $this->commi();
                return "internal transfer got canceled succsessfully";
            }
            else
            {
                throw new PDOException("error. money isn't enough for the transaction");
            }
            
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to cancel internal transfer, <br>".$e->getMessage();
        }
    }

    public function cancel_external_transfer($trans_id)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            // sender_card_num
            $sql1 = "UPDATE card 
                    JOIN transfer ON transfer.sender_card_num = card.card_num
                    SET card.balance = card.balance + transfer.amount + transfer.commission
                    WHERE transfer.trans_id = :trans_id";

            $stmt = $pdo->prepare($sql1);
            $stmt->execute([":trans_id" => $trans_id]);

            // receiver_card_num
            $sql2 = "UPDATE central_bank_customer 
                    JOIN transfer ON transfer.receiver_card_num = central_bank_customer.card_num
                    JOIN bank_account ON central_bank_customer.bank_id = bank_account.bank_id
                    SET central_bank_customer.balance = central_bank_customer.balance - transfer.amount,
                        bank_account.balance = bank_account.balance + transfer.amount
                    WHERE transfer.trans_id = :trans_id 
                    AND transfer.bank_name = bank_account.bank_name";

            $stmt = $pdo->prepare($sql2);
            $stmt->execute([":trans_id" => $trans_id]);

            // to delete from transfer
            $stmt = $pdo->prepare("DELETE FROM transfer WHERE trans_id = :trans_id");

            $stmt->execute([
                "trans_id" => $trans_id
            ]);

            $this->commi();
            return "external transfer got canceled succsessfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to cancel external transfer, <br>".$e->getMessage();
        }
    }*/

    public function get_last_transfer()
    {
        $this->read_transfer($this->last_transfer_id);
    }
}