<?php
class ACF_Woo_After_Single_Variation extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_single_variation';
        $this->acf_slug = 'wc-after-single-variation';
        $this->name = 'After Single Variation';
        $this->form_id = 'acf_after_single_variation';
        parent::__construct();
    }
}

ACF_Woo_After_Single_Variation::get_instance();