<?php 

trait customer_trait
{
    private $customer_user_id;
    private $last_customer_id;
    use account;
    use passport;
    use login;

    private function get_customer_user_id($id)
    {
        $this->customer_user_id = $id;
    }

    public function create_customer($address, $phone, $email, $account_type, $currency)
    {
        global $pdo;

        try
        {
            $pdo->beginTransaction();

            $sql = "INSERT INTO customer(passport_id, address, phone, email, created_by)
                    VALUES (:passport_id, :address, :phone, :email, :created_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':passport_id' => $this->last_passport_id,
                ':address' => $address,
                ':phone' => $phone,
                ':email'  => $email,
                ':created_by' => $this->customer_user_id
            ]);

            $this->last_customer_id = $pdo->lastInsertId();
            $this->create_account($this->last_customer_id, $account_type, $currency, true);
            $this->create_login($this->last_customer_id, "customer");

            $pdo->commit();
            return "customer have been created successfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to create a customer, <br>".$e->getMessage();
        }
    }

    public function read_all_customer()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT customer.customer_id, passport.passport_id, passport.last_name, 
                                passport.first_name, passport.middle_name, passport.gender, customer.adress,  
                                customer.email, customer.phone, customer.created_by, customer.created_at,
                                customer.updated_by, customer.updated_by                       
                                FROM customer JOIN passport
                                ON customer.passport_id = passport.passport_id
                                ORDER BY passport.last_name ASC");

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read all customers, <br>".$e->getMessage();
        }
    }

    public function read_customer($customer_id, $rethraw = false)
    {
        global $pdo;

        try
        {
            $sql = "SELECT customer.customer_id, passport.passport_id, passport.last_name, 
                        passport.first_name, passport.middle_name, passport.gender, customer.adress,  
                        customer.email, customer.phone, customer.created_by, customer.created_at,
                        customer.updated_by, customer.updated_by                       
                        FROM customer JOIN passport
                        ON passport.passport_id = (
                            SELECT passport_id FROM customer WHERE customer_id = :customer_id
                        )";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":customer_id" => $customer_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            if($rethraw)
            {
                throw new PDOException("error in read customer " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            else
            {
                return "failed to read customer, <br>".$e->getMessage();
            }
        }
    }

    public function search_customer($search)
    {
        if(!isset($search)) { return; }

        global $pdo;

        try
        {
            $sql = "SELECT customer.customer_id, passport.passport_id, passport.last_name, 
                    passport.first_name, passport.middle_name, passport.gender, customer.adress,  
                    customer.email, customer.phone, customer.created_by, customer.created_at,
                    customer.updated_by, customer.updated_by 
                    FROM customer JOIN passport ON customer.passport_id = passport.passport_id
                    WHERE customer.customer_id LIKE :search OR passport.passport_id LIKE :search
                    OR passport.last_name LIKE :search OR passport.first_name LIKE :search
                    OR customer.phone LIKE :search
                    ORDER BY passport.last_name ASC";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":search" => "%".$search."%"
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to search for customer, <br>".$e->getMessage();
        }
    }

    public function update_customer($customer_id, $address, $phone, $email)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE employee SET address = :address, email = :email, phone = :phone,
                    updated_by = :updated_by 
                    WHERE customer_id = :customer_id";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":customer_id" => $customer_id,
                ":address" => $address,
                ":email" => $email,
                ":phone" => $phone,
                ":updated_by" => $this->customer_user_id
            ]);

            return "updated successfully";
        }
        catch(PDOException $e)
        {
            return "failed to update customer, <br>".$e->getMessage();
        }
    }

    public function delete_customer($passport_id, $login_id)
    {
        global $pdo;
        try
        {
            $pdo->beginTransaction();

            $this->delete_passport($passport_id);
            $this->delete_login($login_id);

            $pdo->commit();
            return "customer deleted successfully";
        }
        catch(PDOException $e)
        {
            $pdo->rollBack();
            return "failed to get delete customer, <br>".$e->getMessage();
        }
    }

    public function get_last_customer()
    {
        global $pdo;

       try
        {
            $pdo->beginTransaction();

            $customer_info = $this->read_customer($this->last_customer_id, true);
            $account_info = $this->read_account($this->last_account_id, true);
            $card_info = $this->read_card($this->last_card_num, true);
            $login_info = $this->read_login($this->last_login_id);

            $pdo->commit();

            return [
                'customer' => $customer_info,
                'account' => $account_info,
                'card'    => $card_info,
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