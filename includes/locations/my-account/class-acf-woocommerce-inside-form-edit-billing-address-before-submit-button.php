<?php
class ACF_Woo_Edit_Account_Billing_Address_Form_Before_Save_Button_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_edit_address_form_billing';
        $this->acf_slug = 'wc-edit-account-address-form-billing-before-submit';
        $this->name = 'Inside Form: Edit Billing Address - Before Submit';
        $this->form_id = 'acf_edit_account_address_form_billing_before_submit';
        parent::__construct();
    }
}

ACF_Woo_Edit_Account_Billing_Address_Form_Before_Save_Button_Location::get_instance();
