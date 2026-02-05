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


    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="{{ url('/') }}">
                        <span class="logo-text">{{ get_setting('site_title', 'BxCode') }}</span>
                    </a>
                    <span class="page-subtitle">{{ get_setting('tagline', '') }}</span>
                </div>

                <nav class="main-nav">
                    {!! wp_nav_menu([
    'theme_location' => 'primary',
    'container' => false,
    'menu_class' => 'nav-list',
    'item_class' => 'menu-link'
]) !!}
                </nav>

            </div>
        </div>
    </header>