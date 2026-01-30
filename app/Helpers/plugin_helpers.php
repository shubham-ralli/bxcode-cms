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
        // Smart pattern detection for shared routes
        if (strpos($url, 'admin/posts') !== false) {
            $pattern = 'admin.posts*';
        } elseif (strpos($url, 'admin/media') !== false) {
            $pattern = 'admin.media*';
        } else {
            $pattern = 'admin.' . $slug . '*';
        }

        // Parse URL for query parameters
        $active_queries = [];
        $parsedUrl = parse_url($url);
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $active_queries);
        }

        AdminMenuService::register($slug, [
            'label' => $label,
            'route' => $url,
            'icon' => $icon ?: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>',
            'active_pattern' => $pattern,
            'active_queries' => $active_queries,
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

if (!function_exists('add_filter')) {
    function add_filter($tag, $callback, $priority = 10)
    {
        add_action($tag, $callback, $priority);
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value, ...$args)
    {
        global $plugin_hooks;

        if (empty($plugin_hooks[$tag])) {
            return $value;
        }

        // Sort by priority
        usort($plugin_hooks[$tag], function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        foreach ($plugin_hooks[$tag] as $item) {
            $value = call_user_func_array($item['callback'], array_merge([$value], $args));
        }

        return $value;
    }
}

/**
 * META BOX SYSTEM
 * WordPress-style meta box registration for post edit screens.
 */
global $meta_boxes;
$meta_boxes = [];

if (!function_exists('add_meta_box')) {
    /**
     * Register a meta box for the post edit screen.
     *
     * @param string   $id        Unique ID for the meta box
     * @param string   $title     Title of the meta box
     * @param callable $callback  Function to output the meta box content
     * @param string   $post_type Post type ('post', 'page', or custom type)
     * @param string   $context   'main' for left column, 'side' for right column
     * @param int      $priority  Display priority (default 10)
     */
    function add_meta_box($id, $title, $callback, $post_type = 'post', $context = 'side', $priority = 10)
    {
        global $meta_boxes;

        if (!isset($meta_boxes[$post_type])) {
            $meta_boxes[$post_type] = [];
        }

        if (!isset($meta_boxes[$post_type][$context])) {
            $meta_boxes[$post_type][$context] = [];
        }

        $meta_boxes[$post_type][$context][] = [
            'id' => $id,
            'title' => $title,
            'callback' => $callback,
            'priority' => $priority
        ];
    }
}

if (!function_exists('get_meta_boxes')) {
    /**
     * Get registered meta boxes for a specific post type and context.
     *
     * @param string $post_type Post type
     * @param string $context   'main' or 'side'
     * @return array
     */
    function get_meta_boxes($post_type, $context)
    {
        global $meta_boxes;

        if (!isset($meta_boxes[$post_type][$context])) {
            return [];
        }

        $boxes = $meta_boxes[$post_type][$context];

        // Sort by priority
        usort($boxes, function ($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });

        return $boxes;
    }
}

if (!function_exists('render_meta_boxes')) {
    /**
     * Render all meta boxes for a specific context.
     *
     * @param string $post_type Post type
     * @param string $context   'main' or 'side'
     * @param object $post      Post object
     */
    function render_meta_boxes($post_type, $context, $post)
    {
        $boxes = get_meta_boxes($post_type, $context);

        foreach ($boxes as $box) {
            echo '<div class="bg-white rounded-lg shadow-sm mb-6" x-data="{ open: true }">';
            echo '<div class="px-4 py-3 border-b border-gray-100 cursor-pointer flex justify-between items-center" @click="open = !open">';
            echo '<h3 class="font-semibold text-gray-700">' . e($box['title']) . '</h3>';
            echo '<button type="button" class="text-gray-400 hover:text-gray-600 transition-transform" :class="{ \'rotate-180\': !open }">';
            echo '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            echo '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>';
            echo '</svg>';
            echo '</button>';
            echo '</div>';
            echo '<div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95">';

            ob_start();
            call_user_func($box['callback'], $post);
            $content = ob_get_clean();

            echo $content;
            echo '</div>';
            echo '</div>';
        }
    }
}

if (!function_exists('supports_meta_box')) {
    /**
     * Check if a post type supports a specific meta box.
     * This allows flexible configuration for custom post types.
     *
     * @param string $post_type Post type to check
     * @param string $meta_box  Meta box identifier
     * @return bool
     */
    function supports_meta_box($type, $feature)
    {
        // Standard WordPress-like features mapping
        $defaultSupports = ['title', 'editor', 'featured_image', 'excerpt', 'author', 'categories', 'tags', 'comments'];

        // Check if it's a built-in type
        if ($type === 'post') {
            return in_array($feature, $defaultSupports);
        }
        if ($type === 'page') {
            $pageSupports = ['title', 'editor', 'featured_image', 'author', 'page_attributes'];
            return in_array($feature, $pageSupports);
        }

        // Custom Post Type
        static $cptCache = [];

        if (!isset($cptCache[$type])) {
            // Using DB facade fully qualified if not imported, or assume global usage if in helpers
            $cpt = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('key', $type)->first();
            $cptCache[$type] = $cpt ? (json_decode($cpt->supports, true) ?? []) : false;
        }

        if ($cptCache[$type] === false) {
            return false; // Type not found
        }

        $supports = $cptCache[$type];

        // Handle both ['title', 'editor'] (indexed) and ['title' => '1'] (associative)
        if (isset($supports[$feature]) && $supports[$feature]) {
            return true;
        }

        return in_array($feature, $supports);
    }
}
