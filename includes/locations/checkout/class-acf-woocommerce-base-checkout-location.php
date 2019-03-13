<?php
require_once ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/locations/class-acf-woocommerce-base-location.php');

class ACF_Woo_Base_Checkout_Location extends ACF_Woo_Base_Location {
    protected $hook;
    protected $form_id;

    // initialize this location
    protected function __construct() {
        $this->group_slug = 'checkout';
        $this->priority = 1;

        add_action($this->hook, array(&$this, 'add_fields_to_billing_form'), 100);
        add_action('woocommerce_checkout_order_processed', array(&$this, 'process_checkout_fields'), 100, 2);

        parent::__construct();
    }

    // determine when this group needs to load the acf_form_head function. should be overriden by child class for it's logic to run
    protected function _needs_form_head() {
        if (class_exists('WooCommerce')) {
             return is_checkout();
        }
    }

    // on the checkout, load our checkout js
    protected function _enqueue_assets() {

        if ( function_exists('is_checkout') ) {
            if(is_checkout()) {
            // reused vars
            $uri = ACF_Woo_Launcher::get_instance()->plugin_dir_url('assets/js/acf-woocommerce-checkout-script.js');

            // queue up the checkout specific js, that handles the acf form validation
            wp_enqueue_script('acf-woocommerce-checkout', $uri, array('jquery'));
            }
        }
    }

    // add the fields we need to the billing information form on the checkout
    public function add_fields_to_billing_form() {
        $api = ACF_Woo_API::get_instance();

        // get post_id and post_type for single, checkout type to apply filter groups rule
        $post_id = get_the_ID();
        $post_type = get_post_type($post_id);

        // fetch the list of groups that belong on the checkout
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
        $this->acf_form(apply_filters('acf-my-account-form-params', array(
            'id' => $this->form_id,
            'post_id' => 'checkout_' . $order_id,
            'field_groups' => $group_keys,
            'form' => false,
            'updated_message' => '',
        ),  'checkout_' . $order_id, wc_get_order($order_id)
        ));

        // add the javascript we need in order to make this work via ajax
        $api->acf_js_form_register('#' . $this->form_id);

        // stop styling the fields for a woocommerce form
        $api->wc_fields_stop();

    }

    // detect and process the checkout fields, once we have an order number and a user to work with
    public function process_checkout_fields($order_id, $posted) {
        $this->_handle_checkout_fields($order_id);
    }

    // handle the submitted fields
    protected function _handle_checkout_fields($order_id) {
        // if the acf fields validate, then save them
        if ($this->_form_submitted()) {
            // if the function exists (because acf pro is active), then set the form data
            if (function_exists('acf_set_form_data'))
                acf_set_form_data(array('post_id' => $order_id));

            //get all group_keys
            $api = ACF_Woo_API::get_instance();
            $acf_field_wrapper = $api->acf_field_in_request();
            $field_groups = $api->get_field_groups(array(
                $this->group_slug => $this->acf_slug,
            ));
            $group_keys = wp_list_pluck($field_groups, 'ID');

            // set order to custom_field_value
            if (isset($_REQUEST)) {
                $email_fields = array();
                $wc_order = wc_get_order($order_id);
                $wc_customer_id = $wc_order->get_user_id();

                foreach ($group_keys as $group_key => $key) {
                    $fields = $api->get_field_group_fields($key);
                    foreach ($fields as $field => $value) {
                        $field_key = $value['key'];
                        $field_label = $value['label'];
                        $fields_options = $value['show_fields_options'];
                        


                        if ($fields_options && isset($_REQUEST[$acf_field_wrapper][$field_key])) {

                            if (($value['type'] == 'file' || $value['type'] == 'image')) {

                                $attachment_id = $_REQUEST[$acf_field_wrapper][$field_key];

                                if (is_numeric($attachment_id) && $attachment_id > 0) {
                                    $data = base64_encode(serialize($attachment_id));

                                    wp_update_post(array(
                                        "ID" => $attachment_id,
                                        "post_author" => $wc_customer_id,
                                        "post_parent" => $order_id
                                    ));

                                } else {
                                    $data = base64_encode(serialize(''));
                                }
                            } else {
                                $data = base64_encode(serialize(($_REQUEST[$acf_field_wrapper][$field_key])));
                            }

                            if (in_array('order', $fields_options)) {
                                update_post_meta($order_id, $field_key, $data);
                            }
                            if (in_array('price', $fields_options)) {
                                if (isset($_REQUEST[$acf_field_wrapper][$field_key])) {
                                    $valueSelected = $_REQUEST[$acf_field_wrapper][$field_key];
                                   
                                    $priceTotal = 0;
                                    $textOptions = [];

                                    if ($value['choices']) {
                                        if (is_array($valueSelected)) {
                                            foreach ($valueSelected as $index => $price) {
                                                $priceTotal += (int)$price;
                                                
                                                $text = isset($value['choices'][$price]) ? $value['choices'][$price] : '';
                                                $textOptions[] = "<b>{$text}: </b> +{$price}";

                                                $item_id = wc_add_order_item( $order_id, array(
                                                    'order_item_name' => $text,
                                                    'order_item_type' => 'fee'
                                                ) );
                                                wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $price ) );
                                            }
                                        } else if (is_numeric($valueSelected)) {
                                            $priceTotal += (int)$valueSelected;
                                            $textOptions[] = "<b>{$field_label}: </b> +{$valueSelected}";

                                            $item_id = wc_add_order_item( $order_id, array(
                                                'order_item_name' => $field_label,
                                                'order_item_type' => 'fee'
                                            ) );
                                            wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $valueSelected ) );
                                        }

                                        $wc_order->calculate_totals();
                                    }

                                    update_post_meta($order_id, $field_key, $data);
                                }
                            }
                            
                            if (in_array('email', $fields_options)) {
                                array_push($email_fields, array($field_label => $data));
                            }
                            if (in_array('user', $fields_options)) {
                                $current_user = wp_get_current_user();
                                $user_id = $current_user->ID;
                                update_user_meta($user_id, $field_key, $_REQUEST[$acf_field_wrapper][$field_key]);
                            }
                        }

                    }
                }
                if ($email_fields) {
                    add_filter('woocommerce_email_order_meta_fields', function ($email_fields, $sent_to_admin, $order) {
                        return $email_fields;
                    }, 10, 3);
                }
            }
        }
    }
}
