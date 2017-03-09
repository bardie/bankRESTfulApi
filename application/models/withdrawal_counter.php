<?php

class Withdrawal_counter extends CI_Model {

        

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function get_amount($date)
        {
                $query = $this->db->query("SELECT amount FROM daily_withdrawal_counter WHERE date='$date'");
                
                return $query->result_array();
        }
        public function get_transactions_per_day($date,$table)
        {
                $query = $this->db->query("SELECT transaction_per_day FROM $table WHERE date='$date'");
                
                return $query->result_array();
        }

        

}
