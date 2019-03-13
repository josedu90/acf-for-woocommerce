<?php
class ACF_Woo_After_My_Account_Location extends ACF_Woo_Base_My_Account_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_after_my_account';
        $this->acf_slug = 'wc-after-my-account';
        $this->name = 'After Form: My Account';
        $this->form_id = 'acf_after_my_account';
        parent::__construct();
    }
}

ACF_Woo_After_My_Account_Location::get_instance();
