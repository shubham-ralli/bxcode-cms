<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SeoSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            // Posts
            'seo_post_title_template' => Setting::get('seo_post_title_template', '%%title%% %%sep%% %%sitename%%'),
            'seo_post_description_template' => Setting::get('seo_post_description_template', '%%excerpt%%'),
            'seo_post_show_in_search' => Setting::get('seo_post_show_in_search', '1'),

            // Pages
            'seo_page_title_template' => Setting::get('seo_page_title_template', '%%title%% %%sep%% %%sitename%%'),
            'seo_page_description_template' => Setting::get('seo_page_description_template', ''),
            'seo_page_show_in_search' => Setting::get('seo_page_show_in_search', '1'),

            // Categories
            'seo_category_title_template' => Setting::get('seo_category_title_template', '%%title%% Archives %%sep%% %%sitename%%'),
            'seo_category_description_template' => Setting::get('seo_category_description_template', 'Browse our %%title%% archives.'),
            'seo_category_show_in_search' => Setting::get('seo_category_show_in_search', '1'),
        ];

        return view('plugins.Seo.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $fields = [
            'seo_post_title_template',
            'seo_post_description_template',
            'seo_post_show_in_search',
            'seo_page_title_template',
            'seo_page_description_template',
            'seo_page_show_in_search',
            'seo_category_title_template',
            'seo_category_description_template',
            'seo_category_show_in_search',
        ];

        foreach ($fields as $field) {
            $value = $request->input($field, '');
            Setting::set($field, $value);
        }

        return redirect()->route('admin.seo.settings')->with('success', 'SEO settings saved successfully');
    }
}
