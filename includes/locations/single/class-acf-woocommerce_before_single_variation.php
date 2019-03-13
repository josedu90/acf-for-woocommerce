<?php
class ACF_Woo_Before_Single_Variation extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_single_variation';
        $this->acf_slug = 'wc-before-single-variation';
        $this->name = 'Before Single Variation';
        $this->form_id = 'acf_before_single_variation';
        parent::__construct();
    }
}

ACF_Woo_Before_Single_Variation::get_instance();