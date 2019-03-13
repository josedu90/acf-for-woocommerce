<?php
class ACF_Woo_Before_Variations_Form extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_variations_form';
        $this->acf_slug = 'wc-before-variations-form';
        $this->name = 'Before Variations Form';
        $this->form_id = 'acf_before_variations_form';
        parent::__construct();
    }
}

ACF_Woo_Before_Variations_Form::get_instance();