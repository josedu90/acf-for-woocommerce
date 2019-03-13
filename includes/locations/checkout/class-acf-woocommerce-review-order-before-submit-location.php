<?php
class ACF_Woo_Review_Order_Before_Submit_Location extends ACF_Woo_Base_Checkout_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_review_order_before_submit';
        $this->acf_slug = 'wc-review-order-before-submit';
        $this->name = 'Review order before submit';
        $this->form_id = 'acf_review_order_before_submit';
        parent::__construct();
    }
}

ACF_Woo_Review_Order_Before_Submit_Location::get_instance();
