<?php
class ACF_Woo_Single_Product_Summary extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_single_product_summary';
        $this->acf_slug = 'wc-single-product-summary';
        $this->name = 'Single Product Summary';
        $this->form_id = 'acf_single_product_summary';
        parent::__construct();
    }
}

ACF_Woo_Single_Product_Summary::get_instance();
