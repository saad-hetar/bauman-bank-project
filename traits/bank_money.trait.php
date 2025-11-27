<?php 

trait bank_money
{
    public function read_bank_money()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM bank_money");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read bank's money, <br>".$e->getMessage();
        }
    }

    public function search_bank_money($search)
    {
        if (!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM bank_money
                    WHERE currency LIKE :search OR cacula_date LIKE :search
                    OR cacula_id LIKE :search";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to search in bank's money, <br>".$e->getMessage();
        }
    }

    public function read_capital()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM capital");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read capital, <br>".$e->getMessage();
        }
    }

    public function update_capital($capital_amount, $currency)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE capital
                    SET capital_amount = :capital_amount, currency = :currency
                    WHERE branch_id :branch_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':capital_amount' => $capital_amount,
                ':branch_name' => $currency,
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update captial, <br>".$e->getMessage();
        }
    }
}