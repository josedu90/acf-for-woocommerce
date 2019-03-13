<?php
class ACF_Woo_After_Edit_Account_Form_Location extends ACF_Woo_Base_My_Account_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_edit_account_form';
        $this->acf_slug = 'wc-after-form-edit-account-detail';
        $this->name = 'After Form: Edit Account Detail';
        $this->form_id = 'acf_after_form_edit_account_detail';
        parent::__construct();
    }
}

ACF_Woo_After_Edit_Account_Form_Location::get_instance();
