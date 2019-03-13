<?php
class ACF_Woo_Before_Billing_Form_Location extends ACF_Woo_Base_Checkout_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_checkout_billing_form';
        $this->acf_slug = 'wc-before-billing-form';
        $this->name = 'Before billing form';
        $this->form_id = 'acf_before_billing_form';
        parent::__construct();
    }
}

ACF_Woo_Before_Billing_Form_Location::get_instance();
