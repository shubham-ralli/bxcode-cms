<footer class="bg-gray-800 text-white py-6 mt-12">
    <div class="container mx-auto px-4 text-center">
        &copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_title', 'BxCode CMS') }}. All rights reserved.
    </div>
</footer>
{!! wp_footer() !!}
</body>

</html>