<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Load Core System Helpers
        if (file_exists(app_path('Helpers/CoreHelpers.php'))) {
            require_once app_path('Helpers/CoreHelpers.php');
        }
        if (file_exists(app_path('Helpers/WPHelpers.php'))) {
            require_once app_path('Helpers/WPHelpers.php');
        }
    }

    /**
     * Bootstrap any application services.
     */


    public function boot(): void
    {
        // Register Admin Components path
        \Illuminate\Support\Facades\Blade::anonymousComponentPath(resource_path('views/admin/components'), 'admin');

        \Illuminate\Pagination\Paginator::useTailwind();

        // Force Root URL if configured (Fixes "public" appearing in URLs)
        if (!empty(config('app.url')) && config('app.url') !== 'http://localhost') {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        // Load Custom Theme Functions (Active Theme)
        // We do this in boot() because it requires Database access
        try {
            // Load Core System Helpers manually if needed (though composer autoload is preferred)
            // if (file_exists(app_path('Helpers/WPHelpers.php'))) require_once app_path('Helpers/WPHelpers.php');

            if (function_exists('load_theme_functions')) {
                load_theme_functions();
            } else {
                // Fallback if helper not yet loaded via composer
                $activeTheme = 'bxcode-theme';
                if (class_exists('App\Models\Setting')) {
                    try {
                        $activeTheme = \App\Models\Setting::get('active_theme', 'bxcode-theme');
                    } catch (\Throwable $e) {
                    }
                }
                if (file_exists(resource_path("views/themes/{$activeTheme}/functions.blade.php"))) {
                    require_once resource_path("views/themes/{$activeTheme}/functions.blade.php");
                }
            }
        } catch (\Throwable $e) {
            // Database not ready or migration running
        }
    }
}
