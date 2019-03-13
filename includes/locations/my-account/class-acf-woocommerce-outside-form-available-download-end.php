<?php
class ACF_Woo_Available_Download_End_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_available_download_end';
        $this->acf_slug = 'wc-available-download-end';
        $this->name = 'After Form: Available Download End';
        $this->form_id = 'acf_available_download_end';
        parent::__construct();
    }
}

ACF_Woo_Available_Download_End_Location::get_instance();
