<?php 

trait transfer
{
    private $transfer_user_id;
    private $last_transfer_id;
    private const COMMISSION_INTERNAL = 0.03;
    private const COMMISSION_EXTERNAL = 0.08;
    use card;

    private function get_transfer_user_id($id)
    {
        $this->transfer_user_id = $id;
    }

    // transfer money from bank to customer to another bank by card
    private function add_money_central_bank_card($card_num, $amount, $bank_name)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE central_bank_customer
                    JOIN  bank_account ON central_bank_customer.bank_id = bank_account.bank_id
                    SET bank_account.balance = bank_account.balance - :amount, 
                        central_bank_customer.balance = central_bank_customer.balance + :amount
                    WHERE central_bank_customer.card_num = :card_num 
                    AND bank_account.bank_name = :bank_name";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":card_num" => $card_num,
                ":amount" => $amount,
                ":bank_name" => $bank_name
            ]);
        }
        catch(PDOException $e)
        {
            throw new PDOException("Error in add_money_central_bank_card: " . $e->getMessage(), (int)$e->getCode(), $e);
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

            $phone = $stmt->fetch();
            return $phone;
        }
        catch(PDOException $e)
        {
            throw new PDOException("Error in get_phone_by_card: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    // for internal and external
    private function get_card_by_phone($phone, $trans_type)
    {
        global $pdo;

        try
        {
            if($trans_type === "internal")
            {
                $sql = "SELECT card.card_num FROM card
                    JOIN account ON account.account_id = card.account_id
                    JOIN customer ON customer.customer_id = account.customer_id
                    WHERE customer.phone = :phone
                    ORDER BY card.balance DESC
                    LIMIT 1";
            }
            elseif($trans_type === "external")
            {
                $sql = "SELECT card_num FROM central_bank_customer
                        WHERE phone = :phone AND account.currency = central_bank_customer.currency
                        ORDER BY balance
                        LIMIT 1";
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":phone" => $phone
            ]);

            $card_num = $stmt->fetch();
            return $card_num;
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

            $sql = "INSERT INTO transaction (sender_card_num, trans_type, currency, 
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

            $this->add_money($receiver_card_num, $amount);
            $this->last_transaction_id = $pdo->lastInsertId();

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

            $sender_phone = $this->get_phone_by_card($sender_card_num, $trans_type);
            $receiver_phone = $this->get_phone_by_card($receiver_card_num, $trans_type);

            $this->remove_money($sender_card_num, $amount +  $commi);

            $sql = "INSERT INTO transaction (sender_card_num, trans_type, currency, 
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

            $this->add_money_central_bank_card($receiver_card_num, $amount, $receiver_bank);
            $this->last_transaction_id = $pdo->lastInsertId();

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

            $sender_card = $this->get_card_by_phone($sender_phone, $trans_type);
            $receiver_card = $this->get_card_by_phone($receiver_phone, $trans_type);

            $this->remove_money($sender_card, $amount +  $commi);

            $sql = "INSERT INTO transaction (sender_card_num, trans_type, currency, 
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
            ':receiver_phone' => $sender_phone,
            ':transfered_by' => $this->transfer_user_id
            ]);

            $this->add_money($receiver_card, $amount);
            $this->last_transaction_id = $pdo->lastInsertId();

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

            $sender_card = $this->get_card_by_phone($sender_phone, $trans_type);
            $receiver_card = $this->get_card_by_phone($receiver_phone, $trans_type);

            $this->remove_money($sender_card, $amount + $commi);

            $sql = "INSERT INTO transaction (sender_card_num, trans_type, currency, 
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
            ':receiver_phone' => $sender_phone,
            ':transfered_by' => $this->transfer_user_id
            ]);

            $this->add_money_central_bank_card($receiver_card, $amount, $receiver_bank);
            $this->last_transaction_id = $pdo->lastInsertId();

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
            $stmt = $pdo->query("SELECT * FROM transfer");

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
                    ORDER BY trans_date";

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
                    WHERE trans_id LIKE :search OR sender_phone LIKE :search
                    OR receiver_phone LIKE :search";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
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