<?php
require_once ACF_Woo_Launcher::get_instance()->plugin_dir_path('includes/locations/class-acf-woocommerce-base-location.php');

if (!function_exists('wp_handle_upload')) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
}

class ACF_Woo_Base_Single_Location extends ACF_Woo_Base_Location
{
    protected $hook;
    protected $form_id;

    // store data from product variable ajax
    public $group_slug_api = null;
    public $acf_slug_api = null;
    public $post_id_api = null;

    // container cart item data
    protected $cart_item_key = null;
    protected $cart_item_data = null;

    // initialize this location
    protected function __construct()
    {
        $this->group_slug = 'single';
        $this->priority = 1;

        add_action($this->hook, array(&$this, 'add_fields_to_single_page'), 100);
        add_action('wp_enqueue_scripts', array(&$this, 'acf_load_script'), 100);
        add_filter('woocommerce_add_cart_item_data', function ($cart_item_data, $product_id, $variation_id) {
//            bdump("1", "woocommerce_add_cart_item_data");
            $api = ACF_Woo_API::get_instance();

            $acf_field_wrapper = $api->acf_field_in_request();

            $post_id = $variation_id ? $variation_id : $product_id;
            $field_groups = $api->get_field_groups(array(
                $this->group_slug => ($this->acf_slug_api) ? $this->acf_slug_api : $this->acf_slug,
                'post_id' => $post_id,
                'post_type' => get_post_type($variation_id ? $variation_id : $product_id),
            ));

            $group_keys = wp_list_pluck($field_groups, 'ID');
            if (isset($_REQUEST) && isset($_REQUEST[$acf_field_wrapper])) {

                $data = array();
                $email_fields = array();
                if (sizeof($group_keys) > 0) {

                    foreach ($group_keys as $group_key => $key) {
                        $fields = $api->get_field_group_fields($key);
                        foreach ($fields as $field => $value) {
                            $field_key = isset($value['key']) ? $value['key'] : '';

                            $fields_options = $value['show_fields_options'];
                            if ($fields_options) {

                                if (in_array('single', $fields_options) || in_array('price', $fields_options)) {

                                    if (isset($_REQUEST[$acf_field_wrapper][$field_key])) {
                                        $data = $_REQUEST[$acf_field_wrapper][$field_key];
                                    }

                                    $attach_id = 0;
                                    if (($value['type'] == 'file' || $value['type'] == 'image')) {
                                        $attach_id = $_REQUEST[$acf_field_wrapper][$field_key];

                                        if (is_numeric($attach_id) && $attach_id > 0) {
                                            $data = base64_encode(serialize($attach_id));

                                            $current_user = wp_get_current_user();
                                            $user_id = $current_user->ID;

                                            wp_update_post(array(
                                                "ID" => $attach_id,
                                                "post_author" => $user_id,
                                                "post_parent" => $post_id,
                                            ));

                                        }
                                    }

                                    if ($attach_id) {
                                        $data = $attach_id;
                                    }

                                    if ($data) {
                                        $cart_item_data[$field_key] = $data;
                                    }
                                }

                                if (in_array('user', $fields_options)) {
                                    $current_user = wp_get_current_user();
                                    $user_id = $current_user->ID;
                                    if (isset($_REQUEST[$acf_field_wrapper][$field_key])) {
                                        update_user_meta($user_id, $field_key, $_REQUEST[$acf_field_wrapper][$field_key]);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $cart_item_data;
        }, 10, 3);

        // pre-calculate total price after add extra cost to each item cart
        add_action('woocommerce_before_calculate_totals', function ($cart_object) {
            if ($cart_object->cart_contents) {
                $cart_contents = $cart_object->cart_contents;
                $api = ACF_Woo_API::get_instance();

                foreach ($cart_contents as $meta_key => $content) {
                    $extra_price = 0;
                    $post_id = !empty($content['variation_id']) ? $content['variation_id'] : $content['product_id'];
                    $post_type = !empty($post_id) ? get_post_type($post_id) : null;

                    // get field groups
                    $field_groups = $api->get_field_groups(array(
                        $this->group_slug => ($this->acf_slug_api) ? $this->acf_slug_api : $this->acf_slug,
                        'post_id' => $post_id,
                        'post_type' => $post_type,
                    ));

                    $group_keys = wp_list_pluck($field_groups, 'ID');

                    // get extra price for each item
                    if (sizeof($group_keys) > 0) {
                        foreach ($group_keys as $group_key => $key) {
                            $fields = $api->get_field_group_fields($key);

                            foreach ($fields as $field => $value) {
                                $field_key = isset($value['key']) ? $value['key'] : '';

                                $fields_options = $value['show_fields_options'];

                                if ($fields_options && in_array('price', $fields_options)) {

                                    if (!empty($content[$field_key])) {
                                        $prices = $content[$field_key];

                                        // sum array prices into extra price
                                        if (is_array($prices)) {
                                            if ($value['type'] == 'repeater' || $value['type'] == 'flexible_content') {
                                                foreach ($prices as $row) {
                                                    foreach ($row as $price) {
                                                        if (is_array($price)) { // checkbox
                                                            foreach ($price as $val) {
                                                                $extra_price += $this->get_extra_price($val);
                                                            }
                                                        } else { // select and radio
                                                            $extra_price += $this->get_extra_price($price);
                                                        }
                                                    }
                                                }
                                            } else { // checkbox
                                                foreach ($prices as $price) {
                                                    $extra_price += $this->get_extra_price($price);
                                                }

                                            }
                                        } else { // select and radio

                                            $extra_price += $this->get_extra_price($prices);
                                        }
                                    }
                                }

                            }
                            // set new product with extra price
                            $priceOrigin = 0;
                            $priceOrigin = $content['data']->get_sale_price();
                            if ($priceOrigin <= 0) {
                                $priceOrigin = $content['data']->get_regular_price();
                            }
                            if ($priceOrigin > 0) {
                                $old_price = $priceOrigin;
                                $new_price = $old_price + $extra_price;
                                // set new product price
                                $content['data']->set_price($new_price);
                            }
                        }

                    }
                }
            }
        }, 100);

        add_filter('woocommerce_get_item_data', function ($cart_data, $cartItem) {
            $custom_items = array();
            if (!empty($cart_data)) {
                $custom_items = $cart_data;
            }

            $product_id = $cartItem['product_id'];
            $post_type = get_post_type($product_id);
            if ($cartItem['data']->is_type('variation') && is_array($cartItem['variation'])) {
                $product_id = $cartItem['variation_id'];
                $post_type = get_post_type($product_id);
            }

            $api = ACF_Woo_API::get_instance();
            $acf_field_wrapper = $api->acf_field_in_request();
            $field_groups = $api->get_field_groups(array(
                $this->group_slug => ($this->acf_slug_api) ? $this->acf_slug_api : $this->acf_slug,
                'post_id' => $product_id,
                'post_type' => $post_type,
            ));

            $data = array();
            $group_keys = wp_list_pluck($field_groups, 'ID');
            foreach ($group_keys as $group_key => $key) {
                $fields = $api->get_field_group_fields($key);
                foreach ($fields as $field => $value) {
                    $field_key = $value['key'];
                    $field_label = $value['label'];

                    $meta = isset($cartItem[$field_key]) ? $cartItem[$field_key] : '';
                    if (empty($meta)) {
                        continue;
                    }

                    if ($value['type'] == 'repeater' || $value['type'] == 'flexible_content') {
                        $showing = '<table style="border-collapse: collapse; width: 100%" class="cats_acf_for_woo_repeater_table">';
                        foreach ((array)$meta as $row) {
                            $showing .= '<tr>';
                            foreach ((array)$row as $_key => $column) {
                                if (empty($column)) {
                                    continue;
                                }

                                $_field = get_field_object($_key);
                                $results = $this->prepare_acf_fields_data($column, $_field);
                                $showing .= "<td style='border: 1px solid #ddd;' class='cats_acf_for_woo_repeater_table' >" . implode('; ', $results) . "</td>";
                            }
                            $showing .= '</tr>';
                        }
                        $showing .= '</table>';
                        $custom_items[] = array("name" => $field_label, "value" => $showing);
                    } else {
                        $results = $this->prepare_acf_fields_data($meta, $value);
                        $custom_items[] = array("name" => $field_label, "value" => implode('; ', $results));
                    }
                }
            }
            return $custom_items;
        }, 10, 2);

        add_filter('woocommerce_display_item_meta', function ($html, $item, $args) {
            $strings = array();
            $html = '';

            if ($item->get_formatted_meta_data()) {
                foreach ($item->get_formatted_meta_data() as $meta_id => $meta) {
                    if ($meta->value) {
                        if (preg_match('/[\()}{]/', $meta->value)) {
                            $_av = explode(';', $meta->value);
                            if (is_array($_av)) {
                                $tmp = array();
                                foreach ($_av as $_v) {
                                    $v = json_decode(stripslashes($_v));
                                    if (is_object($v)) {
                                        $tmp[] = $v->label;
                                    } else {
                                        $tmp[] = $_v;
                                    }
                                }
                                $meta->display_value = '<p>' . implode('; ', $tmp) . '</p>';
                                $value = $args['autop'] ? wp_kses_post($meta->display_value) : wp_kses_post(make_clickable(trim($meta->display_value)));
                                $strings[] = '<strong class="wc-item-meta-label">' . wp_kses_post($meta->display_key) . ':</strong> ' . $value;
                            }
                        } else {
                            $value = $args['autop'] ? wp_kses_post($meta->display_value) : wp_kses_post(make_clickable(trim($meta->display_value)));
                            $strings[] = '<strong class="wc-item-meta-label">' . wp_kses_post($meta->display_key) . ':</strong> ' . $value;
                        }
                    }
                }
            }

            if ($strings) {
                $html = $args['before'] . implode($args['separator'], $strings) . $args['after'];
            }

            return $html;
        }, 10, 3);

        add_action('woocommerce_new_order_item', function ($itemId, $cartItem, $key) {
            $api = ACF_Woo_API::get_instance();
            $acf_field_wrapper = $api->acf_field_in_request();
            $cartItem = isset($cartItem->legacy_values) ? $cartItem->legacy_values : null;
            $field_groups = $api->get_field_groups(array(
                $this->group_slug => $this->acf_slug,
                'post_id' => $cartItem['product_id'],
                'post_type' => get_post_type($cartItem['product_id']),
            ));

            $group_keys = wp_list_pluck($field_groups, 'ID');

            $data = array();

            foreach ($group_keys as $group_key => $key) {
                $fields = $api->get_field_group_fields($key);
                foreach ($fields as $field => $value) {
                    $field_key = $value['key'];
                    $field_label = $value['label'];

                    $meta = isset($cartItem[$field_key]) ? $cartItem[$field_key] : '';
                    if (empty($meta)) {
                        continue;
                    }

                    if ($value['type'] == 'repeater' || $value['type'] == 'flexible_content') {
                        $showing = '<table style="border-collapse: collapse; width: 100%">';
                        foreach ((array)$meta as $row) {
                            $showing .= '<tr>';
                            foreach ((array)$row as $_key => $column) {
                                if (empty($column)) {
                                    continue;
                                }

                                $_field = get_field_object($_key);
                                $results = $this->prepare_acf_fields_data($column, $_field);
                                $showing .= "<td style='border: 1px solid #ddd;'>" . implode('; ', $results) . "</td>";
                            }
                            $showing .= '</tr>';
                        }
                        $showing .= '</table>';
                        wc_add_order_item_meta($itemId, $field_label, $showing);
                    } else {
                        $results = $this->prepare_acf_fields_data($meta, $value);
                        wc_add_order_item_meta($itemId, $field_label, implode('; ', $results));
                    }
                }
            }
        }, 10, 3);

        parent::__construct();
    }

    // prepare_acf_fields_data func
    public function prepare_acf_fields_data($meta, $field)
    {
        $results = array();

        switch ($field['type']) {
            case 'checkbox':
                foreach ((array)$meta as $value) {
                    $choices = $field['choices'];
                    if (preg_match('/[\()}{]/', $value)) {
                        $_v = json_decode(stripslashes($value));
                        $value = ($_v->value) ? $_v->value : '';
                    }
                    $results[] = !empty($choices[$value]) ? $choices[$value] : '-';
                }
                break;

            case 'radio':
            case 'image_select':
            case 'select':
                $choices = $field['choices'];

                if (is_array($meta)) {
                    foreach ((array)$meta as $value) {
                        $choices = $field['choices'];
                        if (preg_match('/[\()}{]/', $value)) {
                            $_v = json_decode(stripslashes($value));
                            $value = ($_v->value) ? $_v->value : '';
                        }
                        $results[] = !empty($choices[$value]) ? $choices[$value] . ' (+' . wc_price($value) . ')' : '-';
                    }
                } else if (is_string($meta)) {
                    if (preg_match('/[\()}{]/', $meta)) {
                        $_v = json_decode(stripslashes($meta));
                        $meta = ($_v->value) ? $_v->value : '';
                    }

                    $results[] = !empty($choices[$meta]) ? $choices[$meta] . ' (+' . wc_price($meta) . ')' : '-';
                }

                break;

            case 'repeater':
            case 'flexible_content':
                $results[] = '-';
                break;

            case 'true_false':
                $results[] = $field['_valid'] ? $field['message'] : '-';
                break;

            case 'image':
                $image_attributes = wp_get_attachment_image_src($meta);
                $img_html = '-';
                if ($image_attributes && $image_attributes[0] && $image_attributes[1] && $image_attributes[2]) {
                    $img_html = '<img src="' . $image_attributes[0] . '" width="150px" height="150px" />';
                }
                $results[] = $img_html;
                break;

            case 'file':
                $fullsize_path = wp_get_attachment_url($meta);
                $filename = basename(get_attached_file($meta));
                $file_html = '-';
                if ($fullsize_path && $filename) {
                    $file_html = '<a href="' . $fullsize_path . '" target="_blank">' . $filename . '</a>';
                }
                $results[] = $file_html;
                break;

            case 'gallery':
                foreach ((array)$meta as $gallery) {
                    $image_attributes = wp_get_attachment_image_src($gallery);
                    $img_html = '-';
                    if ($image_attributes && $image_attributes[0] && $image_attributes[1] && $image_attributes[2]) {
                        $img_html = '<img src="' . $image_attributes[0] . '" width="' . $image_attributes[1] . '" height="' . $image_attributes[2] . '" />';
                    }
                    $results[] = $img_html;
                }
                break;

            case 'page_link':
                $link = "<a target='_blank' href=" . $meta . ">" . $meta . "</a>";
                $results[] = $link;
                break;

            case 'post_object':
                $link = "<a target='_blank' href=" . get_permalink($meta) . ">" . get_the_title($meta) . "</a>";
                $results[] = $link;
                break;

            case 'relationship':
                foreach ((array)$meta as $value) {
                    $results[] = "<a target='_blank' href=" . get_permalink($value) . ">" . get_the_title($value) . "</a>";
                }
                break;

            case 'taxonomy':
                $taxonomy = $field['taxonomy'];
                $field_type = $field['field_type'];

                // get term by taxonomy
                $terms = get_terms(array(
                    'taxonomy' => $taxonomy,
                    'hide_empty' => false,
                ));

                if ($field_type == 'checkbox' || $field_type == 'multi_select') {
                    foreach ((array)$meta as $value) {
                        foreach ((array)$terms as $term) {
                            $result = ($term->term_id == $value) ? $term->name : '';
                            if ($result) {
                                $results[] = $result;
                                continue;
                            }
                        }
                    }
                } else {
                    foreach ((array)$terms as $term) {
                        $results[] = ($term->term_id == $meta) ? $term->name : '';
                    }
                }
                break;

            case 'user':
                $author_obj = get_user_by('id', $meta);
                $result = '-';
                if ($author_obj->first_name && $author_obj->last_name) {
                    $result = $author_obj->first_name . ' ' . $author_obj->last_name;
                }
                $results[] = $result;
                break;

            case 'color_picker':
                $results[] = '<a class="wp-color-result" style="background-color: ' . $meta . '"></a>';
                break;

            case 'google_map':
                $map_url = 'http://www.google.com/maps';
                $address = !empty($meta['address']) ? $meta['address'] : 'http://www.google.com/maps';
                if (!empty($meta['lat']) && !empty($meta['lat'])) {
                    $map_url = 'http://www.google.com/maps/place/' . $meta['lat'] . ',' . $meta['lng'];
                }
                $results[] = "<a target='_blank' href=" . $map_url . ">" . $address . "</a>";
                break;

            default:
                foreach ((array)$meta as $value) {
                    $results[] = !empty($value) ? $value : '-';
                }
                break;
        }

        return $results;
    }

    // get_extra_price func
    function get_extra_price($price)
    {
        $extra_price = 0;
        $price = json_decode(stripslashes($price));
        if (is_object($price) && $price->value) {
            $extra_price = (int)$price->value;
        } else if (is_numeric($price)) {
            $extra_price = $price;
        }

        return (int)$extra_price;
    }

    // determine when this group needs to load the acf_form_head function. should be overriden by child class for it's logic to run
    protected function _needs_form_head()
    {
        if (class_exists('WooCommerce')) {
            return is_product();
        }
    }

    function acf_load_script()
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script(
            'acf_load_script',
            admin_url('js/iris.min.js'),
            array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'),
            false,
            1
        );

        wp_enqueue_script(
            'wp-color-picker',
            admin_url('js/color-picker.min.js'),
            array('acf_load_script'),
            false,
            1
        );

        $colorpicker_l10n = array(
            'clear' => __('Clear'),
            'defaultString' => __('Default'),
            'pick' => __('Select Color'),
            'current' => __('Current Color'),
        );

        wp_localize_script('wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n);

        // reused vars
        //$uri = ACF_Woo_Launcher::get_instance()->plugin_dir_url('assets/js/acf-woocommerce-product-variable.js');

        // queue up the checkout specific js, that handles the acf form validation
        //wp_enqueue_script('acf-woocommerce-product-variable', $uri, array('jquery'));

        // load admin ajax
        // wp_localize_script(
        //   'acf-woocommerce-product-variable',
        //   'acf_for_woo_obj',
        //   [
        //     'url' => admin_url('admin-ajax.php'),
        //   ]
        // );

    }

    // on the checkout, load our checkout js
    protected function _enqueue_assets()
    {
        // reused vars
        $uri = ACF_Woo_Launcher::get_instance()->plugin_dir_url('assets/js/acf-woocommerce-single-script.js');

        // queue up the checkout specific js, that handles the acf form validation
        wp_enqueue_script('acf-woocommerce-single', $uri, array('jquery'), '4.0');
    }

    // check cart item key on url returned and load cart item data
    function load_cart_item_key_data($group_keys)
    {
        global $woocommerce;
        $cart_item_key = isset($_REQUEST['cart_item_key']) ? $_REQUEST['cart_item_key'] : '';
        $this->cart_item_data = WC()->cart->get_cart_item($cart_item_key);

        if ($cart_item_key) {
            $cart_item = WC()->cart->get_cart_item($cart_item_key);
            if (function_exists('acf_get_fields')) {
                add_filter('acf/get_fields', array($this, 'add_value_fields'), 10, 2);
            } else {
                add_filter('acf/field_group/get_fields', array($this, 'add_value_fields'), 10, 2);
            }
        }
    }

    // add value to each field before display
    public function add_value_fields($fields, $gid)
    {
        $cart_item = $this->cart_item_data;

        foreach ($fields as $k => $field) {
            $key = $field['key'];

            if (isset($cart_item[$key])) {
                $fields[$k]['value'] = $cart_item[$key];
            }
        }

        return $fields;
    }

    // add the fields we need to the billing information form on the single product
    public function add_fields_to_single_page()
    {
        $api = ACF_Woo_API::get_instance();

        // get post_id and post_type for single, checkout type to apply filter groups rule
        $post_id = !is_null($this->post_id_api) ? $this->post_id_api : get_the_ID();
        $post_type = get_post_type($post_id);

        $this->group_slug = !is_null($this->group_slug_api) ? $this->group_slug_api : $this->group_slug;
        $this->acf_slug = !is_null($this->acf_slug_api) ? $this->acf_slug_api : $this->acf_slug;

        // fetch the list of groups that belong on the single product
        $field_groups = $api->get_field_groups(array(
            $this->group_slug => $this->acf_slug,
            'post_id' => $post_id,
            'post_type' => $post_type,
        ));

        // if there are no field groups to show, then bail
        if (!is_array($field_groups) || empty($field_groups)) {
            return;
        }

        // get the group keys from the array of fields
        $group_keys = wp_list_pluck($field_groups, 'ID');

        // load cart item data by cart item key
        $this->load_cart_item_key_data($group_keys);

        // fetch the appropriate order id to use
        $order_id = wp_create_nonce();

        // start styling the fields for a woocommerce form
        $api->wc_fields_start();

        wp_enqueue_script(
            'wp-color-picker',
            admin_url('js/color-picker.min.js'),
            array('iris'),
            false,
            1
        );

        if (isset($_GET) & isset($_GET['cart_item_key'])) {

            $edit_cart_item_key = $_GET['cart_item_key'];
            $html_before_fields_single_product = '<input type="hidden" name="cart_item_key" value="' . $edit_cart_item_key . '">';

        } else {
            $html_before_fields_single_product = '';
        }

        // otherwise render the groups
        $this->acf_form(
            apply_filters('acf-my-account-form-params', array(
                'id' => $this->form_id,
                'post_id' => 'checkout_' . $order_id,
                'field_groups' => $group_keys,
                'form' => false,
                'uploader' => 'basic',
                'updated_message' => '',
                'html_before_fields' => $html_before_fields_single_product,
            ), 'checkout_' . $order_id, wc_get_order($order_id)
            )
        );

        // add the javascript we need in order to make this work via ajax
        $api->acf_js_form_register('#' . $this->form_id);

        // stop styling the fields for a woocommerce form
        $api->wc_fields_stop();
    }
}

function price_acf_prepare_field($field)
{

    if (!empty($field['show_fields_options'])) {
        if (
            ($field['type'] === 'checkbox' ||
                $field['type'] === 'select' ||
                $field['type'] === 'radio') &&
            array_search('price', $field['show_fields_options'])
        ) {
            foreach ($field['choices'] as $iPrice => $sLabel) {
                $sFormat = '';

                if ($iPrice > 0) {
                    $sFormat = $sLabel . ' (+' . wc_price($iPrice) . ')';

                    preg_match_all('/(([0-9,.]+).*([A-Z]{3})|([A-Z]{3}).*?([0-9,.]+))/m', $sFormat, $aMatches, PREG_SET_ORDER, 0);

                    // bên trái
                    if (isset($aMatches[0][5])) {
                        $sFormat = $sLabel . ' (+' . $aMatches[0][4] . $aMatches[0][5] . ')';
                        if ($field['type'] === 'select') {
                            $sFormat = $sLabel . ' (+' . get_woocommerce_currency_symbol() . $iPrice . ')';
                        }
                    } elseif (isset($aMatches[0][3])) {
                        // bên phải
                        $sFormat = $sLabel . ' (+' . $aMatches[0][2] . $aMatches[0][3] . ')';
                        if ($field['type'] === 'select') {
                            $sFormat = $sLabel . ' (+' . $iPrice . get_woocommerce_currency_symbol() . ')';
                        }
                    } else {
                        if ($field['type'] === 'select') {
                            $sFormat = $sLabel . ' (+' . $iPrice . get_woocommerce_currency_symbol() . ')';
                        }
                    }
                }

                $field['choices'][$iPrice] = $sFormat;
            }
        }
    }
    return $field;
}

add_filter('acf/prepare_field', 'price_acf_prepare_field');