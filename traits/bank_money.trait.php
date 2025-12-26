<?php 

trait bank_money
{
    public function create_capital($capital_amount, $currency)
    {
        global $pdo;
        
        try
        {
            $sql = "INSERT INTO capital(capital_amount, currency)
                    VALUES (:capital_amount, :currency)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':capital_amount' => $capital_amount,
                ':currency' => $currency
            ]);

            return "capital created successfully";
        }
        catch(PDOException $e)
        {
            return "failed to create capital, <br>".$e->getMessage();
        }
    }
    
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
                    WHERE currency LIKE :s1 OR cacula_date LIKE :s2
                    OR cacula_id LIKE :s3";

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

    public function update_capital($capital_id, $capital_amount, $currency)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE capital
                    SET capital_amount = :capital_amount, currency = :currency
                    WHERE capital_id = :capital_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':capital_amount' => $capital_amount,
                ':currency' => $currency,
                ':capital_id' => $capital_id
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update captial, <br>".$e->getMessage();
        }
    }
}