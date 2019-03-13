<?php
class ACF_Woo_Available_Download_Start_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_available_download_start';
        $this->acf_slug = 'wc-before-billing-form';
        $this->name = 'Before Form: Available Download Start';
        $this->form_id = 'acf_available_download_start';
        parent::__construct();
    }
}

ACF_Woo_Available_Download_Start_Location::get_instance();
