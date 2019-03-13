<?php
class ACF_Woo_After_Order_Notes_Location extends ACF_Woo_Base_Checkout_Location {
    protected function __construct() {
        $this->hook = 'woocommerce_after_order_notes';
        $this->acf_slug = 'wc-after-order-notes';
        $this->name = 'After order notes';
        $this->form_id = 'acf_after_order_notes';
        parent::__construct();
    }
}

ACF_Woo_After_Order_Notes_Location::get_instance();
