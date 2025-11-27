<?php 
require('main/db.php');

class admin
{
    private $admin_id;
    use passport;
    use employee;
    use customer_trait;
    use branch_expenses;
    use transaction;
    use bank_money;
    use transfer;
    use branch;
    use login;

    use card
    {
        read_customer_cards as private;
    }
    
    use account
    {
        read_customer_accounts as private;
    }

    use saving_deposit
    {
        read_customer_saving_deposit as private;
    }


    function __construct($id)
    {
        $this->admin_id = $id;

        $this->get_employee_user_id($id);
        $this->get_customer_user_id($id);
        $this->get_account_user_id($id);
        $this->get_transacted_user_id($id);
        $this->get_expenses_user_id($id);
        $this->get_transfer_user_id($id);
    }
}