{!! get_header() !!}



<article class="article">
    <div class="article-header">
        <div class="container-narrow">
            <div class="article-meta">
                <a href="list.html?category=technology" class="article-category">Technology</a>
                <span
                    class="article-date">{{ $post->published_at ? $post->published_at->format('F d, Y') : $post->created_at->format('F d, Y') }}
                </span>
                <span class="article-read-time">8 min read</span>
            </div>
            <h1 class="article-title">{{ $post->title }}</h1>
            @if(!empty($post->excerpt))
                <p class="article-subtitle">
                    {{ $post->excerpt }}
                </p>
            @endif

            <div class="article-author">
                <img src="{{ $post->author->avatar_url }}" alt="Author" class="author-avatar">
                <div class="author-info">
                    <div class="author-name">{{ $post->author->display_name ?? $post->author->name ?? 'Author' }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Article Image -->

    @if($post->featured_image)
        <div class="article-featured-image">
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
        </div>
    @endif


    <!-- Article Content -->
    <div class="article-content">
        <div class="container-narrow">
            <div class="article-body">

                {!! $content !!}

            </div>

            <!-- Author Card -->
            <div class="author-card">
                <img src="{{ $post->author->avatar_url }}" alt="{{ $post->author->display_name ?? 'Author' }}"
                    class="author-card-avatar">
                <div class="author-card-content">
                    <h3 class="author-card-name">{{ $post->author->display_name ?? $post->author->name ?? 'Author' }}
                    </h3>
                    <p class="author-card-bio">{{ strip_tags($post->author->bio) }}</p>
                </div>
            </div>

            <!-- Related Posts -->
            <section class="related-posts">
                <h2 class="section-title">Related Articles</h2>
                <div class="related-grid">
                    <article class="related-card">
                        <img src="https://images.unsplash.com/photo-1558655146-9f40138edfeb?w=400&h=250&fit=crop"
                            alt="Related post">
                        <div class="related-content">
                            <span class="related-category">Design</span>
                            <h3><a href="post.html">Minimalism in the Digital Age</a></h3>
                        </div>
                    </article>
                    <article class="related-card">
                        <img src="https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=400&h=250&fit=crop"
                            alt="Related post">
                        <div class="related-content">
                            <span class="related-category">Technology</span>
                            <h3><a href="post.html">The Rise of Sustainable Tech</a></h3>
                        </div>
                    </article>
                    <article class="related-card">
                        <img src="https://images.unsplash.com/photo-1519389950473-47ba0277781c?w=400&h=250&fit=crop"
                            alt="Related post">
                        <div class="related-content">
                            <span class="related-category">Design</span>
                            <h3><a href="post.html">Typography That Tells Stories</a></h3>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </div>
</article>

{!! get_footer() !!}