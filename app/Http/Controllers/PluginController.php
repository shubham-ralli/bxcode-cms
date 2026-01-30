<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Setting;

class PluginController extends Controller
{
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
            if ($action === 'activate') {
                Setting::set("plugin_{$slug}_active", '1');
                $count++;
            } elseif ($action === 'deactivate') {
                Setting::set("plugin_{$slug}_active", '0');
                $count++;
            } elseif ($action === 'delete') {
                // Determine path and delete
                $pluginPath = resource_path("views/plugins/{$slug}");
                // Ensure inactive first
                Setting::set("plugin_{$slug}_active", '0');

                if (File::isDirectory($pluginPath)) {
                    try {
                        @chmod($pluginPath, 0777);
                        if (File::deleteDirectory($pluginPath)) {
                            $count++;
                            // Clean up tables
                            Setting::where('key', "plugin_{$slug}_active")->delete();
                        } else {
                            // Try force delete
                            exec("rm -rf " . escapeshellarg($pluginPath));
                            if (!File::isDirectory($pluginPath)) {
                                $count++;
                                Setting::where('key', "plugin_{$slug}_active")->delete();
                            }
                        }
                    } catch (\Exception $e) {
                    }
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

        // Try to fix permissions first (chmod 777)
        try {
            if (File::isDirectory($pluginPath)) {
                @chmod($pluginPath, 0777);
                // Also chmod all inner files if possible would be better, but start with folder
            }
        } catch (\Exception $e) {
        }

        // Try deleting using Laravel File
        if (File::isDirectory($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }

        // Double check if it still exists (Permissions issue?)
        if (File::isDirectory($pluginPath)) {
            // Force delete using shell command
            $escapedPath = escapeshellarg($pluginPath);
            exec("rm -rf {$escapedPath}");
        }

        // Verify one last time
        if (File::isDirectory($pluginPath)) {
            // It just won't die.
            return back()->with('error', 'Critical Error: Could not delete plugin folder. Please delete manually: ' . $pluginPath);
        }

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

            // Auto-activate the plugin (Set to 1)
            Setting::set("plugin_{$pluginFolder}_active", '1');

            // Clear cache to force plugin reload
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');

            return redirect()->route('admin.plugins.index')
                ->with('success', "Plugin '{$pluginFolder}' installed and activated successfully!");

        } catch (\Exception $e) {
            // Clean up on error
            if (isset($tempPath) && File::isDirectory($tempPath)) {
                File::deleteDirectory($tempPath);
            }

            return back()->with('error', 'Plugin installation failed: ' . $e->getMessage());
        }
    }
}
