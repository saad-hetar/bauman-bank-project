<?php

trait central_bank_customer
{
    public function read_all_central_bank_customer()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT 
                                cbc.customer_id, 
                                cbc.bank_id, 
                                cbc.last_name, 
                                cbc.first_name, 
                                cbc.middle_name, 
                                cbc.phone, 
                                cbc.balance AS customer_balance, 
                                cbc.card_num,
                                ba.bank_id AS bank_account_id,
                                ba.bank_name, 
                                ba.address AS bank_address, 
                                ba.currency, 
                                ba.balance AS bank_balance                       
                                FROM central_bank_customer cbc 
                                LEFT JOIN bank_account ba ON cbc.bank_id = ba.bank_id
                                ORDER BY cbc.customer_id DESC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all central bank customers, <br>".$e->getMessage();
        }
    }

    public function search_central_bank_customer($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT 
                    cbc.customer_id, 
                    cbc.bank_id, 
                    cbc.last_name, 
                    cbc.first_name, 
                    cbc.middle_name, 
                    cbc.phone, 
                    cbc.balance AS customer_balance, 
                    cbc.card_num,
                    ba.bank_id AS bank_account_id,
                    ba.bank_name, 
                    ba.address AS bank_address, 
                    ba.currency, 
                    ba.balance AS bank_balance                       
                    FROM central_bank_customer cbc 
                    LEFT JOIN bank_account ba ON cbc.bank_id = ba.bank_id
                    WHERE cbc.customer_id LIKE :s1 
                    OR cbc.last_name LIKE :s2 
                    OR cbc.first_name LIKE :s3 
                    OR cbc.middle_name LIKE :s4
                    OR cbc.phone LIKE :s5
                    OR cbc.card_num LIKE :s6
                    OR ba.bank_name LIKE :s7
                    OR ba.address LIKE :s8
                    ORDER BY cbc.last_name ASC";

            $stmt = $pdo->prepare($sql);
            
            $like = "%{$search}%";

            $stmt->execute([
                ':s1' => $like,
                ':s2' => $like,
                ':s3' => $like,
                ':s4' => $like,
                ':s5' => $like,
                ':s6' => $like,
                ':s7' => $like,
                ':s8' => $like
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return "failed to search for central bank customer, <br>".$e->getMessage();
        }
    }
}