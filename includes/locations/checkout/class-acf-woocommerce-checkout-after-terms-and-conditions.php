<?php
class ACF_Woo_Checkout_After_Term_And_Conditions_Location extends ACF_Woo_Base_Checkout_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_checkout_after_terms_and_conditions';
        $this->acf_slug = 'wc-checkout-after-terms-and-conditions';
        $this->name = 'After Term & Conditions';
        $this->form_id = 'acf_checkout_after_terms_and_conditions';
        parent::__construct();
    }
}

ACF_Woo_Checkout_After_Term_And_Conditions_Location::get_instance();
