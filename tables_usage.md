# bauman-bank-project

The tables of the project:

Passport
Saves the passport and identity information for everyone who uses or works at the bank (customers, employees, and admins) so you don't duplicate data.

Customer
Saves the profile, contact, and personal information for all the bank's clients.

Account
Allocates financial accounts to your customers. Each customer can have one or multiple accounts, allowing them to hold money in different currencies.

Card
Saves and tracks all the debit or credit cards linked to the users' accounts.

Transaction
Records day-to-day account activities like making deposits, withdrawing money (drawouts), or paying for everyday bills like university fees, electricity, and water.

Employee
Saves all the personal and professional information for the people working at the bank.

Branch
Saves the data for all the physical bank locations and tracking who manages them.

Branch Expenses
Tracks the internal money spent to run each branch, such as building rent, electricity, and water bills.

Bank Money
Acts as the daily accounting sheet. It automatically counts and totals all the bank's money, expenses, deposits, and commissions every day using a database trigger.

Capital
A small table that simply stores the bank's initial starting capital or foundational reserve funds.

Transfer
Records money sent from one person to another, separating them into internal transfers (inside our bank) or external transfers (to other banks) using card or phone numbers.

Deposit
Saves customer investments where they lock away a sum of money with the bank to earn interest over a certain period.

Bank Account
Stores the bank's own accounts used to connect, settle balances, and route money through the Central Bank.

Central Bank Customer
A registry that mirrors customers from other banks, allowing our system to smoothly process transfers when someone sends money to an external bank.

Login
Stores the usernames, roles, and encrypted passwords for customers, employees, and admins so they can log into the system securely.

