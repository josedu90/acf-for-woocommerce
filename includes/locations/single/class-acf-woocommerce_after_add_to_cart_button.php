<?php
class ACF_Woo_After_Add_To_Cart_Button extends ACF_Woo_Base_Single_Location {
    // initialize this location
    protected function __construct() {
        $this->hook = 'woocommerce_after_add_to_cart_button';
        $this->acf_slug = 'wc-after-add-to-cart-button';
        $this->name = 'After Add To Cart Button';
        $this->form_id = 'acf_after_add_to_cart_button';
        parent::__construct();
    }
}

ACF_Woo_After_Add_To_Cart_Button::get_instance();