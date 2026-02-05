<?php

use Illuminate\Support\Facades\View;
use App\Services\ShortcodeService;

if (!function_exists('add_shortcode')) {
    /**
     * Add a new shortcode.
     * @param string $tag
     * @param callable $callback
     */
    function add_shortcode($tag, $callback)
    {
        ShortcodeService::add($tag, $callback);
    }
}

if (!function_exists('do_shortcode')) {
    /**
     * Parse shortcodes in content.
     * @param string $content
     * @return string
     */
    function do_shortcode($content)
    {
        return ShortcodeService::parse($content);
    }
}

if (!function_exists('load_theme_functions')) {
    /**
     * Explicitly load the active theme's functions.blade.php
     */
    function load_theme_functions()
    {
        $activeTheme = get_active_theme();
        $path = resource_path("views/themes/{$activeTheme}/functions.blade.php");

        if (file_exists($path)) {
            require_once $path;
        }
    }
}

if (!function_exists('get_template_part')) {
    /**
     * Load a template part into a template.
     * @param string|null $name The name of the specialized template.
     * @param array $args Additional arguments to pass to the template.
     */
    function get_template_part($slug, $name = null, $args = [])
    {
        $theme = get_active_theme();

        // Construct view name: themes.bxcode-theme.slug-name or themes.bxcode-theme.slug
        $viewBase = "themes.{$theme}.{$slug}";

        if ($name) {
            $viewName = "{$viewBase}-{$name}";
            // Fallback to base if specific doesn't exist? 
            // WP logic: try slug-name.php, then slug.php.
            // Blade logic: check if view exists.
            if (View::exists($viewName)) {
                echo View::make($viewName, $args)->render();
                return;
            }
        }

        // Try generic slug
        if (View::exists($viewBase)) {
            echo View::make($viewBase, $args)->render();
        }
    }
}

// Helper needed for shortcode regex (borrowed from WP)
if (!function_exists('get_shortcode_regex')) {
    function get_shortcode_regex($tagnames = null)
    {
        if (empty($tagnames)) {
            return ''; // Simplified for now
        }
        $tagregexp = join('|', array_map('preg_quote', $tagnames));

        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing bracket for escaping shortcodes: [[tag]]
    }
}

if (!function_exists('shortcode_parse_atts')) {
    function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = '/([\\w-]+)\\s*=\\s*"([^"]*)"(?:\\s|$)|([\\w-]+)\\s*=\\s*\'([^\']*)\'(?:\\s|$)|([\\w-]+)\\s*=\\s*([^\\s"\']+)(?:\\s|$)|"([^"]*)"(?:\\s|$)|(\S+)(?:\\s|$)/';
        $text = preg_replace("/[\\x{00a0}\\x{00b0}\\x{00c0}-\\x{00c6}\\x{00c0}-\\x{00d6}\\x{00d8}-\\x{00f0}\\x{00f8}-\\x{00ff}]/u", ' ', $text);

        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }
}

if (!function_exists('wp_nav_menu')) {
    /**
     * Display a navigation menu.
     * @param array $args
     */
    function wp_nav_menu($args = [])
    {
        $defaults = [
            'menu' => '', // Menu name, slug, or ID
            'theme_location' => '', // 'primary', 'footer', etc.
            'container' => 'div',
            'container_class' => '',
            'container_id' => '',
            'menu_class' => 'menu',
            'menu_id' => '',
            'echo' => true,
            'fallback_cb' => false,
            'items_wrap' => '<ul id="%1$s" class="%2$s">%3$s</ul>',
        ];

        $args = array_merge($defaults, $args);
        $menu = null;

        // 1. Try finding menu by Theme Location Setting
        if (!empty($args['theme_location'])) {
            $locationId = \App\Models\Setting::get("menu_location_" . $args['theme_location']);
            if ($locationId) {
                $menu = \App\Models\Menu::where('id', $locationId)
                    ->with([
                        'items' => function ($q) {
                            $q->orderBy('order');
                        }
                    ])->first();
            }
        }

        // 2. Fallback to direct 'menu' arg if no location found or not set
        if (!$menu && !empty($args['menu'])) {
            $menu = \App\Models\Menu::where('id', $args['menu'])
                ->orWhere('slug', $args['menu'])
                ->orWhere('name', $args['menu'])
                ->with([
                    'items' => function ($q) {
                        $q->orderBy('order');
                    }
                ])->first();
        }

        // 3. Last resort: Get first menu? Only if specifically requested or maybe just fail silently
        if (!$menu && empty($args['theme_location']) && empty($args['menu'])) {
            $menu = \App\Models\Menu::with([
                'items' => function ($q) {
                    $q->orderBy('order');
                }
            ])->first();
        }

        if (!$menu) {
            return;
        }

        // Build tree
        $items = $menu->items;
        $tree = build_menu_tree($items);

        $output = '';
        if ($args['container']) {
            $class = $args['container_class'] ? ' class="' . esc_attr($args['container_class']) . '"' : '';
            $id = $args['container_id'] ? ' id="' . esc_attr($args['container_id']) . '"' : '';
            $output .= '<' . $args['container'] . $id . $class . '>';
        }

        $items_html = walk_nav_menu_tree($tree, $args);

        $wrap_id = $args['menu_id'] ? $args['menu_id'] : 'menu-' . $menu->slug;
        $wrap_class = $args['menu_class'] ? $args['menu_class'] : 'menu';

        $output .= sprintf($args['items_wrap'], esc_attr($wrap_id), esc_attr($wrap_class), $items_html);

        if ($args['container']) {
            $output .= '</' . $args['container'] . '>';
        }

        if ($args['echo']) {
            echo $output;
        } else {
            return $output;
        }
    }
}

