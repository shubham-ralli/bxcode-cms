{!! get_header() !!}

<section class="category-header">
    <div class="container">
        <div class="category-title-section">
            <h1 class="category-title">{{ $tag->name }}</h1>
            @if($tag->description)
                <p class="category-description">{{ $tag->description }}</p>
            @endif
        </div>
    </div>
</section>

<main class="container mx-auto px-4 py-8">
    <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
        @forelse($posts as $post)

            <article class="list-post">
                <div class="list-post-image">
                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
                </div>
                <div class="list-post-content">
                    <div class="post-meta">
                        @if($post->categories->isNotEmpty())
                            <span class="category">{{ $post->categories->first()->name }}</span>
                        @endif
                        <span
                            class="date">{{ $post->published_at ? $post->published_at->format('F d, Y') : $post->created_at->format('F d, Y') }}</span>
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

        @empty
            <div class="col-span-full text-center py-12">
                <p class="text-gray-500 text-lg">No posts found in this archive.</p>
            </div>
        @endforelse
    </div>

    @php bx_posts_pagination($posts); @endphp
</main>

{!! get_footer() !!}