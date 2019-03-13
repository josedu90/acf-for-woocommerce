<?php
/**
 * Plugin Name: ACF for Woocommerce
 * Plugin URI:  http://catsplugins.com
 * Description: A plugin to integrate ACF with WooCommerce
 * Version:     4.2
 * Author:      Cat's Plugins
 * Author URI:  http://catsplugins.com
 * License: GNU General Public License, version 3 (GPL-3.0)
 * License URI: http://www.gnu.org/copyleft/gpl.html
 * Text Domain: acf-woo
 * Domain Path: /languages
 */

define('ACF_WOO_PLUGIN_URL', plugin_dir_url(__FILE__));


require_once plugin_dir_path(__FILE__) . 'includes/core/class-acf-woo-singleton.php';

class ACF_Woo_Launcher extends ACF_Woo_Singleton
{
    protected function __construct()
    {
        require_once $this->plugin_dir_path('includes/core/class-acf-woo-main.php');
    }

    public function plugin_dir_path($path)
    {
        return plugin_dir_path(__FILE__) . $path;
    }

    public function plugin_dir_url($path)
    {
        return plugin_dir_url(__FILE__) . $path;
    }
}

ACF_Woo_Launcher::get_instance();


function acfWooRunCore()
{
    require_once __DIR__ . "/vendor/autoload.php";

    $cpCoreDemo = new CastPlugin\CpCore('ACF for Woocommerce', [
        'plugin_path' => __DIR__,
        'plugin_id' => "18705467",
        'force_active' => false
    ]);

    $cpCoreDemo->createPageSetting([
        'file' => __DIR__ . '/config/admin_setting.json'
    ]);

    $cpCoreDemo->merlin([
        'merlin_url' => 'acfwoo-merlin',
        'edd_theme_slug' => 'acfwoo-merlin',
        'license_step' => true,
        'ready_big_button_url' => admin_url('/admin.php?page=acf-woo-dashboard')
    ], [
        'import-header' => esc_html__('Import ACF Demo', 'acfwoo'),
        'import' => esc_html__('If you want to have the sample demo, please check the demo content below to have a faster kickstart', 'acfwoo'),

        'admin-menu' => esc_html(__('ACF for Woocommerce Import content')),
        'license%s' => "Enter your license key and Email",
        'plugins' => esc_html(__('ACF For WooCommerce requires Advanced Custom Fields as the main dependency, please make sure you have it checked.', 'acfwoo')),
        'ready%s' => esc_html__('ACF For WooCommerce has been setup successfully. Enjoy!', 'acfwoo'),
        'ready-action-link' => esc_html__('Extras', 'acfwoo'),
        'ready-big-button' => esc_html__('Wellcome', 'acfwoo'),
        'ready-link-1' => sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://catsplugins.com/knowledge-base/acf-for-woocommerce/full-guide/', esc_html__('Document', 'acfwoo')),
        'ready-link-2' => sprintf('<a href="%1$s" target="_blank">%2$s</a>', 'https://catsplugins.com/knowledge-base/acf-for-woocommerce/full-guide/', esc_html__('Get Help', 'acfwoo')),
        'ready-link-3' => sprintf('<a href="%1$s">%2$s</a>', 'https://catsplugins.com/faq', esc_html__('Community Support', 'acfwoo')),
    ], array(
        array(
            'import_file_name' => 'Import ACF Demo',
            'import_file_url' => ACF_WOO_PLUGIN_URL . '/data-sample/acf-export-2018-12-11.json',
        )
    ));

    $plugins = array();


    if (is_file(dirname(dirname(__FILE__)) . "/advanced-custom-fields-pro-master/acf.php")) {
        $plugins[] = array(
            'name' => 'Advanced Custom Fields',
            'slug' => 'advanced-custom-fields-pro-master',
            'required' => true,
            'force_activation' => true
        );
    } else if (is_file(dirname(dirname(__FILE__)) . "/advanced-custom-fields-pro/acf.php")) {
        $plugins[] = array(
            'name' => 'Advanced Custom Fields',
            'slug' => 'advanced-custom-fields-pro',
            'required' => true,
            'force_activation' => true
        );
    } else if (is_file(dirname(dirname(__FILE__)) . "/advanced-custom-fields/acf.php")) {
        $plugins[] = array(
            'name' => 'Advanced Custom Fields',
            'slug' => 'advanced-custom-fields',
            'required' => true,
            'force_activation' => true
        );
    }

    $cpCoreDemo->tgm($plugins);
}

acfWooRunCore();
