<?php

// a helper class to help us access the ACF api, and stub it when not available
class ACF_Woo_API extends ACF_Woo_Singleton {
    protected $funcs = array();
    protected $post_id = null;

    // generic function designed to call acf core functions, based on what is available
    public function __call($name, $args) {
        if (isset($this->funcs[$name]))
            return call_user_func_array($this->funcs[$name], $args);
        return null;
    }

    // during the first creation of this object register some hooks
    protected function __construct() {
        // once all plugins are loaded, figure out if we need to stub any functions
        add_action('plugins_loaded', array(&$this, 'initialize_functions'));

        // call product variable js
        $this->product_variable_enqueue_assets();
    }

    public function initialize_functions() {
        if (function_exists('acf_get_fields')) {
            $this->funcs['get_field_groups'] = 'acf_get_field_groups';
            $this->funcs['get_field_group_fields'] = 'acf_get_fields';
            $this->funcs['sort_by_menu_order'] = array(&$this, 'api_sort_by_menu_order');
            $this->funcs['translate_date_format'] = array(&$this, 'no_translate_format');
        } else {
            $this->funcs['get_field_groups'] = array(&$this, 'api_get_field_groups');
            $this->funcs['get_field_group_fields'] = array(&$this, 'filter_acf_get_fields');
            $this->funcs['sort_by_menu_order'] = array(&$this, 'api_sort_by_order_no');
            $this->funcs['translate_date_format'] = array(&$this, 'translate_format');
        }

        // add ajax hook for product variable
        if ( is_user_logged_in() ) {
            add_action( 'wp_ajax_acf_for_woo_ajax_change', array( $this, 'acf_for_woo_ajax_handler' ));
        } else {
            add_action( 'wp_ajax_nopriv_acf_for_woo_ajax_change', array( $this, 'acf_for_woo_ajax_handler' ));
        }
    }

    // product variable ajax handle
    public function acf_for_woo_ajax_handler() {
        $data = array();
        $hooks = array( // register product variation hooks
            'woocommerce_before_add_to_cart_form' => 'wc-before-add-to-cart-form',
            'woocommerce_before_variations_form' => 'wc-before-variations-form',
            'woocommerce_before_add_to_cart_button' => 'wc-before-add-to-cart-button',
            'woocommerce_before_single_variation' => 'wc-before-single-variation',
            'woocommerce_single_variation' => 'wc-single-variation',
            'woocommerce_after_single_variation' => 'wc-after-single-variation',
            'woocommerce_after_add_to_cart_button' => 'wc-after-add-to-cart-button',
            'woocommerce_after_variations_form' => 'wc-after-variations-form',
            'woocommerce_after_add_to_cart_form' => 'wc-after-add-to-cart-button-form',
        );

        if ( isset($_POST['variation_id']) && ! empty($_POST['variation_id']) ) {
            $variation_id = $_POST['variation_id'];
            $this->post_id = $variation_id;
            
            // check rules and get data return
            foreach ($hooks as $hook => $value) {
                $field_groups = $this->_get_valid_field_groups_ajax($value);
                if ( is_array($field_groups) && !empty($field_groups) ) {
                    $data['hook'] = $hook;
                    $data['value'] = $value;
                    break;
                }
            }
            
            // get data html to display on own hook
            if ( isset($data['value']) && !empty($data['value']) ) {
                require_once plugin_dir_path(__DIR__) . 'locations/single/class-acf-woocommerce-base-single-location.php';

                // store product variable data to instance
                $api = ACF_Woo_Base_Single_Location::get_instance();
                $api->group_slug_api = 'single';
                $api->acf_slug_api = $data['value'];
                $api->post_id_api = $variation_id;

                // store the form html returned
                ob_start();
                $api->add_fields_to_single_page();
                $data['html'] = ob_get_contents();
                ob_end_clean();
            }

            echo json_encode($data);
        }

        die;
    }

    function _get_valid_field_groups_ajax($value) {
        $api = ACF_Woo_API::get_instance();

        // get post_id and post_type for single, checkout type to apply filter groups rule
        $post_id = !is_null($this->post_id) ? $this->post_id : get_the_ID();
        $post_type = get_post_type($post_id);

        // fetch the list of groups that belong on the single product
        $field_groups = $api->get_field_groups(array(
            'single' => $value,
            'post_id' => $post_id,
            'post_type' => $post_type,
        ));

        return $field_groups;
    }

    // start overriding the styling of the fields, so that the fields fit better into woocommerce forms
    public function wc_fields_start() {
        add_filter('acf/get_fields', array(&$this, 'wc_modify_fields'), 1000, 2);
    }

    // return acf $_REQUEST field for pro vs free
    public function acf_field_in_request() {
        if (function_exists('acf_get_fields')) {
            return 'acf';
        } else {
            return 'fields';
        }
    }

    // return case senstive for free vs paid
    public function acf_id_case_sensitive() {
        if (function_exists('acf_get_fields')) {
            return 'ID';
        } else {
            return 'id';
        }
    }

