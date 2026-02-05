<?php

namespace App\Services;

class AdminMenuService
{
    protected static $items = [];

    /**
     * Register a new menu item.
     *
     * @param string $key Unique key for the menu item
     * @param array $attributes ['label', 'route', 'icon', 'active_pattern', 'order']
     * @return void
     */
    public static function register(string $key, array $attributes)
    {
        self::$items[$key] = array_merge([
            'label' => 'Unknown',
            'route' => '#',
            'icon' => '', // SVG Content
            'active_pattern' => '',
            'order' => 100,
            'children' => []
        ], $attributes);
    }

    /**
     * Get all registered menu items sorted by order.
     *
     * @return array
     */
    public static function get()
    {
        $items = self::$items;

        uasort($items, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return $items;
    }
}
