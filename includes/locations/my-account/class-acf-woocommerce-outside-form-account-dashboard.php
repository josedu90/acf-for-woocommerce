<?php
class ACF_Woo_Account_Dashboard_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_account_dashboard';
        $this->acf_slug = 'wc-account-dashboard';
        $this->name = 'Account Dashboard';
        $this->form_id = 'acf_account_dashboard';
        parent::__construct();
    }
}

ACF_Woo_Account_Dashboard_Location::get_instance();
