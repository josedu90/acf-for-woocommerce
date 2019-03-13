<?php
class ACF_Woo_Before_Available_Downloads_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_available_downloads';
        $this->acf_slug = 'wc-before-available-downloads';
        $this->name = 'Before Form: Before Available Downloads';
        $this->form_id = 'acf_before_available_downloads';
        parent::__construct();
    }
}

ACF_Woo_Before_Available_Downloads_Location::get_instance();
