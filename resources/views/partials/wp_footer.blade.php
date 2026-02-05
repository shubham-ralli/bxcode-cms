<!-- System Footer Injection -->
@stack('scripts')
@stack('footer')
{!! \App\Models\Setting::get('footer_scripts') !!}
<!-- End System Footer Injection -->