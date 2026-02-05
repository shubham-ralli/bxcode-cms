<?php

namespace App\Traits;

use Illuminate\Support\Facades\File;

trait ForceDelete
{
    /**
     * Force delete a file or directory.
     *
     * @param string $path
     * @return bool
     */
    public function forceDelete($path)
    {
        if (!file_exists($path)) {
            return true;
        }

        // 1. Try standard Laravel/PHP delete
        try {
            // Fix permissions first
            @chmod($path, 0777);

            if (is_file($path)) {
                @unlink($path);
            } else {
                // Recursive chmod for directories
                $this->recursiveChmod($path);
                File::deleteDirectory($path);
            }
        } catch (\Exception $e) {
            // Continue to force methods
        }

        if (!file_exists($path)) {
            return true;
        }

        // 2. System specific force delete
        $escapedPath = escapeshellarg($path);

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows
            if (is_file($path)) {
                exec("del /f /q $escapedPath", $output, $returnVar);
            } else {
                exec("rmdir /s /q $escapedPath", $output, $returnVar);
            }
        } else {
            // Unix/Linux/Mac
            $currentUser = exec('whoami');
            \Illuminate\Support\Facades\Log::info("ForceDelete: Attempting to delete $path as user $currentUser");

            exec("rm -rf $escapedPath 2>&1", $output, $returnVar);

            \Illuminate\Support\Facades\Log::info("ForceDelete: rm -rf output: " . implode("\n", $output));
            \Illuminate\Support\Facades\Log::info("ForceDelete: Return var: $returnVar");
        }

        // 3. Final check
        if (file_exists($path)) {
            // Last resort: Try to rename to a trash folder
            $trashDir = storage_path('framework/trash');

            if (!file_exists($trashDir)) {
                @mkdir($trashDir, 0777, true);
            }

            $trashName = basename($path) . '_' . time();
            $trashPath = $trashDir . '/' . $trashName;

            if (@rename($path, $trashPath)) {
                \Illuminate\Support\Facades\Log::info("ForceDelete: Moved $path to $trashPath");
                return true;
            }

            \Illuminate\Support\Facades\Log::error("ForceDelete: Failed to delete $path and failed to move to trash.");
            return false;
        }

        return true;
    }

    /**
     * Recursively change permissions of a directory
     */
    private function recursiveChmod($path, $mode = 0777)
    {
        if (!is_dir($path)) {
            return @chmod($path, $mode);
        }

        $dir = new \DirectoryIterator($path);
        foreach ($dir as $item) {
            @chmod($item->getPathname(), $mode);
            if ($item->isDir() && !$item->isDot()) {
                $this->recursiveChmod($item->getPathname(), $mode);
            }
        }
    }
}
