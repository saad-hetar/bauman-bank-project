<?php 
require_once(__DIR__ . '/../database/db.php');
require_once(__DIR__ . '/../traits/passport.trait.php');
require_once(__DIR__ . '/../traits/employee_tr.trait.php');
require_once(__DIR__ . '/../traits/customer_tr.trait.php');
require_once(__DIR__ . '/../traits/branch_expenses.trait.php');
require_once(__DIR__ . '/../traits/transaction.trait.php');
require_once(__DIR__ . '/../traits/bank_money.trait.php');
require_once(__DIR__ . '/../traits/transfer.trait.php');
require_once(__DIR__ . '/../traits/branch.trait.php');
require_once(__DIR__ . '/../traits/login.trait.php');
require_once(__DIR__ . '/../traits/card.trait.php');
require_once(__DIR__ . '/../traits/account.trait.php');
require_once(__DIR__ . '/../traits/saving_deposit.trait.php');
require_once(__DIR__ . '/../traits/central_bank_customer.trait.php');

class admin
{
    private $admin_id;
    use passport;
    use employee_tr;
    use central_bank_customer;
    use customer_tr, card, transfer, transaction, account 
    {
        customer_tr::read_customer_cards insteadof card;
        customer_tr::read_customer_cards insteadof transfer;
        customer_tr::read_customer_cards insteadof transaction;
        customer_tr::read_customer_cards as private _hidden_read_customer_cards;

        card::read_customer_cards as read_card_customer_cards;
        transfer::read_customer_cards as read_transfer_customer_cards;
        transaction::read_customer_cards as read_transaction_customer_cards;
        
        account::read_customer_accounts insteadof customer_tr;
        account::read_customer_accounts as private _hidden_read_customer_accounts;
        customer_tr::read_customer_accounts as read_account_customer_accounts;

        account::delete_account insteadof customer_tr;
        customer_tr::delete_account as delete_account_cust;

        account::delete_account as public;
        customer_tr::delete_customer as public;
    }
    use branch_expenses;
    use bank_money;
    use branch;
    use login;

    use saving_deposit
    {
        saving_deposit::read_customer_saving_deposit as private;
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