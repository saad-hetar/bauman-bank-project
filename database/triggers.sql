DELIMITER $$

CREATE EVENT IF NOT EXISTS daily_bank_money_refresh_eur
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
BEGIN
    INSERT INTO bank_money (
        currency,
        expenses_sum,
        commission_sum,
        deposit_sum,
        customer_money_sum,
        cacula_date
    )
    SELECT 
        currency_list.currency,

       
        -- 1. EXPENSES (no currency in table → assume default USD)
        CASE 
            WHEN currency_list.currency = 'eur'
            THEN (SELECT IFNULL(SUM(cost), 0) 
                  FROM branch_expenses 
                  WHERE DATE(paid_at) = CURDATE())
            ELSE 0
        END AS expenses_sum,

 
        -- 2. COMMISSIONS
        (
            -- from transaction table (via card → account currency)
            (SELECT IFNULL(SUM(t.commission), 0)
             FROM transaction t
             JOIN card c ON c.card_num = t.card_num
             JOIN account a ON a.account_id = c.account_id
             WHERE a.currency = currency_list.currency
               AND DATE(t.trans_date) = CURDATE())
            +
            -- from transfer table (direct currency column)
            (SELECT IFNULL(SUM(commission), 0)
             FROM transfer
             WHERE currency = currency_list.currency
               AND DATE(trans_date) = CURDATE())
        ) AS commission_sum,


        -- 3. DEPOSIT SUM
        (SELECT IFNULL(SUM(amount), 0)
         FROM deposit
         WHERE currency = currency_list.currency
           AND status = 'active') AS deposit_sum,

        (SELECT IFNULL(SUM(amount * interest_rate), 0)
         FROM deposit
         WHERE currency = currency_list.currency
           AND status = 'withdrawn') AS interest_deposits_sum,

        -- 4. CUSTOMER MONEY SUM (card → account currency)
        (SELECT IFNULL(SUM(c.balance), 0)
         FROM card c
         JOIN account a ON a.account_id = c.account_id
         WHERE a.currency = currency_list.currency) AS customer_money_sum,

        NOW() AS cacula_date

    FROM (

        -- Build list of all currencies from all relevant tables

        SELECT DISTINCT currency FROM account
        UNION
        SELECT DISTINCT currency FROM transfer
        UNION
        SELECT DISTINCT currency FROM deposit

    ) AS currency_list;

END$$

DELIMITER ;

DELETE FROM login WHERE role = "employee";
DELETE FROM passport WHERE passport_id = 58;
SELECT * from login where login_id = "4266BE32";
SELECT * from deposit where account_id = 38;
DELETE FROM deposit;

SELECT c.*, a.currency FROM card c
                    JOIN account a ON a.account_id = c.account_id
                    JOIN customer cu ON cu.customer_id = a.customer_id
                    WHERE a.customer_id = 37  
                    ORDER BY c.balance DESC;

select * from login;

SELECT employee.emp_id, 
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
                        login.login_id AS login_name,
                        login.role AS login_role

                    FROM employee 
                    JOIN passport 
                        ON passport.passport_id = employee.passport_id
                    LEFT JOIN login
                        ON login.user_id = employee.emp_id
                    WHERE employee.emp_id = 3;