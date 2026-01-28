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

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
