ALTER TABLE customer
ADD FOREIGN KEY (passport_id)
REFERENCES passport(passport_id)
ON DELETE CASCADE;

ALTER TABLE account
ADD FOREIGN KEY (customer_id)
REFERENCES customer(customer_id)
ON DELETE CASCADE;

ALTER TABLE card
ADD FOREIGN KEY (account_id)
REFERENCES account(account_id)
ON DELETE CASCADE;

ALTER TABLE transaction
ADD FOREIGN KEY (card_num)
REFERENCES card(card_num)
ON DELETE SET NULL;

ALTER TABLE employee
ADD FOREIGN KEY (passport_id)
REFERENCES passport(passport_id)
ON DELETE CASCADE;

ALTER TABLE employee
ADD FOREIGN KEY (branch_id)
REFERENCES branch(branch_id)
ON DELETE SET NULL;

ALTER TABLE branch
ADD FOREIGN KEY (manager_id)
REFERENCES employee(emp_id)
ON DELETE SET NULL;

-- ALTER TABLE audit_log
-- ADD FOREIGN KEY (changed_by)
-- REFERENCES employee(emp_id)
-- ON DELETE SET NULL;

ALTER TABLE branch_expenses
ADD FOREIGN KEY (branch_id)
REFERENCES branch(branch_id)
ON DELETE SET NULL;

ALTER TABLE branch_expenses
ADD FOREIGN KEY (paid_by)
REFERENCES employee(emp_id)
ON DELETE SET NULL;

ALTER TABLE transfer
ADD FOREIGN KEY (sender_card_num)
REFERENCES card(card_num)
ON DELETE SET NULL;

ALTER TABLE deposit
ADD FOREIGN KEY (account_id)
REFERENCES account(account_id)
ON DELETE SET NULL;

ALTER TABLE central_bank_customer
ADD FOREIGN KEY (bank_id)
REFERENCES bank_account(bank_id)
ON DELETE SET NULL;