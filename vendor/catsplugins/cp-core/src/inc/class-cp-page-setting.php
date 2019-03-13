<?php
namespace CastPlugin;


if (class_exists('CastPlugin\\CpPageSetting')) return;



class CpPageSetting
{

    public function __construct()
    {
    }

    public function createPageDefault()
    {
        if ( empty ( $GLOBALS['admin_page_hooks'][CpConstant::SLUG_DASHBOARD] ) )
        {
            add_menu_page(
                'Cat\'s Plugins dashboard',
                'Cat\'s Plugins',
                'manage_options',
                CpConstant::SLUG_DASHBOARD,
                'CastPlugin\CpPageSetting::castpluginDashboard',
                '',
                2
            );
        }
    }

    public static function castpluginDashboard()
    {
        include dirname(__DIR__) . '/templates/cp-page-setting/core-dashboard.php';
    }

    public function createPage($config, $options = [])
    {
        add_action('admin_menu', function() use ($config, $options){
            add_submenu_page(
                CpConstant::SLUG_DASHBOARD,
                $config['page_title'],
                $config['menu_title'],
                $config['capability'],
                sanitize_key($config['menu_slug']),
                function () use ($config, $options) {
                    CpPageSetting::pageSettings($config, $options);
                }
            );
        });

    }

    public static function pageSettings($config, $options = [])
    {
        include dirname(__DIR__) . '/templates/cp-page-setting/page-settings.php';
    }


    public static function filterOption($keyName, $fieldData)
    {
        $keyName = self::sanitizeKeyName($keyName);
        $defaultValue = isset($fieldData['default']) && !empty($fieldData['default']) ? $fieldData['default'] : '';
        $requestValue = isset($_POST[$keyName]) && !empty($_POST[$keyName]) ? $_POST[$keyName] : $defaultValue;

        if (isset($fieldData['filters']) && is_array($fieldData['filters'])) {
            foreach ($fieldData['filters'] as $filter) {
                if (function_exists($filter) && !empty($requestValue)) {
                    $requestValue = call_user_func($filter, $requestValue);
                }
            }
        }


        return $requestValue;
    }

    public static function sanitizeKeyName($keyName)
    {
        return str_replace('-', '_', sanitize_title($keyName));
    }
}