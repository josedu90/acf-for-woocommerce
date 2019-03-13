<?php

abstract class ACF_Woo_Base_Group extends ACF_Woo_Singleton {
    // counter to help with unique naming as a fallback
    protected static $inc = 0;

    // information about this group
    protected $slug = '';
    protected $name = '';

    // container for the name of the primary group to add our new group to
    protected $primary_group_key = '';

    // container for the location data
    protected $locations = array();

    // protect the constructor so that we can actually have a singleton
    protected function __construct() {
        // setup the fallback name of this group
        $this->primary_group_key = __('Woocommerce', 'acf');

        // after all plugins are loaded, allow locations to register themselves with this group
        add_action('plugins_loaded', array(&$this, 'plugins_loaded'), 100);

        // add the checkout location to the acf location selection
        // REFERENCE: advanced-custom-fields-pro/admin/views/field-group-locations.php @ 4
        add_filter('acf/location/rule_types', array(&$this, 'add_location'), 10);

        // add all the options for our location
        // REFERENCE: advanced-custom-fields-pro/admin/field-group.php @ 899
        add_filter('acf/location/rule_values/' . $this->slug, array(&$this, 'add_location_values'), 10, 1);

        // when fetching the list of fields to display on the various WC pages, we must filter those groups from the list of all groups
        // REFERENCE: advanced-custom-fields-pro/core/location.php @ 1275
        add_filter('acf/location/rule_match/' . $this->slug, array(&$this, 'filter_location_groups'), 10, 3);

        // load the locations for this location group
        $this->_load_locations();
    }

    // once all plugins are loaded, allow locations to register themselves with this group
    public function plugins_loaded() {
        // once all of the locations are loaded, allow them to register with this group
        do_action('acf-woo-register-locations/' . $this->slug, $this);
    }

    // add the field group location to the list of available locations
    public function add_location($groups) {
        // if the key does not yet exist, like in ACF non-pro, then add it now
        if (is_array($groups) && !isset($groups[$this->primary_group_key]))
            $groups[$this->primary_group_key] = array();

        // add this location to the appropriate group
        $groups[$this->primary_group_key][$this->slug] = $this->name;

        return $groups;
    }

    // add the various possible location values for this location group
    public function add_location_values($choices) {
        // normalize choices to an array
        $choices = is_array($choices) ? $choices : array();
        
        if ($this->slug == 'item-in-cart') { // add active products for item in cart rule
            $choices = $this::_load_active_products_from_woocommerce();
        } else { // add items for each registered location
            foreach ($this->locations as $location)
                $choices[$location['slug']] = $location['name'];
        }

        return $choices;
    }
    
    protected function _load_active_products_from_woocommerce() {
        // get products
        $products = array();
        $products_list = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => 'product',
            'post_status'   => 'publish',
            'order'         => 'ASC',
        ));
        
        foreach ($products_list as $product) {
            $products[$product->ID] = $product->post_title;
        }
        
        // get products variation
        $variations = array();
        $variations_list = get_posts(array(
            'numberposts'   => -1,
            'post_type'     => 'product_variation',
            'post_status'   => 'publish',
            'order'         => 'ASC',
        ));

        foreach ($variations_list as $variation) {
            $variations[$variation->ID] = '- ' . $variation->post_title;
        }


        //get category product
        $cats = array();
        $catList = get_terms(array(
            'taxonomy' => 'product_cat'
        ));
        if ($catList) {
            foreach ($catList as $cat) {
                $cats[$cat->term_id] = $cat->name;
            }
        }


        $choices = [
            'Products' => $products,
            'Products Category' => $cats,
            'Variations' => $variations
        ];
        
        return $choices;
    }

    // allow a location to register itself with this locaiton group
    public function register_location($args) {
        // normalize the args
        $args = wp_parse_args($args, array(
            'slug' => '',
            'name' => '',
            'object' => null,
        ));
        // if we are missing critical data, then bail
        if (empty($args['slug']) || empty($args['name']))
            return false;

        // otherwise add the location to the locations for this group
        $this->locations[$args['slug']] = $args;
        return true;
    }

    // filter the entire list of acf groups to only include those from the specific location
    public function filter_location_groups($match, $rule, $args) {
        // if the rule for this field is not for this group then pass
        if ($this->slug !== $rule['param'])
            return $match;
        if (!is_admin()) {
        // check for item in cart
        if ($rule['param'] == 'item-in-cart') {
            $value = $rule['value'];
            $cart = WC()->cart->get_cart();
            
            if ($cart) {
                foreach ($cart as $item) {
                    $item_value = $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
                    if (($item_value == $value) && ($rule['operator'] == '==')) {
                        return true;
                    }
                }
                
                return false;
            }
        }
        }
        
        // get a list of locations for this location group
        $locations = $this->add_location_values(array());

        // see if the $args ar asking for a specific location from this location group. if so, match
        if (isset($args[$this->slug])) {
            // if the request is for a location that is specific to this location group, then match
            if (is_string($rule['value']) && isset($locations[$rule['value']], $args[$this->slug]) && $rule['value'] == $args[$this->slug]) {
                $match = true;
            }
        }
    
        return $match;
    }

    // figure out the path to this location group's locations
    protected function _my_location_paths($base_path) {
        return apply_filters(
            'acf-woo-location-paths/' . $this->slug,
            array(trailingslashit($base_path) . $this->slug . '/'),
            $base_path,
            $this
        );
    }

    protected function _load_locations() {
        $this::_load_locations_by_regex('/^.*base.*.php/');
        $this::_load_locations_by_regex('/^.*.php$/');
    }

    protected function _load_locations_by_regex($regex) {
        // the base path to the locations
        $base_dir = ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/locations/');

        // the paths to look for locations to load for this location group
        $dirs = $this->_my_location_paths($base_dir);

        // cycle through the list of paths, and load any files we find for this location group
        foreach ($dirs as $dir) {
            // create an iterator to find all the files for this location group
            $iter = new RegexIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($dir),
                    RecursiveIteratorIterator::SELF_FIRST
                ),
                $regex,
                RecursiveRegexIterator::GET_MATCH
            );
            
            // cycle through the matched files, and include them
            foreach ($iter as $fullpath => $filename) {
                require_once $fullpath;
            }
        }
    }



}
