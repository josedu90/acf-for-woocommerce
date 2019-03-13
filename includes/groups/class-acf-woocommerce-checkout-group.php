<?php

class ACF_Woo_Checkout_Group extends ACF_Woo_Base_Group {
    // initialize this group
    protected function __construct() {
        $this->slug = 'checkout';
        $this->name = 'Checkout';

        // finish normal initialization
        parent::__construct();
    }
}

ACF_Woo_Checkout_Group::get_instance();
