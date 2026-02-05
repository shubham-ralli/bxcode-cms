<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Setting;
use App\Traits\ForceDelete;

class PluginController extends Controller
{
    use ForceDelete;

    public function index(Request $request)
    {
        $search = $request->get('s');
        $status = $request->get('status', 'all');
        $pluginsPath = resource_path('views/plugins');
        $allPlugins = [];

        if (File::isDirectory($pluginsPath)) {
            $pluginDirs = File::directories($pluginsPath);

            foreach ($pluginDirs as $pluginDir) {
                $pluginName = basename($pluginDir);

                // Read plugin.json if exists
                $configFile = $pluginDir . '/plugin.json';
                $config = [];

                if (file_exists($configFile)) {
                    $config = json_decode(file_get_contents($configFile), true) ?? [];
                }

                // Check if plugin is active (stored in settings)
                $isActive = Setting::get("plugin_{$pluginName}_active", '0') === '1';

                $allPlugins[] = [
                    'name' => $config['name'] ?? $pluginName,
                    'slug' => $pluginName,
                    'description' => $config['description'] ?? 'No description available.',
                    'version' => $config['version'] ?? '1.0.0',
                    'author' => $config['author'] ?? 'Unknown',
                    'path' => str_replace(base_path() . '/', '', $pluginDir),
                    'is_active' => $isActive,
                ];
            }
        }

        // 1. Calculate Counts (Before filtering)
        $counts = [
            'all' => count($allPlugins),
            'active' => count(array_filter($allPlugins, fn($p) => $p['is_active'])),
            'inactive' => count(array_filter($allPlugins, fn($p) => !$p['is_active'])),
        ];

        // 2. Filter by Status
        $filteredPlugins = $allPlugins;
        if ($status === 'active') {
            $filteredPlugins = array_filter($filteredPlugins, fn($p) => $p['is_active']);
        } elseif ($status === 'inactive') {
            $filteredPlugins = array_filter($filteredPlugins, fn($p) => !$p['is_active']);
        }

        // 3. Filter by Search
        if ($search) {
            $filteredPlugins = array_filter($filteredPlugins, function ($plugin) use ($search) {
                return stripos($plugin['name'], $search) !== false ||
                    stripos($plugin['description'], $search) !== false ||
                    stripos($plugin['slug'], $search) !== false;
            });
        }

        // 4. Pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $items = array_slice($filteredPlugins, $offset, $perPage);
        $plugins = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            count($filteredPlugins),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.plugins.index', compact('plugins', 'counts', 'status', 'search'));
    }

