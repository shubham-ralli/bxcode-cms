<?php

namespace App\Services;

use App\Models\Plugin;
use Illuminate\Support\Facades\File;

class PluginLoader
{
    protected $pluginPath;

    public function __construct()
    {
        // Plugins are now in resources/views/plugins due to previous refactor request
        $this->pluginPath = resource_path('views/plugins');
    }

    public function discover()
    {
        if (!File::exists($this->pluginPath)) {
            File::makeDirectory($this->pluginPath, 0755, true);
        }

        $directories = File::directories($this->pluginPath);

        foreach ($directories as $dir) {
            $name = basename($dir);
            $class = "Plugins\\{$name}\\{$name}ServiceProvider";

            // Standard file path: PluginName/PluginNameServiceProvider.php
            $providerPath = $dir . "/{$name}ServiceProvider.php";

            $headers = $this->getFileHeaders($providerPath);

            // Create or Update DB Record
            Plugin::updateOrCreate(
                ['name' => $name],
                [
                    'path' => "resources/views/plugins/{$name}",
                    'class' => $class,
                    // If plugin exists, keep its active state, else default to false.
                    // 'is_active' is not updated here to prevent deactivating on discovery scan.
                    'description' => $headers['Description'] ?? null,
                    'version' => $headers['Version'] ?? null,
                    'author' => $headers['Author'] ?? null,
                ]
            );
        }
    }

    /**
     * Parse WP-Style Plugin Headers from a file.
     */
    protected function getFileHeaders($file)
    {
        if (!File::exists($file)) {
            return [];
        }

        $content = File::get($file);
        // Limit to first 8kb to save memory
        $content = substr($content, 0, 8192);

        $headers = [
            'Plugin Name' => '/Plugin Name:\s*(.*)$/mi',
            'Plugin URI' => '/Plugin URI:\s*(.*)$/mi',
            'Description' => '/Description:\s*(.*)$/mi',
            'Version' => '/Version:\s*(.*)$/mi',
            'Author' => '/Author:\s*(.*)$/mi',
            'Author URI' => '/Author URI:\s*(.*)$/mi',
        ];

        $results = [];

        foreach ($headers as $key => $regex) {
            if (preg_match($regex, $content, $matches)) {
                $results[$key] = trim($matches[1]);
            } else {
                $results[$key] = null;
            }
        }

        return $results;
    }
}
