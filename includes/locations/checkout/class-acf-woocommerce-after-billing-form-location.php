<?php
class ACF_Woo_After_Billing_Form_Location extends ACF_Woo_Base_Checkout_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_checkout_billing_form';
        $this->acf_slug = 'wc-after-billing-form';
        $this->name = 'After billing form';
        $this->form_id = 'acf_after_billing_form';
        parent::__construct();
    }
}

ACF_Woo_After_Billing_Form_Location::get_instance();
