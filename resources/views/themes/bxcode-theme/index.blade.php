{{-- Template Name: Index --}}
{!! get_header() !!}

<header class="bg-white shadow py-6">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl font-bold text-gray-900">{{ $post->title }}</h1>
    </div>
</header>

<main class="container mx-auto px-4 py-8">
    <article class="prose lg:prose-xl mx-auto">
        {!! $content !!}

        {!! get_search_form() !!}
    </article>
</main>

{!! get_footer() !!}