<?php 
require('main/db.php');

class admin
{
    private $employee_id;
    use passport;

    use transaction
    {
        cancel_payments as private;
    }

    use transfer
    {
        cancel_internal_transfer as private;
        cancel_external_transfer as private;
    }

    use customer_trait
    {
        delete_customer as private;
    }
    
    use card
    {
        read_customer_cards as private;
        read_all_card as private;
        search_card as private;
        delete_card as private;

    }

    use account
    {
        read_customer_accounts as private;
        delete_account as private;
    }

    use saving_deposit
    {
        read_customer_saving_deposit as private;
        delete_saving_deposit as private;
        withdraw_saving_deposit as private;

    }

    use login
    {
        create_login as private;
        read_all_login as private;
        search_login as private;
        get_last_login as private;
    }


    function __construct($id)
    {
        $this->employee_id = $id;

        $this->get_employee_user_id($id);
        $this->get_customer_user_id($id);
        $this->get_account_user_id($id);
        $this->get_transacted_user_id($id);
        $this->get_transfer_user_id($id);
    }
}