{!! get_header() !!}

<div class="container py-20 px-4 text-center">
    <div class="error-404">
        <h1 class="text-9xl font-bold text-gray-800 mb-4">404 3</h1>
        <h2 class="text-3xl font-semibold text-gray-600 mb-6">Page Not Found</h2>
        <p class="text-xl text-gray-500 mb-8">Oops! The page you are looking for does not exist. It might have been
            moved or deleted.</p>
        <a href="{{ url('/') }}"
            class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">Go
            Back Home</a>
    </div>
</div>

{!! get_footer() !!}