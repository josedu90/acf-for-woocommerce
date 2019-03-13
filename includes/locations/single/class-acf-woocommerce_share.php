<?php
class ACF_Woo_Share extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_share';
        $this->acf_slug = 'wc-share';
        $this->name = 'Share';
        $this->form_id = 'acf_share';
        parent::__construct();
    }
}

ACF_Woo_Share::get_instance();