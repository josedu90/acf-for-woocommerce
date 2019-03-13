<?php
class ACF_Woo_Before_Edit_Account_Form_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_edit_account_form';
        $this->acf_slug = 'wc-before-form-edit-account-detail';
        $this->name = 'Before Form: Edit Account Detail';
        $this->form_id = 'acf_before_form_edit_account_detail';
        parent::__construct();
    }
}

ACF_Woo_Before_Edit_Account_Form_Location::get_instance();
