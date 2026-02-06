{{-- Template Name: Page --}}
{!! get_header() !!}

<section class="about-hero">
    <div class="container">
        <div class="about-hero-content">
            <h1 class="about-title">{{ $post->title }}</h1>

            @if(!empty($post->excerpt))
                <p class="about-lead">{{ $post->excerpt }}</p>
            @endif

        </div>
    </div>
</section>


<main class="about-content">
    <div class="container-narrow">

        <section class="about-section">
            <div class="about-text">
                {!! $content !!}
            </div>
        </section>

    </div>
</main>

{!! get_footer() !!}