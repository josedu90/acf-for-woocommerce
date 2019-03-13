<?php
class ACF_Woo_Edit_Account_Billing_Address_Form_End_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_edit_account_address_form';
        $this->acf_slug = 'wc-after-form-edit-account-address-billing';
        $this->name = 'After Form: Edit Account Address';
        $this->form_id = 'acf_after_form_edit_account_address_billing';
        parent::__construct();
    }
}

ACF_Woo_Edit_Account_Billing_Address_Form_End_Location::get_instance();