    // stop making the fields blend into wc form
    public function wc_fields_stop() {
        remove_filter('acf/get_fields', array(&$this, 'wc_modify_fields'), 1000);
    }

    // apply the field modifications to each field in the list
    public function wc_modify_fields($fields) {
        // cycle through the list of fields, and apply the modifications
        foreach ((array) $fields as $index => $field)
            $fields[$index] = $this->wc_modify_field_data($field);

        return $fields;
    }

    // funciton that actually performs the field modifications to make them fit into the wc forms better
    public function wc_modify_field_data($field) {
        // add the appropriate field wrapper classes to make the field fit better in wc forms
        if (isset($field['wrapper'], $field['wrapper']['class']))
            $field['wrapper']['class'] .= ' form-row';

        return $field;
    }

    // add the js to the bottom of a rendered form, that allows the form to be recognized by the frontend acf js, and thus the fields be initialized and required fields be enforced
    public function acf_js_form_register($jq_selector) {
        ?>
        <script type="text/javascript">if (jQuery) jQuery(function ($) {
                if (acf && 'function' == typeof acf.do_action) acf.do_action('append', $('<?php esc_attr($jq_selector) ?>'));
            });</script><?php
    }

    public function api_get_field_groups($args = false) {
        // load all the acf groups
        $field_groups = apply_filters('acf/get_field_groups', array());
        // and add their location information
        foreach ($field_groups as $index => $group)
            $field_groups[$index]['location'] = apply_filters('acf/field_group/get_location', array(), $group['id']);

        // filter the list of groups by our args
        return $this->_filter_groups($field_groups, $args);
    }

    public function filter_acf_get_fields($group_id) {
        return apply_filters('acf/field_group/get_fields', array(), $group_id);
    }

    public function api_sort_by_menu_order($a, $b) {
        if (isset($a['menu_order'], $b['menu_order'])) {
            return $a['menu_order'] - $b['menu_order'];
        }
        return 0;
    }

    public function api_sort_by_order_no($a, $b) {
        if (isset($a['order_no'], $b['order_no'])) {
            return $a['order_no'] - $b['order_no'];
        }
        return 0;
    }

    protected function _filter_groups($field_groups, $args) {
        // if we do not have any args or field groups, then bail
        
        if (empty($field_groups) || empty($args))
            return $field_groups;

        $out_groups = array();
        // cycle through the groups and find all that match the args

        if (is_array($field_groups)) while ($group = array_shift($field_groups)) {
            if ($this->_group_matches($group, $args)) {
                $group['ID'] = $group['id'];
                $out_groups[] = $group;
            }
        }

        return $out_groups;
    }

    // figure out if a discreet group matches the supplied args
    protected function _group_matches($group, $args) {

        $args = wp_parse_args($args, array(
            // pro
            'post_id' => 0,
            'post_type' => 0,
            'page_template' => 0,
            'page_parent' => 0,
            'page_type' => 0,
            'post_status' => 0,
            'post_format' => 0,
            'post_taxonomy' => null,
            'taxonomy' => array(),
            'user_id' => 0,
            'user_role' => 0,
            'user_form' => 0,
            'attachment' => 0,
            'comment' => 0,
            'widget' => 0,
            'lang' => defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '',
            'ajax' => false,
            // non-pro extras
            'post_category' => array(),
            'ef_taxonomy' => 0,
            'ef_user' => 0,
            'ef_media' => 0,
        ));
        // filter for 3rd party customization
        $args = apply_filters('acf/location/screen', $args, $group);
        
        // if the group is not active, bail
        if (isset($group['active']) && !$group['active'])
            return false;

        $show = false;
        // cycle through the location rules, and figure out if this group matches the args
        foreach ($group['location'] as $rules_id => $rules) {
            // figure out if any rules pass
            $passed = true;

            if (is_array($rules)) foreach ($rules as $rule) {
                // figure out if this rule matches
                $match = apply_filters('acf/location/rule_match/' . $rule['param'], false, $rule, $args);

                // if the rule does not match, bail now
                if (!$match) {
                    $passed = false;
                    break;
                }
            }

            // if all rules for any location passed, then this group should be shown
            if ($passed) {
                $show = true;
                break;
            }
        }

        return $show;
    }

    public function no_translate_format($format) {
        return $format;
    }

    public function translate_format($format) {
        $format = preg_replace('#(\'[^\']\')#', '', $format);
        $replacement_map = array(
            'dd' => '?',
            'mm' => '!',
            'DD' => 'l',
            'MM' => 'F',
            'yy' => 'Y',
            'd' => 'j',
            'o' => 'z',
            'm' => 'n',
            '?' => 'd',
            '!' => 'm',
        );
        $format = str_replace(array_keys($replacement_map), array_values($replacement_map), $format);
        return $format;
    }
}

ACF_Woo_API::get_instance();
