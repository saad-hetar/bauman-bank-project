<?php 

trait employee
{
    private $user_id;
    private $last_emp_id;
    use passport;
    use login;

    private function get_employee_user_id($id)
    {
        $this->user_id = $id;
    }


    public function create_emp($branch_id, $email, $phone, $lore)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $sql = "INSERT INTO employee(passport_id, branch_id, email, phone, lore, created_by)
                    VALUES (:passport_id, :branch_id, :email, :phone, :lore, :created_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':passport_id' => $this->last_passport_id,
                ':branch_id'   => $branch_id,
                ':email'       => $email,
                ':phone'       => $phone,
                ':lore'        => $lore,
                ':created_by'  => $this->user_id
            ]);

            $this->last_emp_id = $pdo->lastInsertId();
            $this->create_login($this->last_emp_id, $lore);

            $pdo->commit();
            return "created successfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to create a customer, <br>".$e->getMessage();
        }
    }

    public function read_all_emp()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT employee.emp_id, passport.passport_id, passport.last_name, 
                                passport.first_name, passport.middle_name, passport.gender, employee.lore,  
                                employee.email, employee.phone, employee.branch_id, employee.hire_date,
                                employee.created_by, employee.updated_by, employee.quit_date                        
                                FROM employee JOIN passport
                                ON employee.passport_id = passport.passport_id");

            return $stmt->fetchAll(); 
        }
        catch(PDOException $e)
        {
            return "failed to , <br>".$e->getMessage();
        }
    }

    public function read_emp($emp_id)
    {
        global $pdo;

        try
        {
            $sql = "SELECT employee.emp_id, passport.passport_id, passport.last_name, 
                        passport.first_name, passport.middle_name, passport.gender, employee.lore,  
                        employee.email, employee.phone, employee.branch_id, employee.hire_date,
                        employee.created_by, employee.updated_by, employee.quit_date                        
                        FROM employee JOIN passport
                        ON passport.passport_id = (
                            SELECT passport_id FROM employee WHERE emp_id = :emp_id
                        )";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":emp_id" => $emp_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read employee, <br>".$e->getMessage();
        }
    }

    public function search_emp($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT employee.emp_id, passport.passport_id, passport.last_name, 
                    passport.first_name, passport.middle_name, passport.gender, employee.lore,  
                    employee.email, employee.phone, employee.branch_id, employee.hire_date,
                    employee.created_by, employee.updated_by, employee.quit_date 
                    FROM employee JOIN passport ON employee.passport_id = passport.passport_id
                    WHERE employee.emp_id LIKE :search OR passport.passport_id LIKE :search
                    OR passport.last_name LIKE :search OR passport.first_name LIKE :search
                    OR employee.phone LIKE :search";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to search for employee, <br>".$e->getMessage();
        }
    }


    public function update_emp($emp_id, $branch_id, $email, $phone, $lore, $quit_date)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE employee SET branch_id = :branch_id, email = :email, phone = :phone,
                    lore = :lore, quit_date = :quit_date, updated_by = :admin_id 
                    WHERE emp_id = :emp_id";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":emp_id" => $emp_id,
                ":branch_id" => $branch_id,
                ":email" => $email,
                ":phone" => $phone,
                ":lore" => $lore,
                ":quit_date" => $quit_date,
                ":admin_id" => $this->user_id
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update employee, <br>".$e->getMessage();
        }
    }

    public function delete_emp($passport_id, $login_id)
    {
        global $pdo;
        try
        {
            $pdo->beginTransaction();

            $this->delete_passport($passport_id);
            $this->delete_login($login_id);

            $pdo->commit();
            return "employee deleted successfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to get delete employee, <br>".$e->getMessage();
        }
    }

    public function get_last_emp()
    {
        global $pdo;

       try
        {
            $pdo->beginTransaction();

            $emp_info = $this->read_emp($this->last_emp_id);
            $login_info = $this->read_login($this->last_login_id);

            $pdo->commit();

            return [
                'employee' => $emp_info,
                'login' => $login_info
            ];
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to get last customer, <br>".$e->getMessage();
        }
    }
}