<?php
require_once ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/core/class-acf-woo-singleton.php');

// handles the guts of our plugin
class ACF_Woo_Main extends ACF_Woo_Singleton  {
    // load the singleton instance
    protected function __construct() {
        $this->_load_fields_and_locations();
    }
    protected function _load_fields_and_locations() {
        $dir = ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/');

        require_once $dir . 'groups/class-acf-woocommerce-base-group.php';
        require_once $dir . 'groups/class-acf-woocommerce-checkout-group.php';
        require_once $dir . 'groups/class-acf-woocommerce-single-group.php';
        require_once $dir . 'groups/class-acf-woocommerce-my-account-group.php';
        require_once $dir . 'groups/class-acf-woocommerce-item-in-cart-group.php';

        require_once $dir . 'locations/class-acf-woocommerce-base-location.php';
        require_once $dir . 'locations/checkout/class-acf-woocommerce-base-checkout-location.php';
        require_once $dir . 'locations/single/class-acf-woocommerce-base-single-location.php';
        require_once $dir . 'locations/my-account/class-acf-woocommerce-base-my-account-location.php';
        require_once $dir . 'locations/item-in-cart/class-acf-woocommerce-base-item-in-cart-location.php';

        require_once $dir . 'services/class-acf-woocommerce-api.php';
        require_once $dir . 'services/class-acf-woocommerce-ajax.php';
        require_once $dir . 'services/class-acf-woocommerce-render.php';

        include_once($dir . 'fields/acf-image-select/acf-image-select.php');
    }
}
ACF_Woo_Main::get_instance();


