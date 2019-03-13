<?php
class ACF_Woo_Edit_Account_Form_Before_Submit extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_edit_account_form';
        $this->acf_slug = 'wc-inside-form-edit-account-before-submit';
        $this->name = 'Inside Form: Edit Account - Before Submit Button';
        $this->form_id = 'acf_inside_form_edit_account_before_submit';
        parent::__construct();
    }
}

ACF_Woo_Edit_Account_Form_Before_Submit::get_instance();
