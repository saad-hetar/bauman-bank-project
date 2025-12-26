<?php 

trait branch
{
    public function create_branch($branch_name, $manager_id, $address)
    {
        global $pdo;

        try
        {
            $sql = "INSERT INTO branch(branch_name, address, manager_id)
                    VALUES (:branch_name, :address, :manager_id)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':branch_name' => $branch_name,
                ':address' => $address,
                ':manager_id' => $manager_id
            ]);

            return "branch created successfully";
        }
        catch(PDOException $e)
        {
            return "failed to create branch, <br>".$e->getMessage();
        }
    }

    public function read_all_branch()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM branch ORDER BY branch_name ASC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all branch, <br>".$e->getMessage();
        }
    }

    public function read_branch($branch_id)
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT * FROM branch WHERE branch_id = :branch_id ");

            $stmt->execute([
                ':branch_id' => $branch_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read branch, <br>".$e->getMessage();
        }
    }

    public function search_branch($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM branch
                    WHERE branch_id LIKE :s1 OR branch_name LIKE :s2
                    OR manager_id LIKE :s3 OR address LIKE :s4
                    ORDER BY branch_name ASC";

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
            return "failed to search branch, <br>".$e->getMessage();
        }
    }

    public function update_branch($branch_id, $branch_name, $address, $manager_id)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE branch
                    SET branch_name = :branch_name, address = :address, manager_id = :manager_id
                    WHERE branch_id = :branch_id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':branch_name' => $branch_name,
                ':address' => $address,
                ':manager_id' => $manager_id,
                ':branch_id' => $branch_id
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update branch, <br>".$e->getMessage();
        }
    }

    public function delete_branch($branch_id)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("DELETE FROM branch WHERE branch_id = :branch_id");

            $stmt->execute([
                "branch_id" => $branch_id
            ]);

            return "deleted successfully";
        }
        catch(PDOException $e)
        {
            return "failed to delete branch, <br>".$e->getMessage();
        }
    }
}