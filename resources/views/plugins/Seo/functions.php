<?php

use Illuminate\Support\Facades\Route;

// Only load if plugin is active
if (!plugin_is_active('Seo')) {
    return;
}

// 1. Add Admin Menu (Easy Mode)
add_admin_menu(
    'SEO Manager',      // Label
    'seo',              // Slug
    url('lp-admin/seo'),// URL
    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>',
    90                  // Order
);


// Add Settings Submenu
add_admin_submenu('seo', 'Settings', url('lp-admin/seo/settings'), 10);

// Add Tools Submenu
add_admin_submenu('seo', 'Tools', url('lp-admin/seo/tools'), 20);

// 2. Hook into Post Editor
// 2. Hook into Post Editor (Meta Box)
add_action('admin_post_add', function ($postOrContext) {
    $currentPostType = null;
    $postStub = null;

    if (is_object($postOrContext)) {
        $currentPostType = $postOrContext->type ?? null;
        $postStub = $postOrContext;
    } elseif (is_array($postOrContext)) {
        $currentPostType = $postOrContext['post_type'] ?? null;
        // Mock post object if needed or fetch
    }

    if (!$currentPostType) {
        $currentPostType = request('type', 'post');
    }

    // Register SEO Meta Box
    add_meta_box(
        'seo_manager_box',
        'SEO Manager',
        function ($post) {
            echo plugin_view('seo::seo-box', compact('post'));
        },
        $currentPostType, // Show on all types? Or at least the current one.
        'main',
        100 // High priority (low number = top? check render_meta_boxes sort. Usually WP is low=top, but let's check helper.)
        // Actually, let's look at render_meta_boxes. But usually 100 is "bottom" if ascending, or top if descending.
        // ACF uses 'menu_order' which defaults to 10.
        // If I want SEO to be last, I should use a high number.
    );
});

// 3. Hook into WP Head
add_action('wp_head', function () {
    // Try to get current post from View Share or Route
    $shared = \Illuminate\Support\Facades\View::getShared();
    $post = $shared['post'] ?? null;

    // If not in shared (e.g. some themes might not share it), try route
    if (!$post) {
        $routeParam = request()->route('slug');
        if ($routeParam) {
            // This is risky if slug isn't unique or mapped to post, but generic fallback
            // Better to rely on View Share being standard in this CMS.
        }
    }

    if ($post && $post instanceof \App\Models\Post) {
        $seo = $post->seo;

        // 1. Title Logic
        $siteTitle = \App\Models\Setting::get('site_title', 'BxCode');

        if (!empty($seo->meta_title)) {
            // Use custom SEO title
            $title = $seo->meta_title;
        } else {
            // Use template from settings based on post type
            $templateKey = $post->type === 'page' ? 'seo_page_title_template' : 'seo_post_title_template';
            $template = \App\Models\Setting::get($templateKey, '%%title%% %%sep%% %%sitename%%');
            $title = $template;
        }

        // Variable Replacements
        $replacements = [
            '%%title%%' => $post->title,
            '%%sitename%%' => $siteTitle,
            '%%site_title%%' => $siteTitle,
            '%%sep%%' => '-',
            '%%year%%' => date('Y'),
            '%%date%%' => $post->created_at->format('F d, Y'),
            '%%excerpt%%' => \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?? $post->content), 150),
        ];
        $title = str_replace(array_keys($replacements), array_values($replacements), $title);

        // 2. Description Logic
        if (!empty($seo->meta_description)) {
            // Use custom meta description
            $desc = $seo->meta_description;
        } else {
            // Use template from settings
            $descTemplateKey = $post->type === 'page' ? 'seo_page_description_template' : 'seo_post_description_template';
            $descTemplate = \App\Models\Setting::get($descTemplateKey, '%%excerpt%%');
            $desc = $descTemplate ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?? $post->content), 150);
        }
        $desc = str_replace(array_keys($replacements), array_values($replacements), $desc);
        $robotsIndex = $seo->robots_index ?? 'index';
        $robotsFollow = $seo->robots_follow ?? 'follow';
        $canonical = !empty($seo->canonical_url) ? $seo->canonical_url : url()->current();

        echo "\n<!-- SEO Plugin Output -->\n";
        echo "<title>" . e($title) . "</title>\n"; // $title already contains site title if needed
        echo "<meta name=\"description\" content=\"" . e($desc) . "\">\n";
        echo "<meta name=\"robots\" content=\"{$robotsIndex}, {$robotsFollow}\">\n";
        echo "<link rel=\"canonical\" href=\"" . e($canonical) . "\">\n";

        // Open Graph Tags
        $siteTitle = \App\Models\Setting::get('site_title', 'BxCode CMS');
        $ogType = 'article'; // Default to article for posts/pages
        $ogImage = $post->featured_image_url ?? asset('images/default-og.jpg'); // Fallback?

        echo "<meta property=\"og:locale\" content=\"en_US\" />\n";
        echo "<meta property=\"og:type\" content=\"{$ogType}\" />\n";
        echo "<meta property=\"og:title\" content=\"" . e($title) . "\" />\n";
        echo "<meta property=\"og:description\" content=\"" . e($desc) . "\" />\n";
        echo "<meta property=\"og:url\" content=\"" . e($canonical) . "\" />\n";
        echo "<meta property=\"og:site_name\" content=\"" . e($siteTitle) . "\" />\n";
        echo "<meta property=\"article:modified_time\" content=\"" . $post->updated_at->toIso8601String() . "\" />\n";

        if ($post->featured_image) {
            echo "<meta property=\"og:image\" content=\"" . e($post->featured_image_url) . "\" />\n";
        }

        echo "<!-- /SEO Plugin Output -->\n";
    }
});

