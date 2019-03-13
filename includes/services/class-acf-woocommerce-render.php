<?php
require_once ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/locations/single/class-acf-woocommerce-base-single-location.php');

// a helper class to help us access the ACF api, and stub it when not available
class ACF_Woo_Display extends ACF_Woo_Singleton {
    // container for the acf core api functions to use
    protected $funcs = array();

    // container for the api helper
    protected $api = null;

    // container cart item key
    protected $cart_item_key = null;

    // single location obj
    protected $single_location = null;

    // generic function designed to call acf core functions, based on what is available
    public function __call($name, $args) {
        return isset($this->funcs[$name]) ? call_user_func_array($this->funcs[$name], $args) : '';
    }

    // during the first creation of this object register some hooks
    protected function __construct() {
        
        $this->single_location = ACF_Woo_Base_Single_Location::get_instance();
        $this->api = ACF_Woo_API::get_instance();

        if ( ! empty($_REQUEST['cart_item_key']) ) {
            $this->cart_item_key = $_REQUEST['cart_item_key'];
        }

        // once all plugins are loaded, figure out if we need to stub any functions
        add_action('plugins_loaded', array(&$this, 'initialize_functions'));
        add_action('acf/render_field_settings', array(&$this, 'add_field_display_label_pro'));
        add_action('acf/create_field_options', array(&$this, 'add_field_display_label'));

        /* edit cart item data on single product page */
        add_action('woocommerce_admin_order_data_after_billing_address', array(&$this, 'acf_woocommerce_add_fields_to_order'));
        add_action('woocommerce_process_shop_order_meta', array(&$this, 'acf_woocommerce_update_order_meta_detail'), 10, 2);
        add_filter('show_user_profile', array(&$this, 'acf_woocommerce_add_fields_to_user'), 11);
        add_filter('edit_user_profile', array(&$this, 'acf_woocommerce_add_fields_to_user'), 11);
        add_action('personal_options_update', array( &$this, 'acf_woocommerce_save_user_meta_fields' ) );
        add_action('edit_user_profile_update', array( &$this, 'acf_woocommerce_save_user_meta_fields' ) );
        add_action('wpo_wcpdf_after_customer_notes', array(&$this, 'acf_woocommerce_add_fields_to_pdf_invoice'));
        add_action('woocommerce_email_customer_details', @array(&$this, 'acf_woocommerce_add_fields_to_emails'), 10);
        add_filter( 'woocommerce_cart_item_name', array( &$this, 'change_cart_item_name' ), 10, 3 );
        add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'before_add_to_cart_button' ) );
        add_filter( 'woocommerce_quantity_input_args', array( &$this,'put_current_quantity_input_args'), 100, 2 );
        add_filter( 'woocommerce_add_to_cart_quantity', array( &$this, 'remove_previous_product_from_cart' ), 100, 2 );
    }

    // determind which functions to use, based on what is available
    public function initialize_functions() {
    }

    // NON-PRO ONLY: add a field to the admin interface, that decides whether this field's label gets displayed on the frontend or not
    public function add_field_display_label($field) {
        ?>
        <tr class="field_display_label">
            <td class="label"><label>Display on</label>
            <td>
                <?php
                do_action('acf/create_field', array(
                    'type' => 'checkbox',
                    'name' => 'fields[' . $field['name'] . '][show_fields_options]',
                    'value' => isset($field['show_fields_options']) ? $field['show_fields_options'] : 1,
                    'choices' => array(
                        'order' => 'Order field',
                        'email' => 'Email field',
                        'single' => 'Single field',
                        'user' => 'User field',
                        'pdf' => 'PDF Invoice',
                        'price' => 'Price',
                    ),
                    'layout' => 'horizontal',
                ));
                ?>
            </td>
        </tr>
        <?php
    }

    // PRO ONLY: add a field to the admin interface, that decides whether this field's label gets displayed on the frontend or not
    public function add_field_display_label_pro($field) {
        // required
        acf_render_field_wrap(array(
            'label' => 'Display on',
            'type' => 'checkbox',
            'name' => 'show_fields_options',
            'prefix' => $field['prefix'],
            'value' => isset($field['show_fields_options']) ? $field['show_fields_options'] : 1,
            'choices' => array(
                'order' => 'Order field',
                'email' => 'Email field',
                'single' => 'Single field',
                'user' => 'User field',
                'pdf' => 'PDF Invoice',
                'price' => 'Price',
            ),
            'layout' => 'horizontal',
            'class' => 'field-display_location'
        ), 'tr');
    }

    public function acf_woocommerce_update_order_meta_detail($post_id, $post) {
        $order_meta = [];

        if (isset($_POST['acf'])) {
            $order_meta = $_POST['acf'];
        }

        foreach ($order_meta as $key => $value) {
            if ($key) {
                $value = base64_encode(serialize($value));
                update_post_meta($post_id, $key, $value);
            }
        }
    }

    // draw the acf form head
    public function acf_form_head() {
        $func = apply_filters('acf-woo-render-acf_form_head', 'acf_form_head');
        call_user_func($func);
    }

    // draw the acf form
    public function acf_form($args) {
        $func = apply_filters('acf-woo-render-acf_form', 'acf_form', $args);
        call_user_func_array($func, func_get_args());
    }

    /**
     * Show order fields to WooCommerce Order Detail
     */
    public function acf_woocommerce_add_fields_to_order($order) {
        $api = ACF_Woo_API::get_instance();
        $group_keys = wp_list_pluck($api->get_field_groups(), $api->acf_id_case_sensitive());
        $groups = [];
        $_fields = [];

        // Display values
        echo '<div class="address">';

        foreach ((array) $group_keys as $group_key => $key) {
            $fields = $api->get_field_group_fields($key);

            foreach ((array) $fields as $field => $value) {
                $fields_options = isset($value['show_fields_options']) ? $value['show_fields_options'] : array();

                if (!is_array($fields_options)) {
                    $fields_options = array($fields_options);
                }

                if ($fields_options) {
                    if (in_array('single', $fields_options)) {
                        continue;
                    }
                }

                $tmp_meta = get_post_meta(get_the_ID(), $value['key'], true);
                if(!is_array($tmp_meta)) {
                    $raw_meta = base64_decode($tmp_meta);
                    $meta = unserialize($raw_meta);
                }

                if (  isset($meta) && in_array('order', $fields_options)) {

                    $this->_get_acf_fields_html($meta, $value);

                    $groups[$key] = $key;
                    $_fields[] = $value['key'];
                    $GLOBALS["__{$value['key']}"] = $meta;

                    add_filter( "acf/load_value/key={$value['key']}", function($value, $post_id, $field) {
                        return $GLOBALS["__{$field['key']}"];
                    }, 10, 3 );
                }
            }
        }

        echo '</div>';

        echo '<style type="text/css">.edit_address .acf-fields {position: inherit !important;}</style>';
        // start styling the fields for a woocommerce form
        $api->wc_fields_start();

        // check current version is pro or free
        $acf_form_params = array();
        if (function_exists('acf_get_fields')) {
            $acf_form_params = array(
                'post_id' => get_the_ID(),
                'field_groups' => $groups,
                'fields' => $_fields,
                'form' => false,
                'updated_message' => '',
            );
        } else {
            $acf_form_params = array(
                'post_id' => get_the_ID(),
                'field_groups' => $groups,
                'form' => false,
                'updated_message' => '',
            );
        }

        // Display form
        echo '<div class="edit_address">';

        // otherwise render the groups
        $this->acf_form( apply_filters('acf-order-detail-params', $acf_form_params) );

        // stop styling the fields for a woocommerce form
        $api->wc_fields_stop();

        echo '</div>';
    }


    /**
     * Show order fields to WooCommerce PF & Packing List
     * Plugin: WC PIP
     * Hook
     */

    public function acf_woocommerce_add_fields_to_pdf_invoice_wc_pip() {
        $api = ACF_Woo_API::get_instance();
        $group_keys = wp_list_pluck($api->get_field_groups(), $api->acf_id_case_sensitive());

        foreach ((array) $group_keys as $group_key => $key) {
            $fields = $api->get_field_group_fields($key);

            foreach ((array) $fields as $field => $value) {
                $fields_options = isset($value['show_fields_options']) ? $value['show_fields_options'] : array();
                if (!is_array($fields_options)) {
                    $fields_options = array($fields_options);
                }

                if ($fields_options) {
                    if (in_array('single', $fields_options)) {
                        continue;
                    }
                }

                if (function_exists( 'wc_pip' )) {
                    global $wpo_wcpdf;
                    $order_id = $wpo_wcpdf->export->order->id;
                }

                $raw_meta = base64_decode(get_post_meta($order_id, $value['key'], true));
                $meta = unserialize($raw_meta);

                if ( in_array('pdf', $fields_options) && isset($meta) ) {
                    $this->_get_acf_fields_html($meta, $value);
                }
            }
        }
    }

    /**
     * Show order fields to WooCommerce PF & Packing Slip
     */
    public function acf_woocommerce_add_fields_to_pdf_invoice() {
        $api = ACF_Woo_API::get_instance();
        $group_keys = wp_list_pluck($api->get_field_groups(), $api->acf_id_case_sensitive());

        foreach ((array) $group_keys as $group_key => $key) {
            $fields = $api->get_field_group_fields($key);

            foreach ((array) $fields as $field => $value) {
                $fields_options = isset($value['show_fields_options']) ? $value['show_fields_options'] : array();
                if (!is_array($fields_options)) {
                    $fields_options = array($fields_options);
                }

                if ($fields_options) {
                    if (in_array('single', $fields_options)) {
                        continue;
                    }
                }

                if (class_exists('WooCommerce_PDF_Invoices')) {
                    global $wpo_wcpdf;
                    $order_id = $wpo_wcpdf->export->order->id;
                }

                $raw_meta = base64_decode(get_post_meta($order_id, $value['key'], true));
                $meta = unserialize($raw_meta);

                if ( in_array('pdf', $fields_options) && isset($meta) ) {
                    $this->_get_acf_fields_html($meta, $value);
                }
            }
        }
    }

    /**
     * Show order fields to Email
     */
    public function acf_woocommerce_add_fields_to_emails($order) {
        $order_id = (int) $order->get_order_number();
        $api = ACF_Woo_API::get_instance();
        $group_keys = wp_list_pluck($api->get_field_groups(), $api->acf_id_case_sensitive());

        foreach ((array) $group_keys as $group_key => $key) {
            $fields = $api->get_field_group_fields($key);

            foreach ((array) $fields as $field => $value) {
                $fields_options = isset($value['show_fields_options']) ? $value['show_fields_options'] : array();
                if (!is_array($fields_options)) {
                    $fields_options = array($fields_options);
                }

                if ($fields_options) {
                    if (in_array('single', $fields_options)) {
                        continue;
                    }
                }

                $tmp_meta = get_post_meta($order_id, $value['key'], true);
                if(!is_array($tmp_meta)) {
                    $raw_meta = base64_decode($tmp_meta);
                    $meta = unserialize($raw_meta);
                }

                if ( in_array('email', $fields_options) && isset($meta) ) {
                    $this->_get_acf_fields_html($meta, $value);
                }
            }
        }
    }

    public function _get_acf_fields_html($meta, $value) {
        $single_obj = $this->single_location;
        $field_label = $value['label'];
        $field_type = $value['type'];

        if ($field_type == 'repeater' || $field_type == 'flexible_content') {
            $showing = '<table style="border-collapse: collapse; width: 100%">';
            foreach ((array) $meta as $row) {
                $showing .='<tr>';
                foreach ((array) $row as $_key => $column) {
                    if(empty($column) ){
                        continue;
                    }

                    $_field = get_field_object($_key);
                    $results = $single_obj->prepare_acf_fields_data($column, $_field);
                    $showing .="<td style='border: 1px solid #c6c6c6;text-align:center;'>".implode('; ', $results)."</td>";
                }
                $showing .='</tr>';
            }
            $showing .='</table>';
            echo '<p><strong>' . $field_label . ': </strong>' . $showing . '</p>';
        } else {
            $results = $single_obj->prepare_acf_fields_data($meta, $value);
            echo '<p><strong>' . $field_label . ': </strong>' . implode('; ', $results) . '</p>';
        }
    }

    // Add edit link on product title in cart
    public function change_cart_item_name( $title, $cart_item, $cart_item_key ){
        $product = $cart_item['data'];
        $link = $product->get_permalink( $cart_item );
        $link = add_query_arg( array( 'cart_item_key' => $cart_item_key ), $link );

        //wp_nonce_url escapes the url
        $link = wp_nonce_url($link, 'edit-item');
        $title .= '<a href="' . $link . '" class="cart-edit-product"> <span style="font-size:13px; color: black; text-decoration: underline;">Edit</span></a>';

        return $title;
    }

    // Alters add to cart text when editing a product
    public function before_add_to_cart_button() {
        if ( !empty( $this->cart_item_key ) && isset($_GET['_wpnonce']) && wp_verify_nonce( $_GET['_wpnonce'], 'edit-item' ) ){
            add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
                return __('Update Cart','woocommerce');
            } , 101);
        }
    }

    // Redirect to cart when done
    public function add_to_cart_redirect( $url ) {
        global $woocommerce;

        return WC()->cart->get_cart_url();
    }

    // Change quantity value when editing a cart item
    public function put_current_quantity_input_args( $args, $product ) {
        global $woocommerce;

        if( isset($_POST['cart_item_key']) ) {

            $cart_item_key = $_POST['cart_item_key'];
            $cart_item = WC()->cart->get_cart_item( $cart_item_key );

            if ( isset($cart_item["quantity"]) ){
                $args["input_value"] = $cart_item["quantity"];
            }
        }

        return $args;
    }

    // Remove previous product from cart when editing a product
    public function remove_previous_product_from_cart( $quantity, $product_id) {
        
        if (isset($_POST['cart_item_key'])) {

            $cart_item_key = $_POST['cart_item_key'];
            WC()->cart->remove_cart_item( $cart_item_key );
            unset(WC()->cart->removed_cart_contents[ $cart_item_key ]);

        }

        return $quantity;
    }

    public function acf_woocommerce_add_fields_to_user($data) {

        if (is_admin() && !class_exists( 'Theme_My_Login' )) {
            if ($data->ID) {
                $api = ACF_Woo_API::get_instance();
                $group_keys = wp_list_pluck($api->get_field_groups(), $api->acf_id_case_sensitive());
                $_fields = [];
                $groups = [];

                foreach ((array) $group_keys as $group_key => $key) {
                    $groups[$key] = $key;
                    $fields = $api->get_field_group_fields($key);

                    foreach ($fields as $field => $value) {
                        $fields_options = isset($value['show_fields_options']) ? $value['show_fields_options'] : array();
                        if (!is_array($fields_options)) {
                            $fields_options = array($fields_options);
                        }

                        $meta = get_user_meta($data->ID, $value['key'], true);

                        if (isset($meta) && in_array('user', $fields_options)) {
                            $_fields[] = $value['key'];
                            $GLOBALS["__{$value['key']}"] = $meta;

                            add_filter( "acf/load_value/key={$value['key']}", function($value, $post_id, $field) {
                                return $GLOBALS["__{$field['key']}"];
                            }, 10, 3 );
                        }
                    }
                }

                // start styling the fields for a woocommerce form
                $api->wc_fields_start();

                // check current version is pro or free
                $acf_form_params = array();
                if (function_exists('acf_get_fields')) { // pro
                    $acf_form_params = array(
                        'post_id' => 'user_' . $data->ID,
                        'field_groups' => $groups,
                        'fields' => $_fields,
                        'html_before_fields' => '<h2>Meta Data</h2><table class="form-table">',
                        'html_after_fields' => '</table>',
                        'field_el' => 'tr',
                        'form' => false,
                        'updated_message' => '',
                    );
                } else { // free
                    $acf_form_params = array(
                        'form' => false,
                    );
                }

                // otherwise render the groups
                $this->acf_form( apply_filters('acf-user-meta-params', $acf_form_params) );

                // stop styling the fields for a woocommerce form
                $api->wc_fields_stop();
            }
        }
    }

    public function acf_woocommerce_save_user_meta_fields($user_id) {

        if (is_admin() && !class_exists( 'Theme_My_Login' )) {
            //get all group_keys
            $api = ACF_Woo_API::get_instance();
            $acf_field_wrapper = $api->acf_field_in_request();
            $group_keys = $group_keys = wp_list_pluck($api->get_field_groups(), $api->acf_id_case_sensitive());

            // set order to custom_field_value
            if (isset($_REQUEST)) {
                foreach ($group_keys as $group_key => $key) {
                    $fields = $api->get_field_group_fields($key);
                    foreach ($fields as $field => $value) {
                        $field_key = $value['key'];
                        $fields_options = isset($value['show_fields_options']) ? $value['show_fields_options'] : array();
                        if (!is_array($fields_options)) {
                            $fields_options = array($fields_options);
                        }

                        if ($fields_options) {
                            if (in_array('user', $fields_options)) {
                                if (isset($_REQUEST[$acf_field_wrapper][$field_key])) {
                                    update_user_meta($user_id, $field_key, $_REQUEST[$acf_field_wrapper][$field_key]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

ACF_Woo_Display::get_instance();