<?php
/**
 * Theme Functions
 * Mimics WordPress functions.php
 */

// 1. Example: Add Shortcode
add_shortcode('hello', function ($atts, $content = null) {
    return "<div class='greeting'>Hello " . (!empty($content) ? $content : 'World') . "!</div>";
});

// 1. Dynamic Year (Liquid Variable)
// Usage: {{ current_year }}
add_liquid_variable('current_year', date('Y'));

// 2. Callout Block (Liquid Tag)
// Usage: {% callout type="warning" %} Content {% endcallout %}
register_liquid_tag('callout', \App\Liquid\Tags\CalloutTag::class);

/**
 * 3. Custom PHP Function for Blade Templates
 * Usage in Blade: {{ get_custom_theme_message() }}
 */
function get_custom_theme_message()
{
    return "This is a custom message from functions.blade.php!";
}

// 2. Example: Using get_template_part with args
// Usage in templates: <?php get_template_part('partials/card', null, ['title' => 'My Card']); ?>