<?php 
require_once __DIR__ . '/card.trait.php';

trait account
{
    private $account_user_id;
    public $last_account_id;
    use card;

    private function get_account_user_id($id)
    {
        $this->account_user_id = $id;
    }


    public function create_account($customer_id, $account_type, $currency, $rethraw = false)
    {
        global $pdo;

        try 
        {
            if (!$rethraw) {
                $pdo->beginTransaction();
            }

            $sql = "INSERT INTO account(customer_id, account_type, currency, created_by)
                    VALUES (:customer_id, :account_type, :currency, :created_by)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':customer_id' => $customer_id,
                ':account_type' => $account_type,
                ':currency' => $currency,
                ':created_by' => $this->account_user_id
            ]);

            $this->last_account_id = $pdo->lastInsertId();
            $this->create_card($this->last_account_id, true);

            if (!$rethraw) {
            $pdo->commit();
                return "account have been created successfully";
            } else {
                // when $rethraw = true (called from create_customer),
                // we do NOT commit here – outer create_customer() will commit
                return;
            }
        } 
        catch (PDOException $e) 
        {
            if (!$rethraw && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            if($rethraw)
            {
                throw new PDOException("Error in create_account: " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            else
            {
                return "failed to create an account, <br>".$e->getMessage();
            }
        }
    }

    public function read_all_account()
    {
        try 
        {
            global $pdo;
            
            $stmt = $pdo->query("SELECT customer.customer_id, passport.passport_id, account.account_id, 
                                passport.last_name, passport.first_name,    
                                customer.email, customer.phone, customer.created_by,
                                customer.created_at, customer.updated_by, account.account_type,
                                account.account_status, account.currency, account.created_by, account.updated_by,
                                account.created_at, account.closed_at                     
                                FROM account 
                                JOIN customer ON customer.customer_id = account.customer_id 
                                JOIN passport ON passport.passport_id = customer.passport_id
                                ORDER BY account.created_at DESC");

            return $stmt->fetchAll();
        } 
        catch (PDOException $e) 
        {
            return "failed to read all accounts, <br>".$e->getMessage();
        }
    }

    public function read_account($account_id, $rethraw = false)
    {
        global $pdo;

        try
        {
            $sql = "SELECT customer.customer_id, passport.passport_id, account.account_id, 
                    passport.last_name,passport.first_name, passport.middle_name, passport.gender,   
                    customer.address, customer.email, customer.phone, customer.created_by, customer.created_at,
                    customer.created_at, customer.updated_by, customer.updated_by, account.account_type,
                    account.account_status, account.currency, account.created_by, account.updated_by,
                    account.created_at, account.closed_at                     
                    FROM account 
                    JOIN customer ON customer.customer_id = account.customer_id 
                    JOIN passport ON passport.passport_id = customer.passport_id
                    WHERE account.account_id = :account_id
                    ORDER BY passport.last_name ASC";

            $stmt = $pdo->prepare($sql);
            
            $stmt->execute([
                ":account_id" => $account_id
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        catch(PDOException $e)
        {
            if($rethraw)
            {
                throw new PDOException("error in read account " . $e->getMessage(), (int)$e->getCode(), $e);
            }
            else
            {
                return "failed to create a account, <br>".$e->getMessage();
            }
        }
    }

    // to show all the accounts of the user
    public function read_customer_accounts()
    {
        global $pdo;
       
        try
        {
            $sql = "SELECT account_id, currency 
                    FROM account 
                    WHERE customer_id = :customer_id
                    ORDER BY created_at ASC";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':customer_id' => $this->account_user_id
            ]);

            return $stmt->fetchAll();
        }
        catch(PDOException $e)
        {
            return "failed to read customer accounts, <br>".$e->getMessage();
        }
    }

    public function search_account($search)
    {
        if (!isset($search)) { 
            return; 
        }

        global $pdo;

        try {
            $sql = "SELECT 
                        customer.customer_id, 
                        passport.passport_id, 
                        account.account_id, 
                        passport.last_name,
                        passport.first_name, 
                        passport.middle_name, 
                        passport.gender,   
                        customer.address, 
                        customer.email, 
                        customer.phone, 
                        customer.created_by, 
                        customer.created_at,
                        customer.updated_by, 
                        account.account_type,
                        account.account_status, 
                        account.currency, 
                        account.created_by, 
                        account.updated_by,
                        account.created_at, 
                        account.closed_at
                    FROM account 
                    JOIN customer ON customer.customer_id = account.customer_id 
                    JOIN passport ON passport.passport_id = customer.passport_id
                    WHERE customer.customer_id   LIKE :s1
                    OR passport.passport_id  LIKE :s2
                    OR passport.last_name    LIKE :s3
                    OR passport.first_name   LIKE :s4
                    OR customer.phone        LIKE :s5
                    OR account.account_id    LIKE :s6
                    ORDER BY passport.last_name ASC";

            $stmt = $pdo->prepare($sql);

            $like = "%{$search}%";

            $stmt->execute([
                ':s1' => $like,
                ':s2' => $like,
                ':s3' => $like,
                ':s4' => $like,
                ':s5' => $like,
                ':s6' => $like
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } 
        catch (PDOException $e) {
            return "failed to search for account, <br>" . $e->getMessage();
        }
    }


    public function update_account($account_id, $account_type, $account_status, $closed_at)
    {
        global $pdo;

        try
        {
            $sql = "UPDATE account SET account_type = :account_type, account_status = :account_status, 
                    closed_at = :closed_at, updated_by = :updated_by 
                    WHERE account_id = :account_id";
            
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ":account_id" => $account_id,
                ":account_type" => $account_type,
                ":account_status" => $account_status,
                ":closed_at" => $closed_at,
                ":updated_by" => $this->account_user_id
            ]);

            return "updated successfully!";
        }
        catch(PDOException $e)
        {
            return "failed to update account, <br>".$e->getMessage();
        }
    }

    private function delete_account($account_id)
    {
        global $pdo;

        try
        {
            $stmt = $pdo->prepare("DELETE FROM account WHERE account_id = :account_id");

            $stmt->execute([
                "account_id" => $account_id
            ]);

            return "deleted successfully!";
        }
        catch(PDOException $e)
        {
            return "failed to delete account, <br>".$e->getMessage();
        }
    }

    public function get_last_account()
    {
        $this->read_account($this->last_account_id);
    }
}