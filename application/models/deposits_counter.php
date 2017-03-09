<?php

class Deposits_counter extends CI_Model {

        

        public function __construct()
        {
                // Call the CI_Model constructor
                parent::__construct();
        }

        public function get_amount($date)
        {
                $query = $this->db->query("SELECT amount FROM daily_amount_counter WHERE date='$date'");
                
                return $query->result_array();
        }

        

}
