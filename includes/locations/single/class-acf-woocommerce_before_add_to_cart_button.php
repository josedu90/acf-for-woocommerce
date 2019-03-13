<?php
class ACF_Woo_Before_Add_To_Cart_Button extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_before_add_to_cart_button';
        $this->acf_slug = 'wc-before-add-to-cart-button';
        $this->name = 'Before Add To Cart Button';
        $this->form_id = 'acf_before_add_to_cart_button';
        parent::__construct();
    }
}

ACF_Woo_Before_Add_To_Cart_Button::get_instance();