<?php
class ACF_Woo_After_Single_Product_Summary extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_single_product_summary';
        $this->acf_slug = 'wc-after-single-product-summary';
        $this->name = 'After Single Product Summary';
        $this->form_id = 'acf_after_single_product_summary';
        parent::__construct();
    }
}

ACF_Woo_After_Single_Product_Summary::get_instance();