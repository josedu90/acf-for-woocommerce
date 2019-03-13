<?php
class ACF_Woo_After_Available_Downloads_Location extends ACF_Woo_Base_My_Account_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_after_available_downloads';
        $this->acf_slug = 'wc-after-available-downloadst';
        $this->name = 'After Form: After Available Downloads';
        $this->form_id = 'acf_after_available_downloads';
        parent::__construct();
    }
}

ACF_Woo_After_Available_Downloads_Location::get_instance();