    public function bulk(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'ids' => 'required|array',
        ]);

        $action = $request->input('action');
        $slugs = $request->input('ids');
        $count = 0;

        foreach ($slugs as $slug) {
            $pluginPath = resource_path("views/plugins/{$slug}");

            if ($action === 'activate') {
                Setting::set("plugin_{$slug}_active", '1');

                // 1. Run Migrations
                $migrationPath = "resources/views/plugins/{$slug}/database/migrations";
                if (File::isDirectory(base_path($migrationPath))) {
                    try {
                        \Illuminate\Support\Facades\Artisan::call('migrate', [
                            '--path' => $migrationPath,
                            '--force' => true,
                        ]);
                    } catch (\Exception $e) {
                        // Log error but continue
                    }
                }

                // 2. Legacy install.php support
                if (file_exists($pluginPath . '/install.php')) {
                    try {
                        include_once $pluginPath . '/install.php';
                    } catch (\Exception $e) {
                        // Log error but continue
                    }
                }
                $count++;
            } elseif ($action === 'deactivate') {
                Setting::set("plugin_{$slug}_active", '0');
                $count++;
            } elseif ($action === 'delete') {
                // Ensure inactive first
                Setting::set("plugin_{$slug}_active", '0');

                // 1. Rollback Migrations (Clean up database)
                $migrationPath = "resources/views/plugins/{$slug}/database/migrations";
                if (File::isDirectory(base_path($migrationPath))) {
                    try {
                        // Reset all migrations for this specific path
                        \Illuminate\Support\Facades\Artisan::call('migrate:reset', [
                            '--path' => $migrationPath,
                            '--force' => true,
                        ]);
                    } catch (\Exception $e) {
                        // Log error but continue
                    }
                }

                // 2. Legacy uninstall.php support
                if (file_exists($pluginPath . '/uninstall.php')) {
                    try {
                        include_once $pluginPath . '/uninstall.php';
                    } catch (\Exception $e) {
                        // Log error but continue
                    }
                }

                if ($this->forceDelete($pluginPath)) {
                    $count++;
                    Setting::where('key', "plugin_{$slug}_active")->delete();
                }
            }
        }

        // Clear cache
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        $message = match ($action) {
            'activate' => "{$count} plugins activated.",
            'deactivate' => "{$count} plugins deactivated.",
            'delete' => "{$count} plugins deleted.",
        };

        return back()->with('success', $message);
    }

    public function activate($slug)
    {
        Setting::set("plugin_{$slug}_active", '1');

        $pluginPath = resource_path("views/plugins/{$slug}");

        // 1. Run Migrations
        $migrationPath = "resources/views/plugins/{$slug}/database/migrations";
        if (File::isDirectory(base_path($migrationPath))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate', [
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            } catch (\Exception $e) {
                return back()->with('error', 'Plugin activated but migrations failed: ' . $e->getMessage());
            }
        }

        // 2. Legacy install.php
        if (file_exists($pluginPath . '/install.php')) {
            try {
                include_once $pluginPath . '/install.php';
            } catch (\Exception $e) {
                return back()->with('error', 'Plugin activated but installation failed: ' . $e->getMessage());
            }
        }

        // 3. Verify Critical Tables (Specific for ACF)
        if ($slug === 'ACF' && !\Illuminate\Support\Facades\Schema::hasTable('custom_post_types')) {
            return back()->with('error', 'Plugin activated partially. Error: "custom_post_types" table was NOT created. Please ensure your plugin zip contains the migration file "2026_01_29_000000_create_custom_post_types_table.php".');
        }

        // Clear cache to reload plugins
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return back()->with('success', 'Plugin activated successfully. Cache cleared.');
    }

    public function deactivate($slug)
    {
        Setting::set("plugin_{$slug}_active", '0');

        // Clear cache
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        return back()->with('success', 'Plugin deactivated successfully. Cache cleared.');
    }

    public function destroy($slug)
    {
        // Only allow delete if plugin is deactivated
        $isActive = Setting::get("plugin_{$slug}_active", '0') === '1';

        if ($isActive) {
            return back()->with('error', 'Cannot delete an active plugin. Please deactivate it first.');
        }

        $pluginPath = resource_path("views/plugins/{$slug}");

        // 1. Rollback Migrations
        $migrationPath = "resources/views/plugins/{$slug}/database/migrations";
        if (File::isDirectory(base_path($migrationPath))) {
            try {
                \Illuminate\Support\Facades\Artisan::call('migrate:reset', [
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);
            } catch (\Exception $e) {
                // Log error but continue
            }
        }

        // 2. Legacy uninstall.php
        if (file_exists($pluginPath . '/uninstall.php')) {
            try {
                include_once $pluginPath . '/uninstall.php';
            } catch (\Exception $e) {
                // Log but continue
            }
        }

        if ($this->forceDelete($pluginPath)) {
            // Ensure it's treated as deleted
            // 1. Remove setting
            Setting::where('key', "plugin_{$slug}_active")->delete();

            // 2. Remove legacy database entry (if exists)
            try {
                \App\Models\Plugin::where('name', $slug)
                    ->orWhere('path', 'like', "%/{$slug}")
                    ->delete();
            } catch (\Exception $e) {
                // Ignore DB errors if table missing
            }

            // 3. Clear system cache
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            return back()->with('success', 'Plugin deleted successfully.');
        }

        return back()->with('error', 'Critical Error: Could not delete plugin folder. Please delete manually: ' . $pluginPath);
    }

    public function create()
    {
        return view('admin.plugins.create');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'plugin_file' => 'required|file|mimes:zip|max:10240', // Max 10MB
        ]);

        try {
            $file = $request->file('plugin_file');
            $pluginsPath = resource_path('views/plugins');

            // Ensure plugins directory exists
            if (!File::exists($pluginsPath)) {
                try {
                    @File::makeDirectory($pluginsPath, 0777, true);
                    @chmod($pluginsPath, 0777);
                } catch (\Exception $e) {
                    // Continue, maybe it exists or we can't create it (will fail later)
                }
            }

            // Try to find a writable temp path
            $tempPath = null;
            $candidates = [
                storage_path('app/temp/plugin_' . time()),
                public_path('temp/plugin_' . time()),
                sys_get_temp_dir() . '/plugin_' . time()
            ];

            foreach ($candidates as $path) {
                try {
                    $parent = dirname($path);
                    if (!File::exists($parent)) {
                        @File::makeDirectory($parent, 0777, true);
                    }
                    if (File::makeDirectory($path, 0777, true)) {
                        $tempPath = $path;
                        break;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$tempPath) {
                throw new \Exception("Could not create temporary directory. Please ensure 'storage' or 'public' directories are writable.");
            }

            // Extract ZIP
            $zip = new \ZipArchive;
            if ($zip->open($file->getRealPath()) === true) {
                $zip->extractTo($tempPath);
                $zip->close();
            } else {
                throw new \Exception('Failed to extract ZIP file.');
            }

            // Find the plugin folder (should be the only folder in temp)
            $extractedDirs = File::directories($tempPath);

            if (empty($extractedDirs)) {
                // Check if files are in root of ZIP
                $files = File::files($tempPath);
                if (!empty($files)) {
                    throw new \Exception('Invalid plugin structure. Plugin files must be in a folder.');
                }
                throw new \Exception('No plugin folder found in ZIP file.');
            }

            $pluginFolder = basename($extractedDirs[0]);
            $sourcePath = $extractedDirs[0];
            $destinationPath = $pluginsPath . '/' . $pluginFolder;

            // Check if plugin already exists
            if (File::isDirectory($destinationPath)) {
                File::deleteDirectory($tempPath);
                return back()->with('error', "Plugin '{$pluginFolder}' already exists. Please delete it first or upload a different plugin.");
            }

            // Move plugin to plugins directory
            $moved = File::moveDirectory($sourcePath, $destinationPath);

            // If move failed, try copy + delete (sometimes move fails across partitions/perms)
            if (!$moved || !File::isDirectory($destinationPath)) {
                // Try copy instead
                File::copyDirectory($sourcePath, $destinationPath);
                File::deleteDirectory($sourcePath);
            }

            // Final Verification
            if (!File::isDirectory($destinationPath)) {
                throw new \Exception("Failed to move plugin files to {$destinationPath}. Check folder permissions.");
            }

            // Clean up temp directory
            if (File::isDirectory($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            // Auto-activate: DISABLED
            // Setting::set("plugin_{$pluginFolder}_active", '1');

            // Install Hook: DISABLED (Runs on activation only)
            /*
            if (file_exists($destinationPath . '/install.php')) {
                try {
                    include_once $destinationPath . '/install.php';
                } catch (\Exception $e) {
                    // Log error
                }
            }
            */

            // Clear cache to force plugin reload
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Plugin '{$pluginFolder}' uploaded successfully!",
                    'redirect' => route('admin.plugins.index')
                ]);
            }

            return redirect()->route('admin.plugins.index')
                ->with('success', "Plugin '{$pluginFolder}' installed successfully! Please activate it from the list.");

        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && File::isDirectory($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Plugin installation failed: ' . $e->getMessage()
                ], 422);
            }

            return back()->with('error', 'Plugin installation failed: ' . $e->getMessage());
        }
    }
}
