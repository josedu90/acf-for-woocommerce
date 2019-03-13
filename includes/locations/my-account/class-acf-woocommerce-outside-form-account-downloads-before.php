<?php
class ACF_Woo_Before_Account_Downloads_Location extends ACF_Woo_Base_My_Account_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_before_account_downloads';
        $this->acf_slug = 'wc-before-account-downloads';
        $this->name = 'Before Form: Account Downloads';
        $this->form_id = 'acf_before_account_downloads';
        parent::__construct();
    }
}

ACF_Woo_Before_Account_Downloads_Location::get_instance();
