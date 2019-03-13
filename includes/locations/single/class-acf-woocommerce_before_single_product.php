<?php
class ACF_Woo_Before_Single_Product extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_single_product';
        $this->acf_slug = 'wc-before-single-product';
        $this->name = 'Before Single Product';
        $this->form_id = 'acf_before_single_product';
        parent::__construct();
    }
}

ACF_Woo_Before_Single_Product::get_instance();
