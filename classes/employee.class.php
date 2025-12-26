<?php 
require_once(__DIR__ . '/../database/db.php');
require_once(__DIR__ . '/../traits/passport.trait.php');
require_once(__DIR__ . '/../traits/employee_tr.trait.php');
require_once(__DIR__ . '/../traits/customer_tr.trait.php');
require_once(__DIR__ . '/../traits/transaction.trait.php');
require_once(__DIR__ . '/../traits/transfer.trait.php');
require_once(__DIR__ . '/../traits/login.trait.php');
require_once(__DIR__ . '/../traits/card.trait.php');
require_once(__DIR__ . '/../traits/account.trait.php');
require_once(__DIR__ . '/../traits/saving_deposit.trait.php');
require_once(__DIR__ . '/../traits/central_bank_customer.trait.php');

class employee
{
    private $employee_id;
    use passport;
    use central_bank_customer;

    use customer_tr, card, transfer, transaction, account, login, employee_tr 
    {
        // read_customer_cards
        card::read_customer_cards insteadof customer_tr;
        card::read_customer_cards insteadof transfer;
        card::read_customer_cards insteadof transaction;
        card::read_customer_cards as private _hidden_read_customer_cards;

        customer_tr::read_customer_cards as read_card_customer_cards;
        transfer::read_customer_cards    as read_transfer_customer_cards;
        transaction::read_customer_cards as read_transaction_customer_cards;

        //read_customer_accounts
        account::read_customer_accounts insteadof customer_tr;
        account::read_customer_accounts as private _hidden_read_customer_accounts;
        customer_tr::read_customer_accounts as read_account_customer_accounts;

        // Hide sensitive customer methods
        customer_tr::delete_customer as private _hidden_delete_customer;

        // Hide card methods
        card::read_all_card as private _hidden_read_all_card;
        card::search_card   as private _hidden_search_card;
        card::delete_card   as private _hidden_delete_card;

        // Hide transaction methods 
        transaction::cancel_payments as private _hidden_cancel_payments;

        // Hide account methods
        account::delete_account as private _hidden_delete_account;

        // Hide login methods not needed 
        login::read_all_login as private _hidden_read_all_login;
        login::search_login   as private _hidden_search_login;

        // Hide create_login completely
        login::create_login insteadof customer_tr, employee_tr;

        login::create_login as private _hidden_login_create_login;
        customer_tr::create_login as private _hidden_customer_create_login;
        employee_tr::create_login as private _hidden_employee_create_login;
    }

    use saving_deposit
    {
        read_customer_saving_deposit as private;
        delete_saving_deposit as private;
        withdraw_saving_deposit as private;
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