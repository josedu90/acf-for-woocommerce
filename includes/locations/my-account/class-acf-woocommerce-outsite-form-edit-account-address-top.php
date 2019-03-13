<?php
class ACF_Woo_Before_Edit_Account_Address_Form_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_edit_account_address_form';
        $this->acf_slug = 'wc-before-edit-account-address-form';
        $this->name = 'Before Form: Edit Account Address';
        $this->form_id = 'acf_before_edit_account_address_form';
        parent::__construct();
    }
}

ACF_Woo_Before_Edit_Account_Address_Form_Location::get_instance();
