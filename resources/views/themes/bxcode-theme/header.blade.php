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
    <header class="bg-white border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo / Site Title -->
                <div class="flex-shrink-0">
                    <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-600">
                        {{ get_setting('site_title', 'BxCode') }}
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <nav class="hidden md:block">
                    {!! wp_nav_menu([
    'theme_location' => 'primary',
    'container' => false,
    'menu_class' => 'flex space-x-6',
    'item_class' => 'menu-link'
]) !!}
                </nav>

                <!-- Mobile Menu Button (Hamburger) -->
                <div class="md:hidden">
                    <button type="button"
                        class="text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>