<?php
require_once ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/locations/class-acf-woocommerce-base-location.php');

class ACF_Woo_Base_My_Account_Location extends ACF_Woo_Base_Location {
    protected $hook;
    protected $form_id;

    // initialize this location
    protected function __construct() {
        $this->group_slug = 'my-account';
        $this->priority = 1;

        add_action($this->hook, array(&$this, 'add_fields_to_my_account_page'), 100);
        add_action('woocommerce_customer_save_address', array(&$this, 'process_my_account_addresses_fields'), 100, 2);
        add_action('woocommerce_save_account_details', array(&$this, 'process_my_account_detail_fields'), 100);

        parent::__construct();
    }

    // determine when this group needs to load the acf_form_head function. should be overriden by child class for it's logic to run
    protected function _needs_form_head() {
        if (class_exists('WooCommerce')) {
             return is_account_page();
        }
    }
    // on the my account, load our my account js
    protected function _enqueue_assets() {
        // reused vars
        $uri = ACF_Woo_Launcher::get_instance()->plugin_dir_url('assets/js/acf-woocommerce-my-account-script.js');

        // queue up the my account specific js, that handles the acf form validation
        wp_enqueue_script('acf-woocommerce-my-account', $uri, array('jquery'));
    }

    // add the fields we need to the billing information form on the my account
    public function add_fields_to_my_account_page() {
        $api = ACF_Woo_API::get_instance();

        // get post_id and post_type for single, my account type to apply filter groups rule
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        // fetch the list of groups that belong on the my account
        $field_groups = $api->get_field_groups(array(
            $this->group_slug => $this->acf_slug,
            'post_id' => $post_id,
            'post_type' => $post_type,
        ));

        // if there are no field groups to show, then bail
        if (!is_array($field_groups) || empty($field_groups))
            return;

        // get the group keys from the array of fields
        $group_keys = wp_list_pluck($field_groups, 'ID');

        // fetch the appropriate order id to use
        $order_id = wp_create_nonce();

        // start styling the fields for a woocommerce form
        $api->wc_fields_start();

        // otherwise render the groups
        $this->acf_form(
            apply_filters(
                'acf-my-account-form-params', 
                array(
                    'id' => $this->form_id,
                    'post_id' => 'my_account_' . $order_id,
                    'field_groups' => $group_keys,
                    'form' => false,
                    'updated_message' => '',
                ),  
                'my_account_' . $order_id,
                wc_get_order($order_id)
            ));

        // add the javascript we need in order to make this work via ajax
        $api->acf_js_form_register('#' . $this->form_id);

        // stop styling the fields for a woocommerce form
        $api->wc_fields_stop();

    }

    // detect and process the my account addresses fields, once we have an order number and a user to work with
    public function process_my_account_addresses_fields($user_id, $load_address) {
        $this->_handle_my_account_fields($user_id);
    }
    
    // detect and process the my account detail fields, once we have an order number and a user to work with
    public function process_my_account_detail_fields($user_id) {
        $this->_handle_my_account_fields($user_id);
    }

    public function _handle_my_account_fields($user_id) {
        // if the acf fields validate, then save them
        if ($this->_form_submitted()) {
            // if the function exists (because acf pro is active), then set the form data
            //if (function_exists('acf_set_form_data'))
                //acf_set_form_data(array('post_id' => $order_id));

            //get all group_keys
            $api = ACF_Woo_API::get_instance();
            $acf_field_wrapper = $api->acf_field_in_request();
            $field_groups = $api->get_field_groups(array(
                $this->group_slug => $this->acf_slug,
            ));
            $group_keys = wp_list_pluck($field_groups, 'ID');

            // set order to custom_field_value
            if (isset($_REQUEST)) {
                foreach ($group_keys as $group_key => $key) {
                    $fields = $api->get_field_group_fields($key);
                    
                    foreach ($fields as $field => $value) {
                        $field_key = $value['key'];
                        $fields_options = $value['show_fields_options'];

                        if ($fields_options) {
                            //$data = base64_encode(serialize(($_REQUEST[$acf_field_wrapper][$field_key])));
                            if (isset($_REQUEST[$acf_field_wrapper][$field_key]) && in_array('user', $fields_options)) {
                                update_user_meta($user_id, $field_key, $_REQUEST[$acf_field_wrapper][$field_key]);
                            }
                        }
                    }
                }
            }
        }
    }
}
