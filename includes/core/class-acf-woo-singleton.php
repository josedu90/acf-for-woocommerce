<?php

abstract class ACF_Woo_Singleton {
    private static $_instances = array();

    protected function __construct() {}

    public static function get_instance() {
        $class = get_called_class();
        if (!isset(self::$_instances[$class])) {
            self::$_instances[$class] = new $class();
        }
        return self::$_instances[$class];
    }
}
