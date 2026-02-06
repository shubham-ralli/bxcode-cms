{!! get_header() !!}


<main class="container margin-auto py-main box-sizing">
    <div class="post-grid">
        @forelse($posts as $post)
            <article class="post-card">
                @if($post->featured_image)
                    <div class="post-image-wrapper">
                        <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="post-image">
                    </div>

                @endif
                <div class="post-content">
                    <h2 class="post-title">
                        <a href="{{ $post->url }}">{{ $post->title }}</a>
                    </h2>
                    <div class="post-excerpt">
                        {{ Str::limit(strip_tags($post->content), 120) }}
                    </div>
                    <a href="{{ $post->url }}" class="read-more-btn">Read More</a>
                </div>
            </article>
        @empty
            <div class="no-posts">
                <p>No posts found.</p>
            </div>
        @endforelse
    </div>
</main>

{!! get_footer() !!}