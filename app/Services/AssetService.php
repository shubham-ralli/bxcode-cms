<?php

namespace App\Services;

class AssetService
{
    protected static $styles = [];
    protected static $scripts = [];

    public static function enqueueStyle($handle, $src, $deps = [], $ver = false, $media = 'all')
    {
        self::$styles[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'media' => $media
        ];
    }

    public static function enqueueScript($handle, $src, $deps = [], $ver = false, $inFooter = false)
    {
        self::$scripts[$handle] = [
            'src' => $src,
            'deps' => $deps,
            'ver' => $ver,
            'in_footer' => $inFooter
        ];
    }

    public static function printStyles()
    {
        foreach (self::$styles as $handle => $style) {
            $src = $style['src'];
            if ($style['ver']) {
                $src .= '?ver=' . $style['ver'];
            }
            echo "<link rel='stylesheet' id='{$handle}-css' href='{$src}' media='{$style['media']}' />\n";
        }
    }

    public static function printHeadScripts()
    {
        foreach (self::$scripts as $handle => $script) {
            if (!$script['in_footer']) {
                $src = $script['src'];
                if ($script['ver']) {
                    $src .= '?ver=' . $script['ver'];
                }
                echo "<script src='{$src}' id='{$handle}-js'></script>\n";
            }
        }
    }

    public static function printFooterScripts()
    {
        foreach (self::$scripts as $handle => $script) {
            if ($script['in_footer']) {
                $src = $script['src'];
                if ($script['ver']) {
                    $src .= '?ver=' . $script['ver'];
                }
                echo "<script src='{$src}' id='{$handle}-js'></script>\n";
            }
        }
    }
}
