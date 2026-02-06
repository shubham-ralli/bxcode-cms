<?php

// Check if hooks logic exists
if (function_exists('add_action')) {

    // Register Theme Customizations
    add_action('customize_register', function ($customizer) {

        // Example: Add Footer Section
        $customizer->addSection('footer_settings', [
            'title' => 'Footer Settings',
            'priority' => 120,
            'description' => 'Customize the footer area.'
        ]);

        $customizer->addControl('footer_copyright_text', [
            'label' => 'Copyright Text',
            'section' => 'footer_settings',
            'type' => 'text',
            'default' => '© 2024 Your Company. All rights reserved.'
        ]);

        $customizer->addControl('footer_bg_color', [
            'label' => 'Footer Background Color', // In real generic control, this might be a 'color' input
            'section' => 'footer_settings',
            'type' => 'text',
            'description' => 'Enter hex code (e.g. #333333)'
        ]);


    });

}

if (function_exists('add_action')) {

    if (!function_exists('hook_vwoCodeto')) {
        function hook_vwoCodeto()
        {
            echo '<meta name="custom-meta" content="value shubham ralli">';
        }
    }
    add_action('bx_head', 'hook_vwoCodeto', 0);

    if (!function_exists('setup_theme_header')) {
        function setup_theme_header()
        {
            $siteIconId = get_setting('site_icon');
            if ($siteIconId) {
                $siteIcon = get_media($siteIconId);
                if ($siteIcon) {
                    echo '<link rel="icon" type="' . $siteIcon->mime_type . '" href="' . asset($siteIcon->path) . '">';
                    echo '<link rel="apple-touch-icon" href="' . asset($siteIcon->path) . '">';
                }
            }
            echo '<link rel="stylesheet" href="' . get_theme_file_uri('style.css') . '">';
        }
    }
    add_action('bx_head', 'setup_theme_header', 5);

    if (!function_exists('setup_theme_footer')) {
        function setup_theme_footer()
        {
            echo '<script src="' . get_theme_file_uri('script.js') . '"></script>';
        }
    }
    add_action('bx_footer', 'setup_theme_footer', 10);
}


