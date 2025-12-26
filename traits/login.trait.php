<?php

trait login
{
    public $last_login_id;
    public $last_password;

    private function create_login($user_id, $role)
    {
        global $pdo;

        try
        {
            // Generate a unique login_id
            do 
            {
                $login_id = strtoupper(bin2hex(random_bytes(4))); // 8 chars
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM login WHERE login_id = :login_id");
                $stmt->execute([':login_id' => $login_id]);
                $count = $stmt->fetchColumn();
            } while ($count > 0);

            // Generate a strong random password (letters, numbers)
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
            $password = '';

            for ($i = 0; $i < 9; $i++) {
                $password .= $characters[random_int(0, strlen($characters) - 1)];
            }

            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO login (login_id, user_id, role, password_hash) 
                    VALUES (:login_id, :user_id, :role, :password_hash)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':login_id' => $login_id,
                ':user_id' => $user_id, 
                ':role' => $role, 
                ':password_hash' => $password_hash
            ]);

            $this->last_login_id = $pdo->lastInsertId();
            $this->last_password = $password;
        }
        catch(PDOException $e)
        {
            throw new PDOException("Error in create login: " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function read_all_login()
    {
        global $pdo;
        
        try
        {
           $stmt = $pdo->query("SELECT * FROM login ORDER BY role DESC");

            return $stmt->fetchAll(); 
        }
        catch(PDOException $e)
        {
            return "failed to read all logins, <br>".$e->getMessage();
        }
    }

    public function read_login($login_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT * FROM login WHERE login_id = :login_id";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":login_id" => $login_id
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return "failed to read the login, <br>".$e->getMessage();
        }
    }

    public function search_login($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT * FROM login
                    WHERE login_id LIKE :s1 OR user_id LIKE :s2";

            $stmt = $pdo->prepare($sql);
            
            $like = "%{$search}%";

            $stmt->execute([
                ':s1' => $like,
                ':s2' => $like
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            return "failed to search for login, <br>".$e->getMessage();
        }
    }

    // just to update the your passwords and not the logins of the other users
    public function update_passwort($login_id, $password_hash)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE login SET password_hash = :password_hash
                    WHERE login_id = :login_id";
            
            $stmt = $pdo->prepare($sql);

            $password_hash = password_hash($password_hash, PASSWORD_DEFAULT);

            $stmt->execute([
                ":login_id" => $login_id,
                ":password_hash" => $password_hash
            ]);

            return "password updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update password, <br>".$e->getMessage();
        }
    }

    private function delete_login($login_id)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("DELETE FROM login WHERE login_id = :login_id");

            $stmt->execute([
                ":login_id" => $login_id
            ]);

            return "deleted successfully";
        }
        catch(PDOException $e)
        {
            throw new PDOException("error in delete login " . $e->getMessage(), (int)$e->getCode(), $e);
        }
    }
}