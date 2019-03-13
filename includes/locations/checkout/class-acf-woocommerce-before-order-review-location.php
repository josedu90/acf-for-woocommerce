<?php
class ACF_Woo_Before_Order_Review_Location extends ACF_Woo_Base_Checkout_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_before_order_review';
        $this->acf_slug = 'wc-before-order-review';
        $this->name = 'Before order review';
        $this->form_id = 'acf_before_order_review';
        parent::__construct();
    }
}

ACF_Woo_Before_Order_Review_Location::get_instance();
