<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-acf-woocommerce-base-item-in-cart-location
 *
 * @author nghiato
 */

require_once ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/locations/class-acf-woocommerce-base-location.php');

class ACF_Woo_Base_Item_In_cart_Location extends ACF_Woo_Base_Location {
    protected $hook;
    protected $form_id;

    // initialize this location
    protected function __construct() {
        $this->group_slug = 'item-in-cart';

        parent::__construct();
    }
}