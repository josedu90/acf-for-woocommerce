<?php

namespace CastPlugin;


if (class_exists('CpUtil')) {
    return;
}


class CpUtil
{
    public static function startsWith($haystack, $needle)
    {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }

    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }

    public static function pathToUrl(string $path) {
        $ssl          = @$_SERVER['HTTPS'];
        $serverName   = $_SERVER['SERVER_NAME'];
        $documentRoot = $_SERVER['DOCUMENT_ROOT'];
        $correctPath  = $path;

        $uri          = str_replace($documentRoot, '', $correctPath);

        $protocol = $ssl === 'on' ? 'https' : 'http';

        return "$protocol://$serverName$uri";
    }
}