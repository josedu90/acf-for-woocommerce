<?php
class ACF_Woo_Product_Meta_Start extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_product_meta_start';
        $this->acf_slug = 'wc-product-meta-start';
        $this->name = 'Product Meta Start';
        $this->form_id = 'acf_product_meta_start';
        parent::__construct();
    }
}

ACF_Woo_Product_Meta_Start::get_instance();