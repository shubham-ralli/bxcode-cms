{{-- Template Name: Page New --}}
{!! get_header(null, [
    'title' => $post->title . ' - ' . \App\Models\Setting::get('site_title', 'BxCode CMS')
]) !!}

<header class="bg-indigo-600 text-white py-12">
    <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-extrabold">{{ $post->title }}</h1>
        @if(get_field('subtitle'))
            <p class="text-2xl text-indigo-200 mt-2 font-light">{{ get_field('subtitle') }}</p>
        @endif
    </div>
</header>
<main class="container mx-auto px-4 py-12 max-w-4xl">
    <div class="prose lg:prose-xl mx-auto">
        @if(!empty($post->excerpt))
            <p class="lead text-xl text-gray-500 mb-8 italic">{{ $post->excerpt }}</p>
        @endif
        {{-- Displaying content from $post object directly as requested --}}
        {{-- Note: Using {!! !!} to render HTML. {{ }} would escape it. --}}
        {{-- Note: This skips shortcode processing done in the controller. --}}
        {!! $post->content !!}

        {{-- Ultra Simple: One-line fetch (No PHP block needed) --}}

        <div class="mt-8 p-6 bg-gray-100 rounded-lg">
            <h3 class="text-xl font-bold mb-2">Simple Title: {{ get_post_field(26, 'title') }}</h3>
            <div class="prose">
                {!! get_post_field(26, 'content') !!}
            </div>
        </div>
    </div>
</main>

{!! get_footer() !!}