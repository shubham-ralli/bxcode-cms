<!DOCTYPE html>
<html lang="{{ get_setting('site_language', 'en') }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $siteIconId = \App\Models\Setting::get('site_icon');
        $siteIcon = $siteIconId ? \App\Models\Media::find($siteIconId) : null;
    @endphp

    @if($siteIcon)
        <link rel="icon" type="{{ $siteIcon->mime_type }}" href="{{ asset($siteIcon->path) }}">
        <link rel="apple-touch-icon" href="{{ asset($siteIcon->path) }}">
    @endif

    <link rel="stylesheet" href="{{ get_theme_file_uri('style.css') }}">

    {!! wp_head() !!}
</head>

<body id="{{ body_id($bodyId ?? '') }}"
    class="{{ body_class($bodyClass ?? '') }} bg-gray-50 flex flex-col min-h-screen">
    @include('partials.admin-bar')

    <!-- Site Header -->
    <header class="site-header">
        <div class="container header-container">
            <!-- Logo / Site Title -->
            <div class="site-branding">
                <a href="{{ url('/') }}">
                    {{ get_setting('site_title', 'BxCode') }}
                </a>
            </div>

            <!-- Desktop Navigation -->
            <nav class="main-navigation">
                {!! wp_nav_menu([
    'theme_location' => 'primary',
    'container' => false,
    'menu_class' => 'menu', // Use standard 'menu' class styled in style.css
    'item_class' => 'menu-link'
]) !!}
            </nav>

            <!-- Mobile Toggle (Hidden by default in CSS, shown in media query if needed) -->
            <div class="mobile-menu-toggle" style="display: none;">
                <button>Menu</button>
            </div>
        </div>
    </header>