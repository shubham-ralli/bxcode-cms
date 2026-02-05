{!! get_header() !!}

<header class="bg-white shadow py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900">
            {{ $archiveTitle ?? \App\Models\Setting::get('site_title') }}

        </h1>
    </div>
</header>

<main class="container mx-auto px-4 py-8">
    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)
            <article class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300">
                @if($post->featured_image)
                    <img src="{{ Storage::url($post->featured_image) }}" alt="{{ $post->title }}"
                        class="w-full h-48 object-cover">
                @endif
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-2">
                        <a href="{{ $post->url }}" class="text-indigo-600 hover:text-indigo-800">
                            {{ $post->title }}
                        </a>
                    </h2>
                    <div class="text-gray-600 mb-4 text-sm">
                        {{ Str::limit(strip_tags($post->content), 100) }}
                    </div>
                    <a href="{{ $post->url }}"
                        class="inline-block px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition-colors">
                        Read More
                    </a>
                </div>
            </article>
        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No posts found in this archive.</p>
            </div>
        @endforelse
    </div>

    @if($posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-8">
            {{ $posts->links() }}
        </div>
    @endif
</main>

{!! get_footer() !!}