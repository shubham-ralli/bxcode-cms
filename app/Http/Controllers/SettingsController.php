<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Post;

class SettingsController extends Controller
{
    public function general()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.general', compact('settings'));
    }

    public function reading()
    {
        $settings = Setting::all()->pluck('value', 'key');
        // Pages needed for homepage selection
        $pages = Post::where('type', 'page')->where('status', 'publish')->get();
        return view('admin.settings.reading', compact('settings', 'pages'));
    }

    public function permalink()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('admin.settings.permalink', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::set($key, $value);
        }

        // Check if site_url is being updated and sync to .env
        if (isset($data['site_url'])) {
            $this->updateEnvironmentFile('APP_URL', $data['site_url']);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    private function updateEnvironmentFile($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {
            $value = '"' . trim($value) . '"'; // Quote value for safety

            if (strpos(file_get_contents($path), $key . '=') === false) {
                // Key missing, append
                file_put_contents($path, PHP_EOL . $key . '=' . $value . PHP_EOL, FILE_APPEND);
            } else {
                // Key exists, replace
                file_put_contents($path, preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$value}",
                    file_get_contents($path)
                ));
            }
        }
    }
}
