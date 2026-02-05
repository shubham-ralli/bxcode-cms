<?php

namespace App\Services;

class ShortcodeService
{
    protected static $shortcodes = [];

    public static function add($tag, $callback)
    {
        self::$shortcodes[$tag] = $callback;
    }

    public static function parse($content)
    {
        if (empty(self::$shortcodes)) {
            return $content;
        }

        $pattern = get_shortcode_regex(array_keys(self::$shortcodes));

        return preg_replace_callback("/$pattern/", [self::class, 'doShortcodeTag'], $content);
    }

    public static function doShortcodeTag($m)
    {
        // allow [[foo]] syntax for escaping a tag
        if ($m[1] == '[' && $m[6] == ']') {
            return substr($m[0], 1, -1);
        }

        $tag = $m[2];
        $attr = shortcode_parse_atts($m[3]);

        if (isset(self::$shortcodes[$tag])) {
            return call_user_func(self::$shortcodes[$tag], $attr, $m[5], $tag);
        }

        return $m[0];
    }
}
