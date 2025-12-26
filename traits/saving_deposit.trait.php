<?php
require_once __DIR__ . '/card.trait.php';

trait saving_deposit
{
    private $last_deposit_id;
    // per month:
    private const INTEREST_RATE1 = 0.0121;
    private const INTEREST_RATE2 = 0.0127; // more than 500000
    private const INTEREST_RATE3 = 0.0132; // more than 2000000
    use card;

    public function create_saving_deposit($card_num, $account_id, $amount,
                                          $currency, $deposit_type, $period_months)
    {
        global $pdo; 

        try
        {
            $pdo->beginTransaction();

            $this->remove_money($card_num, $amount);

            $interest_rate = self::INTEREST_RATE1;
            if($amount >= 500000) {$interest_rate = self::INTEREST_RATE2;}
            elseif($amount >= 2000000) {$interest_rate = self::INTEREST_RATE3;}

            $sql = "INSERT INTO deposit(account_id, amount, currency, deposit_type, period_months, interest_rate)
                    VALUES (:account_id, :amount, :currency, :deposit_type, :period_months, :interest_rate)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':account_id' => $account_id,
                ':amount' => $amount,
                ':currency' => $currency,
                ':deposit_type' => $deposit_type,
                ':period_months' => $period_months,
                ':interest_rate' => $interest_rate
            ]);

            $this->last_deposit_id = $pdo->lastInsertId();

            $pdo->commit();
            return "created successfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to create a saving deposite";
        }
    }

    public function read_all_saving_deposit()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM deposit ORDER BY start_date ASC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all saving deposite, <br>".$e->getMessage();
        }
    }

    public function read_saving_deposit($deposit_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM deposit WHERE deposit_id = :deposit_id";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":deposit_id" => $deposit_id
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return "failed to read saving deposit, <br>".$e->getMessage();
        }
    }

    // to show all the deposite of the user
    public function read_customer_saving_deposit($account_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT *
                    FROM deposit 
                    WHERE account_id = :account_id
                    ORDER BY start_date ASC";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':account_id' => $account_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read customer saving deposit, <br>".$e->getMessage();
        }
    }

    public function search_saving_deposit($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM deposit
                    WHERE deposit_id LIKE :s1 OR currency LIKE :s2
                    OR deposit_type LIKE :s3 OR start_date LIKE :s4
                    OR end_date LIKE :s5
                    ORDER BY start_date ASC";

            $stmt = $pdo->prepare($sql);
            
            $like = "%{$search}%";

            $stmt->execute([
                ':s1' => $like,
                ':s2' => $like,
                ':s3' => $like,
                ':s4' => $like,
                ':s5' => $like
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);  
        }
        catch(PDOException $e)
        {
            return "failed to search for saving depsit, <br>".$e->getMessage();
        }
    }

    public function update_saving_deposit($deposit_id, $amount, $deposit_type, $status, $period_months)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE deposit SET amount = :amount, deposit_type = :deposit_type,
                    status = :status, period_months = :period_months
                    WHERE deposit_id = :deposit_id";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":deposit_id" => $deposit_id,
                ":amount" => $amount,
                ":deposit_type" => $deposit_type,
                ":status" => $status,
                ":period_months" => $period_months
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update saving depsit, <br>".$e->getMessage();
        }
    }

    public function delete_saving_deposit($deposit_id)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("SELECT c.card_num, d.amount
                                    FROM deposit d
                                    JOIN card c ON c.account_id = d.account_id
                                    WHERE d.deposit_id = :deposit_id
                                    ORDER BY c.balance DESC
                                    LIMIT 1
                                    ");

            $stmt->execute([":deposit_id" => $deposit_id]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $this->add_money($result["card_num"], $result["amount"]);
            } else{
                throw new PDOException;
            }
            
            $stmt = $pdo->prepare("DELETE FROM deposit WHERE deposit_id = :deposit_id");

            $stmt->execute([
                ":deposit_id" => $deposit_id
            ]);

            return "deleted successfully";
        }
        catch(PDOException $e)
        {
            return "failed to delete saving deposit, <br>".$e->getMessage();
        }
    }

    public function withdraw_saving_deposit($deposit_id, $card_num)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT end_date FROM deposit WHERE deposit_id = :deposit_id");
            $stmt->execute([':deposit_id' => $deposit_id]);
            $endDate = $stmt->fetchColumn();

            if($endDate !== false && $endDate <= date('Y-m-d H:i:s'))
            {
                $stmt = $pdo->prepare("SELECT end_date FROM deposit WHERE deposit_id = :deposit_id");
                $stmt->execute([':deposit_id' => $deposit_id]);
                $row = $stmt->fetchColumn();
                $amount = $row['amount'] + ($row['amount'] * $row['interest_rate']);

                $this->add_money($card_num, $amount);

                $sql = "UPDATE deposit SET status = 'withdrawn' WHERE deposit_id = :deposit_id";
                $stmt = $pdo->prepare($sql);

                $stmt->execute([
                    "deposit_id" => $deposit_id
                ]);
            }
            else
            {
                $pdo->commit();
                return "Deposit is still active";
            }

            $pdo->commit();
            return "withdrawn successfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to withdraw saving deposit, <br>".$e->getMessage();
        }
    }

    public function read_last_saving_deposit()
    {
        $this->read_deposit($this->last_deposit_id);
    }
}