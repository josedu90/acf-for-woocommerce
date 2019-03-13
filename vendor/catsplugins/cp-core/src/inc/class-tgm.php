<?php


if (!class_exists('CpTgm')) {
    class CpTgm
    {
        private $plugins = [];
        private $config = [];

        function __construct($plugins)
        {
            $this->config = array(
                'id'           => 'catsplugin-tgm',
                'default_path' => '',
                'menu'         => 'catsplugin-tgm',
                'parent_slug'  => \CastPlugin\CpConstant::SLUG_DASHBOARD,
                'capability'   => 'edit_theme_options',
                'has_notices'  => true,
                'dismissable'  => true,
                'dismiss_msg'  => '',
                'is_automatic' => false,
                'message'      => '',
                'strings'      => array(
                    'page_title'                      => __( 'Install Required Plugins', 'theme-slug' ),
                    'menu_title'                      => __( 'Install Plugins', 'theme-slug' ),
                )
            );


            $plugins = array_merge($this->plugins, $plugins);
            $config = $this->config;


            add_action( 'tgmpa_register', function () use ($plugins, $config) {
                tgmpa($plugins, $config);
            });

        }
    }
}