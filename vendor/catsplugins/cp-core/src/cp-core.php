<?php

namespace CastPlugin;

if (!class_exists('CastPlugin\CastPlugin')) require_once __DIR__ . "/common/class-cp-constant.php";
if (!class_exists('CastPlugin\CpUtil')) require_once __DIR__ . "/common/class-cp-util.php";

if (!class_exists('CpMerlin')) require_once __DIR__ . "/inc/class-merlink.php";
if (!class_exists('CastPlugin\CpAdminNotice')) require_once __DIR__ . "/inc/class-cp-admin-notice.php";
if (!class_exists('CastPlugin\CpPageSetting')) require_once __DIR__ . "/inc/class-cp-page-setting.php";
if (!class_exists('CastPlugin\CpVueElement')) require_once __DIR__ . "/inc/class-cp-vue-element.php";
if (!class_exists('CastPlugin\CpTgm')) require_once __DIR__ . "/inc/class-tgm.php";

require_once __DIR__ . "/inc/tgm-active/class-tgm-plugin-activation.php";
require_once __DIR__ . "/inc/functions_helper.php";


if (!class_exists('CastPlugin\CpCore')) {
    class CpCore
    {
        public $pluginName;
        public $pluginPath;
        public $adminNotice;
        public $pluginID;
        public $force_active;

        public function __construct($pluginName, $args = array())
        {
            $this->pluginName = $pluginName;
            $this->pluginID = isset($args['plugin_id']) ? $args['plugin_id'] : '';

            $this->adminNotice = new CpAdminNotice();

            if (!isset($args['plugin_path'])) {
                $this->adminNotice->error(__("[{$pluginName}] plugin_path is require"));
            }
            if (isset($args['force_active'])) {
                $this->force_active = $args['force_active'];
            }


            $this->pageSetting = new CpPageSetting();
            $this->pluginPath = $args['plugin_path'];

            add_action('admin_menu', [$this->pageSetting, 'createPageDefault']);

            $this->assets();
        }

        public function tgm($plugins) {
            new \CpTgm($plugins);
        }


        public function merlin($config, $options, $files = []) {
            $options['product_id'] = $this->pluginID;
            $options['product_id'] = $this->pluginID;
            $config['force_active'] = $this->force_active;

            $self = $this;
            add_action('plugins_loaded', function() use($self, $config, $options, $files) {
                new \CpMerlin($self->pluginPath, $config, $options, $files);
            });


        }


        public function createPageSetting($params)
        {
            $config = [];

            if (isset($params['file']) && file_exists($params['file'])) {
                $fileContent = file_get_contents($params['file']);
                if (!empty($fileContent)) {
                    $config = json_decode($fileContent, true);
                    if (!is_array($config)) {
                        $this->adminNotice->error(__("Plugin: [{$this->pluginName}]: Config json invalid"));
                    }
                }

                $this->pageSetting->createPage($config, $params);

            } else if (isset($params['callback']) && is_array($params['callback'])) {
                $config = $params['callback'];

                add_action('admin_menu', function() use ($config){
                    add_submenu_page(
                        CpConstant::SLUG_DASHBOARD,
                        $config['page_title'],
                        $config['menu_title'],
                        $config['capability'],
                        sanitize_key($config['menu_slug']),
                        $config['func']
                    );
                });

                if (!empty($config['save_callback'])) {
                    add_action('admin_init', $config['save_callback']);
                }

            }

        }




        // Method init
        private function assets()
        {
            add_action( 'admin_enqueue_scripts',  function () {

                wp_register_style( "Cats-core-css", plugin_dir_url(__FILE__ ). '/assets/css/style.css' );
                wp_register_style( "Vue-element-ui", "https://unpkg.com/element-ui@2.4.6/lib/theme-chalk/index.css");

                wp_register_script( "Vue", 'https://cdn.jsdelivr.net/npm/vue');
                wp_register_script( "Vue-element-ui", '//unpkg.com/element-ui@2.4.6/lib/index.js', array( 'Vue' ));
                wp_register_script( "Vue-en", '//unpkg.com/element-ui@2.4.8/lib/umd/locale/en.js', array( 'Vue', 'Vue-element-ui'));

                wp_register_script( "cpcore-js", plugin_dir_url(__FILE__ ) . '/assets/js/cp-core.js');



                wp_enqueue_style('Cats-core-css');
                wp_enqueue_style('Vue-element-ui');

                wp_enqueue_script('Vue');
                wp_enqueue_style('Vue-element-ui');
                wp_enqueue_script('Vue-element-ui');
                wp_enqueue_script('Vue-en');
                wp_enqueue_script('cpcore-js');
            });
        }

        public static function loadVue()
        {
            wp_enqueue_script('Vue');
            wp_enqueue_style('Vue-element-ui');
            wp_enqueue_script('Vue-element-ui');
            wp_enqueue_script('Vue-en');
        }

        public static function getRequest($key, $filters = [], $default='') {
            $value = $default;
            if (isset($_GET[$key])) {
                $value = $_GET[$key];

                if (count($filters) > 0) {
                    foreach ($filters as $filter) {
                        if (function_exists($filter)) {
                            $value = call_user_func($filter, $value);
                        }
                    }

                    if (in_array('int', $filters)) {
                        $value = (int) $value;
                    }
                }
            }

            return $value;
        }
    }
}
