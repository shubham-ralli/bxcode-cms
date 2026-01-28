<!-- System Head Injection -->
@stack('styles')
@stack('head')
@stack('head')
{!! \App\Models\Setting::get('header_scripts') !!}
@php do_action('wp_head'); @endphp

@auth
    <link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
@endauth