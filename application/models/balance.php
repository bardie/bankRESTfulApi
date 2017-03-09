<?php

class Balance extends CI_Model {

        public $balance;
        

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function get_balance()
        {
                $query = $this->db->query('SELECT balance_amount FROM balance');
                
                return $query->result_array();
        }

        

}
