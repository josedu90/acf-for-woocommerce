<?php
class ACF_Woo_Edit_Account_Form_Start_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_edit_account_form_start';
        $this->acf_slug = 'wc-inside-form-edit-account-start';
        $this->name = 'Inside Form: Edit Account Form Start';
        $this->form_id = 'acf_inside_form_edit_account_start';
        parent::__construct();
    }
}

ACF_Woo_Edit_Account_Form_Start_Location::get_instance();
