<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit6c6d3506f629d37849a37fb07ede418c
{
    public static $files = array (
        '587bedc970e389773915cbfa8d484551' => __DIR__ . '/../..' . '/src/cp-core.php',
    );

    public static $prefixesPsr0 = array (
        'C' => 
        array (
            'CastPlugin\\CpCore' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit6c6d3506f629d37849a37fb07ede418c::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
