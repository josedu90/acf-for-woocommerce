<?php
class ACF_Woo_Account_Navigation_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_account_navigation';
        $this->acf_slug = 'wc-account-navigation';
        $this->name = 'Account Navigation';
        $this->form_id = 'acf_account_navigation';
        parent::__construct();
    }
}

ACF_Woo_Account_Navigation_Location::get_instance();
