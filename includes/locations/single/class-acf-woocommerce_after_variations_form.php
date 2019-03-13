<?php
class ACF_Woo_After_Variations_Form extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_variations_form';
        $this->acf_slug = 'wc-after-variations-form';
        $this->name = 'After Variations Form';
        $this->form_id = 'acf_after_variations_form';
        parent::__construct();
    }
}

ACF_Woo_After_Variations_Form::get_instance();