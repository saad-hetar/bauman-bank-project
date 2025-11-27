<?php 

require('db.php');

trait passport 
{
    private $last_passport_id;


    public function create_passport($last_name, $first_name, $middle_name, $passport_num,
                                    $passport_series, $nationality, $passport_type, $birth_date, 
                                    $birth_place, $gender, $issue_date, $expire_date, 
                                    $assuing_authority, $owner)
    {
        global $pdo;
        try
        {
            $sql = "INSERT INTO passport(last_name, first_name, middle_name, passport_num,
                    passport_series, nationality, passport_type, birth_date, 
                    birth_place, gender, issue_date, expire_date, 
                    assuing_authority, owner)
                    VALUES (:last_name, :first_name, :middle_name, :passport_num,
                            :passport_series, :nationality, :passport_type, :birth_date, 
                            :birth_place, :gender, :issue_date, :expire_date, 
                            :assuing_authority, :owner)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':last_name' => $last_name,
                ':first_name' => $first_name,
                ':middle_name' => $middle_name,
                ':passport_num' => $passport_num,
                ':passport_series' => $passport_series,
                ':nationality' => $nationality,
                ':passport_type' => $passport_type,
                ':birth_date' => $birth_date,
                ':birth_place' => $birth_place,
                ':gender' => $gender,
                ':issue_date' => $issue_date,
                ':expire_date' => $expire_date,
                ':assuing_authority' => $assuing_authority,
                ':owner' => $owner
            ]);

            $this->last_passport_id = $pdo->lastInsertId();

            return "passport created successfully";
        }
        catch(PDOException $e)
        {
            return "failed to create a passport, <br>".$e->getMessage();
        }
    }

    public function read_all_passport()
    {
        global $pdo;

        try
        {
            $stmt = $pdo->query("SELECT * FROM passport");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all passports, <br>".$e->getMessage();
        }
    }

    public function read_passport($passport_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM employee WHERE passport_id = :passport_id";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":passport_id" => $passport_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read the passport, <br>".$e->getMessage();
        }
    }

    public function search_passport($search)
    {
        if(!isset($search)) { return; }
        
        global $pdo;

        try
        {
            $sql = "SELECT * FROM passport WHERE passport_num LIKE :search
                    OR passport_id LIKE :search OR last_name LIKE :search
                    OR first_name LIKE :search OR owner LIKE :search";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to search for passport, <br>".$e->getMessage();
        }
    }

    public function update_passport($passport_id, $last_name, $first_name, $middle_name, $passport_num,
                                    $passport_series, $nationality, $passport_type, $birth_date, 
                                    $birth_place, $gender, $issue_date, $expire_date, 
                                    $assuing_authority, $owner)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE passport SET last_name = :last_name, first_name = :first_name, 
                    middle_name = :middle_name, passport_num = :passport_num, lore = :lore,
                    passport_series = :passport_series, nationality = :nationality, gender = :gender,
                    passport_type = :passport_type, birth_date = :birth_date, birth_place = :birth_place,
                    issue_date = :issue_date, expire_date = :expire_date, owner = :owner
                    assuing_authority = :assuing_authority,
                    WHERE passport_id = :passport_id";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":passport_id" => $passport_id,
                ":last_name" => $last_name,
                ":first_name" => $first_name,
                ":middle_name" => $middle_name,
                ":passport_num" => $passport_num,
                ":passport_series" => $passport_series,
                ":nationality" => $nationality,
                ":passport_type" => $passport_type,
                ":birth_date" => $birth_date,
                ":birth_place" => $birth_place,
                ":gender" => $gender,
                ":issue_date" => $issue_date,
                ":expire_date" => $expire_date,
                ":assuing_authority" => $assuing_authority,
                ":owner" => $owner,
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update passport, <br>".$e->getMessage();
        }
    }

    private function delete_passport($passport_id)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("DELETE FROM passport WHERE passport_id = :passport_id");

            $stmt->execute([
                "passport_id" => $passport_id
            ]);

            return "deleted successfully";
        }
        catch(PDOException $e)
        {
            throw new PDOException("error in deleting passport " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function get_last_passport()
    {
        $this->read_passport($this->last_passport_id);
    }
}