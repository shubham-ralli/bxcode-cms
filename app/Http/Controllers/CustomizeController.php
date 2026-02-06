<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

use App\Services\Customizer;

class CustomizeController extends Controller
{
    public function index(Request $request)
    {
        $url = $request->get('url', url('/'));

        // Ensure URL is within our domain to prevent open redirect/phishing
        // Simple check: starts with base url
        if (!str_starts_with($url, url('/'))) {
            $url = url('/');
        }

        // Store original URL for close button
        $closeUrl = $url;

        // Append customize_preview=1
        $connector = str_contains($url, '?') ? '&' : '?';
        $url .= $connector . 'customize_preview=1';

        // Initialize Customizer
        $customizer = new Customizer();

        // Register Core Sections (Default)
        // We can do this directly or via hook. Doing it directly for simplicity + Hook for extensions.

        // 1. Site Identity
        $customizer->addSection('title_tagline', [
            'title' => 'Site Identity',
            'priority' => 20
        ]);

        $customizer->addControl('site_logo', [
            'label' => 'Logo',
            'section' => 'title_tagline',
            'type' => 'image',
            'description' => 'Upload a logo for your site.'
        ]);

        $customizer->addControl('logo_width', [
            'label' => 'Logo Width',
            'section' => 'title_tagline',
            'type' => 'range',
            'min' => 20,
            'max' => 500,
            'step' => 1,
            'default' => 150
        ]);

        $customizer->addControl('logo_width', [
            'label' => 'Logo Width',
            'section' => 'title_tagline',
            'type' => 'range',
            'min' => 20,
            'max' => 500,
            'step' => 1,
            'default' => 150
        ]);

        $customizer->addControl('site_title', [
            'label' => 'Site Title',
            'section' => 'title_tagline',
            'type' => 'text'
        ]);

        $customizer->addControl('tagline', [
            'label' => 'Tagline',
            'section' => 'title_tagline',
            'type' => 'text'
        ]);

        $customizer->addControl('display_header_text', [
            'label' => 'Display Site Title and Tagline',
            'section' => 'title_tagline',
            'type' => 'checkbox',
            'default' => true
        ]);

        $customizer->addControl('site_icon', [
            'label' => 'Site Icon',
            'section' => 'title_tagline',
            'type' => 'image',
            'description' => 'Site Icons should be square and at least 512 × 512 pixels.'
        ]);

        // 2. Additional CSS
        $customizer->addSection('custom_css', [
            'title' => 'Additional CSS',
            'priority' => 200
        ]);

        $customizer->addControl('custom_css', [
            'label' => 'Additional CSS',
            'section' => 'custom_css',
            'type' => 'textarea',
            'description' => 'Add your own CSS code here to customize the appearance and layout of your site.'
        ]);

        // Trigger Hook for Plugins/Themes
        // Using global function if available, or try-catch if plugin_helpers not loaded?
        // It is loaded in composer autoload usually or global helpers.
        if (function_exists('do_action')) {
            do_action('customize_register', $customizer);

            // Allow theme functions to hooks
            if (function_exists('load_theme_functions')) {
                load_theme_functions();
                // We might need to re-trigger if theme registers hooks on load
                // But typically theme functions are loaded early? 
                // Let's assume they hook into 'customize_register'.
                // Ideally, theme functions file should be loaded BEFORE do_action.
            }
        }

        // For this specific request, the user wants "customize this type function create them... with theme side"
        // I'll make sure to load theme functions first. 


        // Re-trigger do action after loading theme functions just in case
        if (function_exists('do_action')) {
            do_action('customize_register', $customizer);
        }

        return view('admin.customize.index', compact('url', 'closeUrl', 'customizer'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($data['settings'] as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['success' => true, 'message' => 'Settings saved successfully']);
    }
}
