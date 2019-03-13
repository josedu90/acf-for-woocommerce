<?php

class ACF_Woo_My_Account_Group extends ACF_Woo_Base_Group {
    // initialize this group
    protected function __construct() {
        $this->slug = 'my-account';
        $this->name = 'My Account';
        
        // finish normal initialization
        parent::__construct();
    }
}

ACF_Woo_My_Account_Group::get_instance();
