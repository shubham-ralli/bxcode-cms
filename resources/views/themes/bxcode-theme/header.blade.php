<!DOCTYPE html>
<html lang="{{ get_setting('site_language', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {!! bx_head() !!}
</head>

<body id="{{ body_id($bodyId ?? '') }}"
    class="{{ body_class($bodyClass ?? '') }} bg-gray-50 flex flex-col min-h-screen">
    @include('partials.admin-bar')


    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="{{ url('/') }}" style="text-decoration: none; color: inherit;">
                        @php 
                                                        $siteLogo = \App\Models\Setting::get('site_logo');
                            $displayHeaderText = \App\Models\Setting::get('display_header_text', true);
                        @endphp

                                                   
@if($siteLogo)
    <img src="{{ $siteLogo }}" alt="{{ get_setting('site_title') }}" class="custom-logo" style="max-height: 80px; width: auto; display: block; margin-bottom: 10px;">
@endif
                    
                        @if($displayHeaderText)
                            <span class="logo-text" style="display:block;">{{ get_setting('site_title', 'BxCode') }}</span>
                            <span class="page-subtitle" style="display:block;">{{ get_setting('tagline', '') }}</span>
                        @else
                            <span class="logo-text" style="display:none;">{{ get_setting('site_title', 'BxCode') }}</span>
                            <span class="page-subtitle" style="display:none;">{{ get_setting('tagline', '') }}</span>
                        @endif
                    </a>
                </div>

                <nav class="main-nav">
                    {!! bx_nav_menu([
    'theme_location' => 'primary',
    'container' => false,
    'menu_class' => 'nav-list',
    'item_class' => 'menu-link'
]) !!}
                </nav>

            </div>
        </div>
    </header>