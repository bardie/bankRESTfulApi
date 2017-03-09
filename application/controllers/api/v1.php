<?php

defined('BASEPATH') OR exit('No direct script access allowed');



require APPPATH . '/libraries/REST_Controller.php';

use application\Libraries\REST_Controller;

/**
 * This is an example of a few basic user interaction methods you could use
 * by querrying the database
 *
 * @package         CodeIgniter
 * @subpackage      bankapi
 * @category        Controller
 * @author          Mutinda Michael
 */
class v1 extends REST_Controller {

    function __construct() {
        // Construct the parent class
        parent::__construct();

       
    }

    /*
     * GET method to SELECT account balance from the database and 
     * return a response 
     */

    function balance_get() {
        $id = $this->uri->segment(3);

        if (isset($id) && $id === 'balance') {
            $this->load->model('balance');
            $data['query'] = $this->balance->get_balance();

            $this->response($data['query']['0'],200);
        }
        
       
    }

    /*
     * POST method to deposit amount into the database and 
     * Updates the balance
     */

    public function deposit_post() {
        $maxtransactioperday = $this->transactionsDone(date('Y-m-d'), 'daily_amount_counter');
        $balance = $this->getCurrentBalance();
        $amount = $this->uri->segment(4);
        $sum = $balance + $amount;
        $dailycounter = $this->getMaxDepositOfDay(date('Y-m-d'));

        //compare amount to maximum deposit amount
        if ($amount > 40000) {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Exceeded Maximum Withdrawal Per Transaction'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        } elseif (($amount + $dailycounter) > 150000) {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Cannot exceed the maximum deposits for the day $150K'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        }elseif($maxtransactioperday >= 4){
            $this->set_response([
                'status' => FALSE,
                'message' => 'Max deposit frequency = 4 transactions/day'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        } else {
            $data = [
                'balance_amount' => $sum,
            ];
            $res = $this->db->update('balance', $data, 'id = 1');
            if ($res) {
                $this->response($res, 201); 
                //increment daily deposits amounts counter
                $this->increment_counter(date('Y-m-d'), $amount);
                //increment transaction per day column by 1
                $this->addTransaction(date('Y-m-d'), 'daily_amount_counter');
            } else {
                $this->response(NULL, 404);
            }
        }
    }

    /*
     * POST method to withdraw amount from the database and 
     * Updates the balance
     */

    public function withdrawal_post() {
        $maxtransactioperday = $this->transactionsDone(date('Y-m-d'), 'daily_withdrawal_counter');
        $balance = $this->getCurrentBalance();
        $amount = $this->uri->segment(4);
        $sum = $balance - $amount;
        //get total of withdrawals per day
        $dailycounter = $this->getMaxWithdrawalOfDay(date('Y-m-d'));

        if ($amount > 20000) {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Exceeded Maximum Withdrawal Per Transaction'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        } elseif ($balance < $amount) {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Cannot withdraw when balance is less than withdrawal amount'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        } elseif (($amount + $dailycounter) > 50000) {
            $this->set_response([
                'status' => FALSE,
                'message' => 'Cannot exceed the maximum withdrawal for the day $50K'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code 
        } elseif($maxtransactioperday >= 3){
            $this->set_response([
                'status' => FALSE,
                'message' => 'Max withdrawal frequency = 3 transactions/day'
                    ], REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
        } else {
            $data = [
                'balance_amount' => $sum,
            ];
            $res = $this->db->update('balance', $data, 'id = 1');
            if ($res) {
                $this->response($res, 201);
                //increments the withdrawal amount column
                $this->increment_counter_withdrawal(date('Y-m-d'), $amount);
                //increments the transaction per day column by 1
                $this->addTransaction(date('Y-m-d'), 'daily_withdrawal_counter');
            } else {
                $this->response(NULL, 404);
            }
        }
    }

    public function getCurrentBalance() {
        $this->load->model('balance');
        $data['query'] = $this->balance->get_balance();
        return $data['query']['0']['balance_amount'];
    }
    /*
     * Checks whether the amount for that day has been found 
     * if false creates new record
     * if true increases the daily deposited amount
     */
    public function increment_counter($date, $amount) {
        $query = $this->db->query("SELECT date FROM daily_amount_counter WHERE date='$date'");

        if ($query->num_rows() == 0) {
            //insert new record
            $data = [
                'date' => $date,
                'amount' => $amount,
            ];
            $this->db->insert('daily_amount_counter', $data);
        } else {
            //update record
            $this->db->set('amount', 'amount+' . $amount, FALSE);
            $this->db->where('date', $date);
            $this->db->update('daily_amount_counter');
        }
    }
    /*
     * Checks whether the amount for that day has been found 
     * if false creates new record
     * if true increases the daily withdrawn amount
     */
    public function increment_counter_withdrawal($date, $amount) {
        $query = $this->db->query("SELECT date FROM daily_withdrawal_counter  WHERE date='$date'");

        if ($query->num_rows() == 0) {
            //insert new record
            $data = [
                'date' => $date,
                'amount' => $amount,
            ];
            $this->db->insert('daily_withdrawal_counter', $data);
        } else {
            //update record
            $this->db->set('amount', 'amount+' . $amount, FALSE);
            $this->db->where('date', $date);
            $this->db->update('daily_withdrawal_counter');
        }
    }
    /*
     * @returns total amount of money deposited in a particular date
     */
    public function getMaxDepositOfDay($date) {
        $query = $this->db->query("SELECT date FROM daily_amount_counter WHERE date='$date'");
        if ($query->num_rows() == 0) {
            return 0;
        } else {
            $this->load->model('deposits_counter');
            $data['query'] = $this->deposits_counter->get_amount($date);
            return $data['query']['0']['amount'];
        }
    }
    /*
     * @returns total amount of money withdrawn in a particular date
     */
    public function getMaxWithdrawalOfDay($date) {
        $query = $this->db->query("SELECT date FROM daily_withdrawal_counter WHERE date='$date'");
        if ($query->num_rows() == 0) {
            return 0;
        } else {
            $this->load->model('withdrawal_counter');
            $data['query'] = $this->withdrawal_counter->get_amount($date);
            return $data['query']['0']['amount'];
        }
    }
    /*
     * increments the transactions per day counter column
     * inputs are current day and table name
     */
    public function addTransaction($date,$table) {
        $this->db->set('transaction_per_day', 'transaction_per_day+1', FALSE);
        $this->db->where('date', $date);
        $this->db->update($table);
    }
    /*
     * @returns transactions done in a particular date
     */
    public function transactionsDone($date,$table) {
        $query = $this->db->query("SELECT transaction_per_day FROM $table WHERE date='$date'");
        if ($query->num_rows() == 0) {
            return 0;
        } else {
            $this->load->model('withdrawal_counter');
            $data['query'] = $this->withdrawal_counter->get_transactions_per_day($date,$table);
            return $data['query']['0']['transaction_per_day'];
        }
    }

}