function build_menu_tree($items, $parentId = null)
{
    $branch = [];
    foreach ($items as $item) {
        if ($item->parent_id == $parentId) {
            $children = build_menu_tree($items, $item->id);
            if ($children) {
                $item->children = $children;
            }
            $branch[] = $item;
        }
    }
    return $branch;
}

function walk_nav_menu_tree($items, $args)
{
    $html = '';
    foreach ($items as $item) {
        $classes = [];
        $classes[] = 'menu-item';
        $classes[] = 'menu-item-' . $item->id;
        $classes[] = 'menu-item-type-' . ($item->type ?? 'custom');

        if (!empty($item->css_class)) {
            $classes[] = $item->css_class;
        }

        if (count($item->children) > 0) {
            $classes[] = 'menu-item-has-children';
        }

        // Add user-provided item_class from args if exists
        if (!empty($args['item_class'])) {
            $classes[] = $args['item_class']; // Be careful, this applies to ALL items. User might want it on <a> or <li>. WP puts 'menu_class' on UL, 'item_class' isn't standard WP but let's leave it if it was there? 
            // Actually, in the code I read, $args['item_class'] was passed in header-v2.
            // But looking at lines 40-45 of header-v2: 'item_class' => 'text-gray-600...'
            // The original code DID NOT use $args['item_class'] inside the walker! 
            // Wait, I should check if I missed it.
            // The original code I read (lines 250-275) did NOT use $args['item_class'].
            // However, header-v2 PASSES 'item_class'. 
            // So currently 'item_class' does NOTHING.
            // I should probably apply it to the <a> tag, or the <li> tag? 
            // In Tailwind menus, usually classes are on the <a>.
            // But the user asked for "menu i add class but theme wp_nav_menu not show".
            // This refers to the DB column 'css_class'.
            // I will focus on user request first.
        }

        $class_names = join(' ', array_filter($classes));
        $id_attr = 'menu-item-' . $item->id;

        $html .= '<li id="' . esc_attr($id_attr) . '" class="' . esc_attr($class_names) . '">';

        $url = $item->url;

        // Handle target attribute
        $target = !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';

        // Apply generic item_class to the link if provided (common pattern for Tailwind)
        $link_class = !empty($args['item_class']) ? ' class="' . esc_attr($args['item_class']) . '"' : '';

        $html .= '<a href="' . esc_attr($url) . '"' . $target . $link_class . '>' . e($item->title) . '</a>';

        if (count($item->children) > 0) {
            $html .= '<ul class="sub-menu">';
            $html .= walk_nav_menu_tree($item->children, $args);
            $html .= '</ul>';
        }
        $html .= '</li>';
    }
    return $html;
}

if (!function_exists('get_post')) {
    /**
     * Retrieve post data by ID or Slug.
     * @param int|string $id Post ID or Slug
     * @return \App\Models\Post|null
     */
    function get_post($id)
    {
        if (is_numeric($id)) {
            return \App\Models\Post::find($id);
        }
        return \App\Models\Post::where('slug', $id)->first();
    }
}

if (!function_exists('get_post_field')) {
    /**
     * Get a specific field from a post by ID or Slug.
     * Returns empty string if not found, making it safe for templates.
     * 
     * @param int|string $id
     * @param string $field
     * @return mixed
     */
    function get_post_field($id, $field = 'title')
    {
        $post = get_post($id);
        return $post ? $post->$field : '';
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
