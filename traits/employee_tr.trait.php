<?php 
require_once('passport.trait.php');
require_once('login.trait.php');

trait employee_tr
{
    private $user_id;
    public $last_emp_id;
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
                                employee.created_by, employee.updated_by, employee.quit_date, login.login_id, login.role                        
                                FROM employee 
                                JOIN passport ON employee.passport_id = passport.passport_id
                                JOIN login ON login.user_id = employee.emp_id AND login.role IN ('admin', 'employee')");

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
            $sql = "SELECT employee.emp_id, 
                        employee.passport_id, 
                        employee.branch_id,
                        employee.email,
                        employee.phone,
                        employee.lore,
                        employee.hire_date,
                        employee.quit_date,
                        employee.created_by,
                        employee.updated_by,

                        passport.last_name, 
                        passport.first_name, 
                        passport.middle_name,
                        passport.gender,
                        passport.nationality,
                        passport.passport_num,
                        passport.passport_series,
                        passport.passport_type,
                        passport.birth_date,
                        passport.birth_place,
                        passport.issue_date,
                        passport.expire_date,
                        passport.assuing_authority,

                        login.login_id,
                        login.role

                    FROM employee 
                    JOIN passport ON passport.passport_id = employee.passport_id
                    JOIN login ON login.user_id = employee.emp_id AND login.role IN ('admin', 'employee')
                    WHERE employee.emp_id = :emp_id";

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
                    employee.created_by, employee.updated_by, employee.quit_date, login.login_id, login.role 
                    FROM employee 
                    JOIN passport ON employee.passport_id = passport.passport_id
                    JOIN login ON login.user_id = employee.emp_id AND login.role IN ('admin', 'employee')
                    WHERE employee.emp_id LIKE :s1 OR passport.passport_id LIKE :s2
                    OR passport.last_name LIKE :s3 OR passport.first_name LIKE :s4
                    OR employee.phone LIKE :s5";

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