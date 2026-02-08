{!! get_header() !!}

<section class="category-header">
    <div class="container">
        <div class="category-title-section">
            <h1 class="category-title">Search Results for: {{ $searchQuery }}</h1>
            <p class="category-description">We found {{ $posts->total() }} results for your search.</p>
        </div>

        <div class="search-page-form mt-4 max-w-lg mx-auto">
            {!! get_search_form(false) !!}
        </div>
    </div>
</section>

<main class="container mx-auto px-4 py-8">
    @if($posts->count() > 0)
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
            @foreach($posts as $post)

                <article class="list-post">
                    @if($post->featured_image_url)
                        <div class="list-post-image">
                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
                        </div>
                    @endif
                    <div class="list-post-content">
                        <div class="post-meta">
                            @if($post->categories->isNotEmpty())
                                <span class="category">{{ $post->categories->first()->name }}</span>
                            @endif
                            <span class="date">{{ $post->published_at ? $post->published_at->format('F d, Y') : $post->created_at->format('F d, Y') }}</span>
                            <span class="read-time">{{ $post->read_time }}</span>
                        </div>
                        <h3 class="list-post-title">
                            <a href="{{ $post->url }}">{{ $post->title }}</a>
                        </h3>
                        <p class="list-post-excerpt">{{ Str::limit(strip_tags($post->excerpt ?? $post->content), 100) }}</p>
                        <div class="list-post-footer">
                            <div class="author-mini">
                                @if($post->author)
                                    <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->name }}">
                                    <span>{{ $post->author->name }}</span>
                                @endif
                            </div>
                            <a href="{{ $post->url }}" class="read-more">Read Article →</a>
                        </div>
                    </div>
                </article>

            @endforeach
        </div>

        @php bx_posts_pagination($posts); @endphp

    @else
        <div class="no-results py-12 text-center">
            <h2 class="text-2xl font-bold mb-4">Nothing Found</h2>
            <p class="text-gray-600 mb-8">Sorry, looking for something that isn't here.</p>
            <div class="max-w-md mx-auto">
                {!! get_search_form(false) !!}
            </div>
        </div>
    @endif
</main>

{!! get_footer() !!}