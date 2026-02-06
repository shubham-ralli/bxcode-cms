<!-- System Head Injection -->
@stack('styles')
@stack('head')
@stack('head')
{!! get_setting('header_scripts') !!}

<!-- Custom CSS -->
<style type="text/css" id="bx-custom-css">
    {!! get_setting('custom_css') !!}
</style>

@php do_action('bx_head'); @endphp

@auth
    <link rel="stylesheet" href="{{ asset('css/custom-admin.css') }}">
@endauth