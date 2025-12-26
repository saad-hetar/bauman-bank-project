CREATE TABLE passport
(
    passport_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    last_name VARCHAR(20),
    first_name VARCHAR(70),
    middle_name VARCHAR(70),
    passport_num VARCHAR(15),
    passport_series VARCHAR(15),
    nationality VARCHAR(20),
    passport_type VARCHAR(20),
    birth_date DATE,
    birth_place VARCHAR(15),
    gender VARCHAR(8),
    issue_date DATE,
    expire_date DATE,
    assuing_authority VARCHAR(20),
    owner ENUM('employee', 'customer', 'admin')
);

CREATE TABLE customer
(
    customer_id BIGINT PRIMARY KEY AUTO_INCREMENT, 
    passport_id BIGINT,
    address VARCHAR(30),
    phone VARCHAR(30),
    email VARCHAR(40),
    created_by BIGINT, -- id
    updated_by BIGINT, -- id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE account
(
    account_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    customer_id BIGINT,
    account_type ENUM('personal', 'business'),
    account_status ENUM('active', 'closed', 'frozen') DEFAULT 'active',
    currency VARCHAR(20),
    created_by BIGINT, -- id
    updated_by BIGINT, -- id
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE card
(
    card_num BIGINT PRIMARY KEY,
    account_id BIGINT,
    cvv SMALLINT,
    expire_date VARCHAR(10),
    balance DECIMAL(15,2) DEFAULT(0)
);

CREATE TABLE transaction
(
    trans_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    card_num BIGINT,
    transacted_by VARCHAR(10),
    trans_type VARCHAR(15),
    amount DECIMAL(15,2),
    trans_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description VARCHAR(100),
    commission DECIMAL(10,2) DEFAULT(0)
);

CREATE TABLE employee
(
    emp_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    passport_id BIGINT,
    branch_id INT,
    email VARCHAR(20),
    phone VARCHAR(15),
    lore ENUM('employee', 'admin'),
    created_by BIGINT, --  id
    updated_by BIGINT, --  id
    hire_date DATE DEFAULT (CURRENT_DATE),
    quit_date DATE
);

CREATE TABLE branch
(
    branch_id INT PRIMARY KEY AUTO_INCREMENT,
    branch_name VARCHAR(40),
    manager_id BIGINT,
    address VARCHAR(20)
);

CREATE TABLE branch_expenses
(
    expenses_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    branch_id INT,
    expenses_type VARCHAR(30),
    paid_by BIGINT,
    cost DECIMAL(13, 2),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_by BIGINT
);

CREATE TABLE bank_money
(
    cacula_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    currency VARCHAR(20),
    expenses_sum DECIMAL(20, 2),
    commission_sum DECIMAL(20, 2),
    deposit_sum DECIMAL(20, 2),
    customer_money_sum DECIMAL(25, 2),
    interest_deposits_sum DECIMAL(20, 2),
    total_money DECIMAL(25,3) AS 
        (
            commission_sum - expenses_sum - interest_deposits_sum 
        ) STORED,
        
    cacula_date TIMESTAMP,
    UNIQUE(currency, cacula_date)
);

CREATE TABLE capital
(
    capital_id INT PRIMARY KEY AUTO_INCREMENT,
    capital_amount DECIMAL(25,3) DEFAULT(1000000000.000),
    currency VARCHAR(10) DEFAULT("rub")
);

CREATE TABLE transfer
(
    trans_id INT PRIMARY KEY AUTO_INCREMENT,
    sender_card_num BIGINT,
    sender_phone VARCHAR(15),
    trans_type ENUM('internal', 'external') NOT NULL,
    amount DECIMAL(15, 2),
    currency VARCHAR(15),
    description TEXT,
    commission DECIMAL(10, 3),
    receiver_card_num BIGINT,
    receiver_phone VARCHAR(15),
    transfered_by BIGINT,
    updated_by BIGINT,
    receiver_bank VARCHAR(20) DEFAULT("Our bank"),
    trans_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE deposit
(
    deposit_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    account_id BIGINT,
    amount DECIMAL(15, 2),
    currency VARCHAR(15),
    deposit_type ENUM('term', 'demand', 'savings') NOT NULL,
    interest_rate DECIMAL(5,2) NOT NULL,
    status ENUM('active', 'withdrawn') DEFAULT 'active',
    period_months INT,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP AS (DATE_ADD(start_date, INTERVAL period_months MONTH)) STORED
);


CREATE TABLE bank_account
(
    bank_id INT PRIMARY KEY AUTO_INCREMENT,
    bank_name VARCHAR(30) UNIQUE,
    address VARCHAR(30),
    currency VARCHAR(20),
    balance DECIMAL(25, 3)
);

CREATE TABLE central_bank_customer
(
    customer_id BIGINT PRIMARY KEY AUTO_INCREMENT,
    bank_id INT, -- forein key from bank_account
    last_name VARCHAR(20),
    first_name VARCHAR(70),
    middle_name VARCHAR(70),
    phone VARCHAR(15),
    balance DECIMAL(15, 2),
    card_num BIGINT
);

CREATE TABLE login (
    login_id CHAR(8) PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('employee','customer', 'admin') NOT NULL,
    password_hash VARCHAR(255) NOT NULL
);
