<?php
class ACF_Woo_Before_Shipping_Form_Location extends ACF_Woo_Base_Checkout_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_checkout_shipping_form';
        $this->acf_slug = 'wc-before-shipping-form';
        $this->name = 'Before shipping form';
        $this->form_id = 'acf_before_shipping_form';
        parent::__construct();
    }
}

ACF_Woo_Before_Shipping_Form_Location::get_instance();
