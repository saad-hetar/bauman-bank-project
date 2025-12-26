<?php 
// require_once __DIR__ . '/account.trait.php';

trait card
{
    public $last_card_num;
    // use account;

    public function create_card($account_id, $rethraw = false)
    {
        global $pdo;

        $prefix = "4"; 
        $remaining = "";
        for ($i = 0; $i < 15; $i++) {
            $remaining .= mt_rand(0, 9);
        }
        $card_num = $prefix . $remaining;

        $cvv = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT);

        $expire_date = date('Y-m-d', strtotime('+5 years'));

        try 
        {
            $sql = "INSERT INTO card(account_id, card_num, cvv, expire_date)
                    VALUES (:account_id, :card_num, :cvv, :expire_date)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':account_id' => $account_id,
                ':card_num' => $card_num,
                ':cvv' => $cvv,
                ':expire_date' => $expire_date
            ]);

            $this->last_card_num = $pdo->lastInsertId();

            if($rethraw === false) { return "created successfully"; }
        } 
        catch (PDOException $e) 
        {
            if($rethraw)
            {
                throw new PDOException("Error in create_card: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            else
            {
                return "failed to create a account, <br>".$e->getMessage();
            }
        }
    }

    public function read_all_card()
    {
        try 
        {
            global $pdo;
            
            $stmt = $pdo->query("SELECT card.card_num, account.account_id, passport.last_name,
                                passport.first_name, account.account_status, account.currency,
                                card.cvv, card.expire_date, card.balance
                                FROM card 
                                JOIN account ON account.account_id = card.account_id
                                JOIN customer  ON customer.customer_id = account.customer_id
                                JOIN passport ON passport.passport_id = customer.passport_id
                                ORDER BY passport.last_name");

            return $stmt->fetchAll();
        } 
        catch (PDOException $e) 
        {
            return "failed to read all cards <br>".$e->getMessage();
        }
    }

    public function read_card($card_num, $rethraw = false)
    {
        global $pdo;

        try
        {
            $sql = "SELECT card.card_num, account.account_id, passport.last_name,
                    passport.first_name, account.account_status, account.currency,
                    card.card_num, card.cvv, card.expire_date, card.balance
                    FROM card 
                    JOIN account ON account.account_id = card.account_id
                    JOIN customer  ON customer.customer_id = account.customer_id
                    JOIN passport ON passport.passport_id = customer.passport_id
                    WHERE card.card_num = :card_num
                    ORDER BY card.balance";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":card_num" => $card_num
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            if($rethraw)
            {
                throw new PDOException("error in read account " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            else
            {
                return "failed to read the card, <br>".$e->getMessage();
            }
        }
    }

    // to show all the accounts of the user
    public function read_customer_cards($account_id)
    {
        global $pdo;
       
        try
        {
            $sql = "SELECT c.*, a.currency  FROM card c
                    JOIN account a ON a.account_id = c.account_id
                    WHERE c.account_id = :account_id
                    ORDER BY balance DESC";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':account_id' => $account_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read customer accounts, <br>".$e->getMessage();
        }
    }

    public function read_all_customer_cards($customer_id)
    {
        global $pdo;
       
        try
        {
            $sql = "SELECT c.*, a.currency 
                    FROM card c
                    JOIN account a ON a.account_id = c.account_id
                    JOIN customer cu ON cu.customer_id = a.customer_id
                    WHERE a.customer_id = :customer_id   
                    ORDER BY c.balance DESC";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':customer_id' => $customer_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read customer accounts, <br>".$e->getMessage();
        }
    }

    public function search_card($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT card.card_num, account.account_id, passport.last_name,
                    passport.first_name, account.account_status, account.currency,
                    card.card_num, card.cvv, card.expire_date, card.balance
                    FROM card 
                    JOIN account ON account.account_id = card.account_id
                    JOIN customer  ON customer.customer_id = account.customer_id
                    JOIN passport ON passport.passport_id = customer.passport_id
                    WHERE card.card_num LIKE :s1 OR account.account_id LIKE :s2
                    OR passport.last_name LIKE :s3 OR passport.first_name LIKE :s4";

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
            return "failed to search for the card, <br>".$e->getMessage();
        }
    }

    public function delete_card($card_num)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("DELETE FROM card WHERE card_num = :card_num");

            $stmt->execute([
                ':card_num' => $card_num
            ]);

            return "deleted successfully";
        }
        catch(PDOException $e)
        {
            return "failed to delete the card, <br>".$e->getMessage();
        }
    }


    private function add_money($card_num, $amount)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE card SET balance = balance + :amount WHERE card_num = :card_num";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":amount" => $amount,
                ":card_num" => $card_num
            ]);
        }
        catch(PDOException $e)
        {
            throw new PDOException("eror in add money " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function check_amount_before_trans($card_num, $amount)
    {
        global $pdo;

        try
        {
            $sql = "SELECT 
                        CASE 
                            WHEN balance >= :amount THEN 1
                            ELSE 0
                        END AS is_enough
                    FROM card
                    WHERE card_num = :card_num";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ":card_num" => $card_num,
                ":amount" => $amount
            ]);

            $result = $stmt->fetchColumn();

            return $result;
        }
        catch(PDOException $e)
        {
            throw new PDOException("error in check_amount_before_trans" . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    private function remove_money($card_num, $amount)
    {
        global $pdo;

        try
        {
            $check = $this->check_amount_before_trans($card_num, $amount);
            if($check)
            {
                $sql = "UPDATE card SET balance = balance - :amount WHERE card_num = :card_num";

                $stmt = $pdo->prepare($sql);
                return $stmt->execute([
                    ":amount" => $amount,
                    ":card_num" => $card_num
                ]);
            }
            elseif(!$check)
            {
                throw new PDOException("error. sojwfljsl");
            }
            else
            {
                throw new PDOException("error. money isn't enough for the transaction");
            }
        }
        catch(PDOException $e)
        {
            throw new PDOException("error in remove money " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function get_last_card()
    {
        $this->read_card($this->last_card_id);
    }
}