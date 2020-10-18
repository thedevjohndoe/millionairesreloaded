<?php
class referrals {
    protected $user_id;

    public function __construct($user_id) {
        $this->user_id = $user_id;
    }

    public function get_referred() {
        global $dbcon;
        $referred = array();

        return $referred;
    }
}