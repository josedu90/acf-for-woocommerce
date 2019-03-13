<?php
class ACF_Woo_After_Account_Downloads_Location extends ACF_Woo_Base_My_Account_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_after_account_downloads';
        $this->acf_slug = 'wc-woocommerce-after-account-downloads';
        $this->name = 'After Form: Account Downloads';
        $this->form_id = 'acf_after_account_downloads';
        parent::__construct();
    }
}

ACF_Woo_After_Account_Downloads_Location::get_instance();
