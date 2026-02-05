<footer class="site-footer">
    <div class="container">
        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('site_title', 'BxCode CMS') }}. All rights reserved.
            </p>
            <div class="footer-legal">
                <a href="#">Privacy Policy</a>
                <a href="#">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>

{!! wp_footer() !!}
</body>

</html>