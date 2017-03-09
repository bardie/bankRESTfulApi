# CodeIgniter Backend Mini project for RESTful API implementation


## Requirements

1. PHP 5.4 or greater
2. CodeIgniter 3.0+


## Installation

* Import the api_bank_account sql file located in the database folder into your MySQL database.
* Extract The Codeigniter zip folder into your localhost directory...wwww for wamp and htdocs for XAMPP
* Run the project after successfully starting the apache server on http://localhost/codeigniter
* The homepage contains the GET endpoits to access the bankaccount balances and instructions to the other endpoints
* To deposit an amount into the database , make POST request to this uri preferrably using POSTMAN tool
    e.g http://localhost/codeigniter/index.php/api/v1/deposit/1000
* To withdraw an amount from the database , make a POST request to this uri preferrably using POSTMAN tool
    e.g http://localhost/codeigniter/index.php/api/v1/withdrawal/1000
* When a successfull deposit is made , the amount deposited is incremented to the balance table and to the daily amounts added per day  table called 'daily_amount_counter' to check total amount deposited in a day also the transaction number is added to 'daily_amount_counter' table to check for maximum transactions done in a day
* When a successfull withdrawal is made , the amount withdrawn is reduced to the balance table and to the daily amounts withrawn per day  table called 'daily_withdrawal_counter' to check total amount withdrawn in a day also the transaction number is added to 'daily_withdrawal_counter' table to check for maximum transactions done in a day


