<?php
class ACF_Woo_Before_Add_To_Cart_Form extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_add_to_cart_form';
        $this->acf_slug = 'wc-before-add-to-cart-form';
        $this->name = 'Before Add To Cart Form';
        $this->form_id = 'acf_before_add_to_cart_form';
        parent::__construct();
    }
}

ACF_Woo_Before_Add_To_Cart_Form::get_instance();