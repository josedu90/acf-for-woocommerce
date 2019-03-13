<?php

class ACF_Woo_Single_Group extends ACF_Woo_Base_Group {
    // initialize this group
    protected function __construct() {
        $this->slug = 'single';
        $this->name = 'Single';

        // finish normal initialization
        parent::__construct();
    }
}

ACF_Woo_Single_Group::get_instance();
