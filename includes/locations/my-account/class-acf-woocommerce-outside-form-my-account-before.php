<?php
class ACF_Woo_Before_My_Account_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_my_account';
        $this->acf_slug = 'wc-before-my-account';
        $this->name = 'Before Form: My account';
        $this->form_id = 'acf_before_my_account';
        parent::__construct();
    }
}

ACF_Woo_Before_My_Account_Location::get_instance();
