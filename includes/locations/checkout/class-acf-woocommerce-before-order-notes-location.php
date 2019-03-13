<?php
class ACF_Woo_Before_Order_Notes_Location extends ACF_Woo_Base_Checkout_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_before_order_notes';
        $this->acf_slug = 'wc-before-order-notes';
        $this->name = 'Before order notes';
        $this->form_id = 'acf_before_order_notes';
        parent::__construct();
    }
}

ACF_Woo_Before_Order_Notes_Location::get_instance();
