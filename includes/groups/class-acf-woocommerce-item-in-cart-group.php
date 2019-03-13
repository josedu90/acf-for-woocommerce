<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-acf-woocommerce-item-in-cart-group
 *
 * @author nghiato
 */

class ACF_Woo_Item_In_Cart_Group extends ACF_Woo_Base_Group {
    // initialize this group
    protected function __construct() {
        $this->slug = 'item-in-cart';
        $this->name = 'Item in Cart';
        
        // finish normal initialization
        parent::__construct();
    }
}

ACF_Woo_Item_In_Cart_Group::get_instance();
