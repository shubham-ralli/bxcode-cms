<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

if (!function_exists('get_active_theme')) {
    /**
     * Get the active theme name.
     * @return string
     */
    function get_active_theme()
    {
        // Avoid DB call if possible or cache it
        // Check for Setting model class existence to be safe during install
        if (class_exists('App\Models\Setting')) {
            return \App\Models\Setting::get('active_theme', 'bxcode-theme');
        }
        return 'bxcode-theme';
    }
}

if (!function_exists('get_theme_file_uri')) {
    /**
     * Get URL for a theme file.
     * @param string $file
     * @return string
     */
    function get_theme_file_uri($file = '')
    {
        $theme = get_active_theme();
        return route('theme.asset', ['theme' => $theme, 'file' => $file]);
    }
}

if (!function_exists('bx_head')) {
    /**
     * Render system head scripts/styles.
     * @return \Illuminate\Contracts\View\View
     */
    function bx_head()
    {
        return View::make('partials.bx_head');
    }
}

if (!function_exists('bx_footer')) {
    /**
     * Render system footer scripts/styles.
     * @return \Illuminate\Contracts\View\View
     */
    function bx_footer()
    {
        return View::make('partials.bx_footer');
    }
}

if (!function_exists('get_header')) {
    /**
     * Load header template.
     *
     * @param string|null $name
     * @param array $data
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    function get_header($name = null, $data = [])
    {
        $theme = get_active_theme();
        $viewName = 'themes.' . $theme . '.header' . ($name ? '-' . $name : '');
        return View::make($viewName, $data);
    }
}

if (!function_exists('get_footer')) {
    /**
     * Load footer template.
     *
     * @param string|null $name
     * @param array $data
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    function get_footer($name = null, $data = [])
    {
        $theme = get_active_theme();
        $viewName = 'themes.' . $theme . '.footer' . ($name ? '-' . $name : '');
        return View::make($viewName, $data);
    }
}

if (!function_exists('body_class')) {
    /**
     * Generate dynamic body classes based on the current page.
     *
     * @param string $class Additional classes to append.
     * @return string
     */
    function body_class($class = '')
    {
        $classes = [];

        if ($class) {
            $classes[] = $class;
        }

        // Home check
        if (Request::is('/')) {
            $classes[] = 'home';
        }

        // Check for shared Post object (from FrontendController)
        if (View::shared('post')) {
            $post = View::shared('post');
            $classes[] = $post->type . '-' . $post->id;
            $classes[] = 'type-' . $post->type;
        }

        // Check for specific routes/parameters
        $route = Route::current();

        if ($route) {
            // Get all parameters
            $params = $route->parameters();

            foreach ($params as $key => $value) {
                if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                    $classes[] = strtolower(class_basename($value)) . '-' . $value->id;
                    $classes[] = 'type-' . strtolower(class_basename($value));
                }
            }

            // Basic Route Name based class
            if (Route::currentRouteName()) {
                $classes[] = 'route-' . str_replace('.', '-', Route::currentRouteName());
            }
        }

        return implode(' ', array_unique($classes));
    }
}

if (!function_exists('body_id')) {
    /**
     * Generate dynamic body ID.
     *
     * @param string $id Optional ID override.
     * @return string
     */
    function body_id($id = '')
    {
        if ($id)
            return $id;

        // Check for shared Post object
        if (View::shared('post')) {
            $post = View::shared('post');
            return $post->type . '-' . $post->id;
        }

        $route = Route::current();
        if (!$route)
            return 'app-body';

        $params = $route->parameters();
        foreach ($params as $value) {
            if ($value instanceof \Illuminate\Database\Eloquent\Model) {
                return strtolower(class_basename($value)) . '-' . $value->id;
            }
        }

        if (Request::is('/')) {
            return 'home-page';
        }

        return 'page-body';
    }
}
if (!function_exists('add_liquid_variable')) {
    function add_liquid_variable($key, $value)
    {
        \App\Services\LiquidService::addGlobal($key, $value);
    }
}

if (!function_exists('register_liquid_tag')) {
    function register_liquid_tag($tag, $class)
    {
        \App\Services\LiquidService::registerTag($tag, $class);
    }
}

// Load core meta boxes
// TEMPORARILY DISABLED - Need to fix variable scope issues
// require_once __DIR__ . '/core_metaboxes.php';

// Load taxonomy system
// DISABLED - Causing 500 errors
// require_once __DIR__ . '/taxonomy_helpers.php';

if (!function_exists('get_setting')) {
    /**
     * Get a setting by key.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function get_setting($key, $default = null)
    {
        if (class_exists('App\Models\Setting')) {
            return \App\Models\Setting::get($key, $default);
        }
        return $default;
    }
}


if (!function_exists('get_media')) {
    /**
     * Get media object by ID.
     * @param int|null $id
     * @return \App\Models\Media|null
     */
    function get_media($id)
    {
        if (!$id)
            return null;
        if (class_exists('App\Models\Media')) {
            return \App\Models\Media::find($id);
        }
        return null;
    }
}



if (!function_exists('get_admin_prefix')) {
    /**
     * Get the Admin Panel URL prefix.
     * @return string
     */
    function get_admin_prefix()
    {
        // Avoid infinite loop if database is missing during install
        try {
            if (class_exists('App\Models\Setting')) {
                return \App\Models\Setting::get('admin_path', config('cms.admin_path', 'bx-admin'));
            }
        } catch (\Exception $e) {
            // Fallback if DB connection fails
        }
        return config('cms.admin_path', 'bx-admin');
    }
}
