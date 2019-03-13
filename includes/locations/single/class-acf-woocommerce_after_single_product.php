<?php
class ACF_Woo_After_Single_Product extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_single_product';
        $this->acf_slug = 'wc-after-single-product';
        $this->name = 'After Single Product';
        $this->form_id = 'acf_after_single_product';
        parent::__construct();
    }
}

ACF_Woo_After_Single_Product::get_instance();