// 4. Admin Routes
// Main SEO route redirects to settings
add_plugin_admin_route('seo', function () {
    return redirect(url('lp-admin/seo/settings'));
});

// Tools route (robots.txt & sitemap)
add_plugin_admin_route('seo/tools', function () {
    $robotsContent = plugin_get_setting('robots_txt', "User-agent: *\nDisallow:");
    return plugin_view('seo::admin', compact('robotsContent'));
});

add_plugin_admin_route('seo/save', function () {
    // Sitemap Toggle
    // Use input() instead of has(). 
    // If unchecked: input is null (falsy) -> '0'.
    // If checked: input is '1' (truthy) -> '1'.
    $val = request()->input('sitemap_enabled');
    $sitemapEnabled = $val ? '1' : '0';

    plugin_save_setting('sitemap_enabled', $sitemapEnabled);

    // Robots.txt Toggle
    $val2 = request()->input('robots_enabled');
    $robotsEnabled = $val2 ? '1' : '0';
    plugin_save_setting('robots_enabled', $robotsEnabled);

    // Robots.txt Content
    plugin_save_setting('robots_txt', plugin_input('robots_content'));

    return plugin_redirect_back("SEO settings updated. (Sitemap: " . ($sitemapEnabled == '1' ? 'On' : 'Off') . ", Robots: " . ($robotsEnabled == '1' ? 'On' : 'Off') . ")");
}, 'admin.seo.update', ['POST']);

add_plugin_admin_route('seo-test', function () {
    return 'SEO ROUTE WORKING';
});

// 3. Frontend Routes
add_plugin_frontend_route('/sitemap.xml', function () {
    if (plugin_get_setting('sitemap_enabled', '0') != '1') {
        abort(404);
    }
    $posts = \App\Models\Post::where('status', 'publish')->get();
    // Now 'seo::sitemap' because sitemap.blade.php is in the root
    return response()->view('seo::sitemap', compact('posts'))->header('Content-Type', 'text/xml');
});

add_plugin_frontend_route('/robots.txt', function () {
    if (plugin_get_setting('robots_enabled', '0') != '1') {
        abort(404);
    }
    $content = plugin_get_setting('robots_txt', "User-agent: *\nDisallow:");
    return response($content)->header('Content-Type', 'text/plain');
});