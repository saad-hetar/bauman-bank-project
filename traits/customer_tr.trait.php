<?php 
require_once __DIR__ . '/account.trait.php';
require_once __DIR__ . '/passport.trait.php';
require_once __DIR__ . '/login.trait.php';
require_once __DIR__ . '/card.trait.php';

trait customer_tr
{
    private $customer_user_id;
    public $last_customer_id;
    use account 
    {
        card::read_customer_cards insteadof account; // choose the card version
    }
    use passport;
    use login;
    use card;

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
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return "failed to create a customer, <br>".$e->getMessage();
        }
    }

    public function read_all_customer()
    {
        global $pdo;
        
        try
        {
            $stmt = $pdo->query("SELECT customer.customer_id, passport.passport_id, passport.last_name, 
                                passport.first_name, passport.middle_name, passport.gender, customer.address,  
                                customer.email, customer.phone, customer.created_by, customer.created_at,
                                customer.updated_by, login.login_id, login.password_hash                       
                                FROM customer 
                                JOIN passport ON customer.passport_id = passport.passport_id
                                JOIN login ON login.user_id = customer.customer_id AND login.role = 'customer'
                                ORDER BY customer.created_at DESC");

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
            $sql = "SELECT c.customer_id, p.passport_id, p.last_name, p.first_name, p.middle_name, p.gender, 
                           c.address, c.email, c.phone, c.created_by, c.created_at, c.updated_by
                    FROM customer c
                    JOIN passport p ON c.passport_id = p.passport_id
                    WHERE c.customer_id = :customer_id";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":customer_id" => $customer_id
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
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
                    passport.first_name, passport.middle_name, passport.gender, customer.address,  
                    customer.email, customer.phone, customer.created_by, customer.created_at,
                    customer.updated_by, customer.updated_by, login.login_id 
                    FROM customer JOIN passport ON customer.passport_id = passport.passport_id
                    JOIN login ON login.user_id = customer.customer_id AND login.role = 'customer'
                    WHERE customer.customer_id LIKE :s1 OR passport.passport_id LIKE :s2
                    OR passport.last_name LIKE :s3 OR passport.first_name LIKE :s4
                    OR customer.phone LIKE :s5
                    ORDER BY passport.last_name ASC";

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
            return "failed to search for customer, <br>".$e->getMessage();
        }
    }

    public function update_customer($customer_id, $address, $phone, $email)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE customer SET address = :address, email = :email, phone = :phone,
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
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return "failed to get last customer, <br>".$e->getMessage();
        }
    }
}