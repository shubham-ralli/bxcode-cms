<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Plugin;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class PluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Dynamic Autoloader for Plugins
        spl_autoload_register(function ($class) {
            // Only handle Plugin classes
            if (strpos($class, 'Plugins\\') === 0) {
                // Class: Plugins\Seo\Http\Controllers\SeoController
                // File: resources/views/plugins/Seo/Http/Controllers/SeoController.php

                // Remove prefix
                $relative = str_replace('Plugins\\', '', $class); // Seo\Http\Controllers\SeoController
                $parts = explode('\\', $relative);
                $pluginName = array_shift($parts); // Seo

                // Construct Path
                $pluginPath = resource_path("views/plugins/{$pluginName}");
                $remainder = implode('/', $parts); // Http/Controllers/SeoController

                $file = "{$pluginPath}/{$remainder}.php";

                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });
    }

    public function boot(): void
    {
        // 0. Load Global Helpers
        $helperFile = app_path('Helpers/plugin_helpers.php');
        if (file_exists($helperFile)) {
            require_once $helperFile;
        }

        // 2. Auto-discover plugins from filesystem
        $pluginsPath = resource_path('views/plugins');

        if (File::isDirectory($pluginsPath)) {
            $pluginDirs = File::directories($pluginsPath);

            foreach ($pluginDirs as $pluginDir) {
                $pluginName = basename($pluginDir);

                // Check if plugin is active (default to active for backward compatibility)
                $isActive = true;

                // Check if DB is configured (avoid 'forge' default or empty)
                $dbName = config('database.connections.mysql.database');
                if ($dbName && $dbName !== 'forge') {
                    try {
                        // Wrap in try-catch to support running artisan/composer when DB is not configured
                        $isActive = \App\Models\Setting::get("plugin_{$pluginName}_active", '1') === '1';
                    } catch (\Throwable $e) {
                        $isActive = true;
                    }
                }

                if (!$isActive) {
                    continue; // Skip inactive plugins
                }

                // Check if plugin has a plugin.json or functions.php
                $configFile = $pluginDir . '/plugin.json';
                $functionsFile = $pluginDir . '/functions.php';

                if (file_exists($functionsFile) || file_exists($configFile)) {
                    // A. Auto-Register Views
                    $this->loadViewsFrom($pluginDir, strtolower($pluginName));

                    // B. Load functions.php (WordPress Style)
                    if (file_exists($functionsFile)) {
                        require_once $functionsFile;
                    }

                    // C. Load ServiceProvider (Laravel Style) if exists
                    $providerFile = $pluginDir . "/{$pluginName}ServiceProvider.php";
                    if (file_exists($providerFile)) {
                        require_once $providerFile;

                        $providerClass = "Plugins\\{$pluginName}\\{$pluginName}ServiceProvider";
                        if (class_exists($providerClass)) {
                            $this->app->register($providerClass);
                        }
                    }
                }
            }
        }

        // 3. Legacy support: Load plugins from database (if table exists)
        try {
            if (Schema::hasTable('plugins')) {
                $dbPlugins = Plugin::where('is_active', true)->get();

                foreach ($dbPlugins as $plugin) {
                    $pluginPath = base_path($plugin->path);
                    $pluginSlug = strtolower($plugin->name);

                    // Only load if not already loaded from filesystem
                    if (File::isDirectory($pluginPath)) {
                        // Views already registered above via filesystem scan

                        // Load functions.php if not already loaded
                        $functionsFile = $pluginPath . '/functions.php';
                        if (file_exists($functionsFile)) {
                            require_once $functionsFile;
                        }

                        // Load ServiceProvider if not already loaded
                        $pluginName = $plugin->name;
                        $providerFile = $pluginPath . "/{$pluginName}ServiceProvider.php";

                        if (file_exists($providerFile)) {
                            require_once $providerFile;

                            if (class_exists($plugin->class)) {
                                $this->app->register($plugin->class);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail if DB is offline or table missing
        }
    }
}
