<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Setting;

class ThemeController extends Controller
{
    public function index()
    {
        $themesPath = resource_path('views/themes');
        $themes = [];
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

                $themes[] = $themeData;
            }
        }

        return view('admin.themes.index', compact('themes'));
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
