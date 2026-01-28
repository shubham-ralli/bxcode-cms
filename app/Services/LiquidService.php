<?php

namespace App\Services;

class LiquidService
{
    protected static $globals = [];
    protected static $tags = [];
    protected static $filters = [];

    // Global Variables for {{ variable }}
    public static function addGlobal($key, $value)
    {
        self::$globals[$key] = $value;
    }

    public static function getGlobals()
    {
        return self::$globals;
    }

    // Tags for {% tag %}
    public static function registerTag($tag, $class)
    {
        self::$tags[$tag] = $class;
    }

    public static function getTags()
    {
        return self::$tags;
    }

    // Filters for {{ value | filter }}
    // (LiquidPHP handles filters typically via classes or auto-methods, strict registration might vary based on config)
}
