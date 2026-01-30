<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Setting;

class ThemeController extends Controller
{
    public function index(Request $request)
    {
        $themesPath = resource_path('views/themes');
        $allThemes = [];
        $activeTheme = Setting::get('active_theme', 'bxcode-theme');

        if (File::exists($themesPath)) {
            $directories = File::directories($themesPath);

            foreach ($directories as $dir) {
                $folderName = basename($dir);
                $stylePath = $dir . '/style.css';
                $themeData = [
                    'id' => $folderName,
                    'name' => $folderName,
                    'author' => 'Unknown',
                    'version' => '1.0',
                    'description' => '',
                    'active' => ($folderName === $activeTheme)
                ];

                if (File::exists($stylePath)) {
                    $content = File::get($stylePath);
                    if (preg_match('/Theme Name:(.*)$/m', $content, $matches))
                        $themeData['name'] = trim($matches[1]);
                    if (preg_match('/Author:(.*)$/m', $content, $matches))
                        $themeData['author'] = trim($matches[1]);
                    if (preg_match('/Version:(.*)$/m', $content, $matches))
                        $themeData['version'] = trim($matches[1]);
                    if (preg_match('/Description:(.*)$/m', $content, $matches))
                        $themeData['description'] = trim($matches[1]);
                }

                $allThemes[] = $themeData;
            }
        }

        // 1. Calculate Counts
        $counts = [
            'all' => count($allThemes),
            'active' => count(array_filter($allThemes, fn($t) => $t['active'])),
            'inactive' => count(array_filter($allThemes, fn($t) => !$t['active'])),
        ];

        // 2. Filter (Search only for now, Status if needed)
        $search = $request->get('s');
        $status = $request->get('status', 'all');

        $filteredThemes = $allThemes;
        if ($status === 'active') {
            $filteredThemes = array_filter($filteredThemes, fn($t) => $t['active']);
        } elseif ($status === 'inactive') {
            $filteredThemes = array_filter($filteredThemes, fn($t) => !$t['active']);
        }

        if ($search) {
            $filteredThemes = array_filter($filteredThemes, function ($theme) use ($search) {
                return stripos($theme['name'], $search) !== false ||
                    stripos($theme['description'], $search) !== false ||
                    stripos($theme['author'], $search) !== false;
            });
        }

        // 3. Pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $items = array_slice($filteredThemes, $offset, $perPage);
        $themes = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            count($filteredThemes),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.themes.index', compact('themes', 'counts', 'status', 'search'));
    }

    public function activate(Request $request)
    {
        $request->validate([
            'theme_id' => 'required|string'
        ]);

        $themeId = $request->theme_id;
        if (!File::exists(resource_path("views/themes/{$themeId}"))) {
            return back()->with('error', 'Theme not found.');
        }

        Setting::set('active_theme', $themeId);

        return back()->with('success', "Theme '{$themeId}' activated successfully.");
    }
}
