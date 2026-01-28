<?php


use App\Services\AdminMenuService;

if (!function_exists('plugin_is_active')) {
    /**
     * Check if the current plugin is active.
     * This should be called from within a plugin's functions.php
     */
    function plugin_is_active($pluginSlug = null)
    {
        if (!$pluginSlug) {
            // Try to auto-detect plugin slug from backtrace
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
            foreach ($trace as $frame) {
                if (isset($frame['file']) && strpos($frame['file'], 'plugins/') !== false) {
                    preg_match('#plugins/([^/]+)/#', $frame['file'], $matches);
                    if (isset($matches[1])) {
                        $pluginSlug = $matches[1];
                        break;
                    }
                }
            }
        }

        if (!$pluginSlug) {
            return false;
        }

        return \App\Models\Setting::get("plugin_{$pluginSlug}_active", '0') === '1';
    }
}

if (!function_exists('add_admin_menu')) {
    /**
     * Add a menu item to the Admin Sidebar.
     *
     * @param string $label Label of the menu
     * @param string $slug  Unique slug (also used for route grouping usually)
     * @param string $url   Target URL
     * @param string $icon  SVG Icon string
     * @param int    $order Order priority (default 100)
     */
    function add_admin_menu($label, $slug, $url, $icon = '', $order = 100)
    {
        // Auto-generate pattern from slug if URL looks internal
        $pattern = 'admin.' . $slug . '*';

        AdminMenuService::register($slug, [
            'label' => $label,
            'route' => $url,
            'icon' => $icon ?: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
            'active_pattern' => $pattern,
            'order' => $order
        ]);
    }

    /**
     * Add a submenu item under a parent menu.
     *
     * @param string $parent_slug Parent menu slug
     * @param string $label Submenu label
     * @param string $url Submenu URL
     * @param int $order Order priority
     */
    function add_admin_submenu($parent_slug, $label, $url, $order = 10)
    {
        $items = AdminMenuService::get();
        if (isset($items[$parent_slug])) {
            AdminMenuService::register($parent_slug, array_merge($items[$parent_slug], [
                'children' => array_merge($items[$parent_slug]['children'] ?? [], [
                    [
                        'label' => $label,
                        'route' => $url,
                        'order' => $order
                    ]
                ])
            ]));
        }
    }

    /**
     * Add a route to the Admin Panel (Easy Mode).
     * Automatically applies 'web' and 'auth' middleware and 'lp-admin' prefix.
     *
     * @param string $path          URL path (e.g. 'seo')
     * @param array|string|callable $callback Controller action or Closure
     * @param string $name          Optional route name
     * @param array $methods        HTTP methods (default ['GET'])
     */
    function add_plugin_admin_route($path, $callback, $name = null, $methods = ['GET'])
    {
        $name = $name ?: 'admin.' . str_replace('/', '.', $path);

        \Illuminate\Support\Facades\Route::middleware(['web', 'auth'])
            ->prefix('lp-admin')
            ->match($methods, $path, $callback)
            ->name($name);
    }
}

if (!function_exists('plugin_setting')) {
    /**
     * Get or set a plugin setting (Shortcut).
     * Usage: 
     *   $val = plugin_setting('my_key', 'default');
     *   plugin_setting('my_key', 'new_value'); // if 2nd arg is not default? No, better separate.
     */

    function plugin_get_setting($key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }

    function plugin_save_setting($key, $value)
    {
        return \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    /**
     * Add a route to the Frontend (Public).
     */
    function add_plugin_frontend_route($path, $callback, $methods = ['GET'])
    {
        \Illuminate\Support\Facades\Route::middleware(['web'])
            ->match($methods, $path, $callback);
    }

    /**
     * Get an input value from the current request.
     */
    function plugin_input($key = null, $default = null)
    {
        return $key ? request()->input($key, $default) : request()->all();
    }

    /**
     * Redirect back with a success message.
     */
    function plugin_redirect_back($message = 'Saved successfully.')
    {
        return back()->with('success', $message);
    }

    /**
     * Render a view.
     */
    function plugin_view($view, $data = [])
    {
        return view($view, $data);
    }
}

/**
 * HOOK SYSTEM
 * Simple action hooks for plugins.
 */
global $plugin_hooks;
$plugin_hooks = [];

if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10)
    {
        global $plugin_hooks;
        $plugin_hooks[$hook][] = ['callback' => $callback, 'priority' => $priority];
    }
}

if (!function_exists('do_action')) {
    function do_action($hook, ...$args)
    {
        global $plugin_hooks;
        if (empty($plugin_hooks[$hook]))
            return;

        // Sort by priority
        usort($plugin_hooks[$hook], function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        foreach ($plugin_hooks[$hook] as $item) {
            call_user_func_array($item['callback'], $args);
        }
    }
}
