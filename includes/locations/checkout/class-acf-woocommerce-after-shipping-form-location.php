<?php
class ACF_Woo_After_Shipping_Form_Location extends ACF_Woo_Base_Checkout_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_checkout_shipping_form';
        $this->acf_slug = 'wc-after-shipping-form';
        $this->name = 'After shipping form';
        $this->form_id = 'acf_after_shipping_form';
        parent::__construct();
    }
}

ACF_Woo_After_Shipping_Form_Location::get_instance();
