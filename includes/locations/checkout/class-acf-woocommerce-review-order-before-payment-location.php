<?php
class ACF_Woo_Review_Order_Before_Payment_Location extends ACF_Woo_Base_Checkout_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_review_order_before_payment';
        $this->acf_slug = 'wc-review-order-before-payment';
        $this->name = 'Review order before payment';
        $this->form_id = 'acf_review_order_before_payment';
        parent::__construct();
    }
}

ACF_Woo_Review_Order_Before_Payment_Location::get_instance();
