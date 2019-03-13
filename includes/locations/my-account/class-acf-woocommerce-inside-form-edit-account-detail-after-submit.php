<?php
class ACF_Woo_Edit_Account_Form_After_Submit extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_edit_account_form_end';
        $this->acf_slug = 'wc-woocommerce-edit-account-form-after-submit';
        $this->name = 'Inside Form: Edit Account - After Submit Button';
        $this->form_id = 'acf_edit_account_form_after_submit';
        parent::__construct();
    }
}

ACF_Woo_Edit_Account_Form_After_Submit::get_instance();
