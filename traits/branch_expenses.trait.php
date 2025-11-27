<?php 

trait branch_expenses
{
    private $expenses_user_id;

    private function get_expenses_user_id($id)
    {
        $this->expenses_user_id = $id;
    }

    public function create_expenses($branch_id, $expenses_type, $cost)
    {
        global $pdo;

        try
        {
            $sql = "INSERT INTO branch(branch_id, expenses_type, paid_by, cost)
                    VALUES (:branch_id, :expenses_type, :paid_by, :cost)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':branch_id' => $branch_id,
                ':expenses_type' => $expenses_type,
                ':paid_by' => $this->expenses_user_id,
                ':cost' => $cost
            ]);

            return "created successfully";
        }
        catch(PDOException $e)
        {
            return "failed to create expenses, <br>".$e->getMessage();
        }
    }

    public function read_all_expenses()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM branch_expenses ORDER BY paid_at ASC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all expenses, <br>".$e->getMessage();
        }
    }

    public function read_expenses($expenses_id)
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM branch_expenses WHERE expenses_id = :expenses_id");

            $stmt->execute([
                ':expenses_id' => $expenses_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read expenses, <br>".$e->getMessage();
        }
    }

    public function search_expenses($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM branch_expenses
                WHERE branch_id LIKE :search OR expenses_id LIKE :search
                OR paid_by LIKE :search OR cost LIKE :search
                ORDER BY paid_at ASC";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to search expenses, <br>".$e->getMessage();
        }
    }

    public function update_expenses($branch_id, $expenses_type, $cost)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE branch_expenses
                    SET branch_id = :branch_id, expenses_type = :expenses_type,
                    cost = :cost, updated_by = :updated_by
                    WHERE branch_id :branch_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':branch_id' => $branch_id,
                ':expenses_type' => $expenses_type,
                'cost' => $cost,
                'updated_by' => $this->expenses_user_id
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update expenses, <br>".$e->getMessage();
        }
    }

    public function delete_expenses($expenses_id)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("DELETE FROM branch_expenses WHERE expenses_id = :expenses_id");

            $stmt->execute([
                "expenses_id" => $expenses_id
            ]);

            return "deleted successfully";
        }
        catch(PDOException $e)
        {
            return "failed to , <br>".$e->getMessage();
        }
    }

    public function get_last_expenses()
    {
        $this->read_expenses($this->last_expenses_id);
    }
}