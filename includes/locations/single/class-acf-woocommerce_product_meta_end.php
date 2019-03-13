<?php
class ACF_Woo_Product_Meta_End extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_product_meta_end';
        $this->acf_slug = 'wc-product-meta-end';
        $this->name = 'Product Meta End';
        $this->form_id = 'acf_product_meta_end';
        parent::__construct();
    }
}

ACF_Woo_Product_Meta_End::get_instance();