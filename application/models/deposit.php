<?php

class Deposit extends CI_Model {

       
        

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function get_daily_bal_max()
        {
                $query = $this->db->query('SELECT daily_deposit_amount FROM deposit order by daily_deposit_amount DESC LIMIT 1');
                
                return $query->result_array();
        }

        

}
