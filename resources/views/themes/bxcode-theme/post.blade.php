{!! get_header() !!}

<article class="max-w-3xl mx-auto px-4 py-12 bg-white shadow-lg my-8 rounded-lg">
    <header class="mb-8">
        <h1 class="text-4xl font-bold mb-2">{{ $post->title }}</h1>
        <p class="text-gray-500 text-sm">Published on {{ $post->created_at->format('F d, Y') }}</p>
    </header>

    <div class="container">


        @if($post->featured_image)
            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}"
                class="w-full h-64 object-cover rounded-lg mb-8">
        @endif

        @if(!empty($post->excerpt))
            <div class="text-xl text-gray-600 mb-8 italic border-l-4 border-indigo-500 pl-4">
                {{ $post->excerpt }}
            </div>
        @endif

        <div class="prose lg:prose-lg max-w-none text-gray-700 leading-relaxed">
            {!! $content !!}
        </div>

    </div>


    <?php

$data = get_field('ebook');

echo get_sub_field('ebook_1');

echo "<pre>";
print_r($data);
echo "</pre>";

echo get_sub_field('ebook_2');

?>

    {{ get_sub_field('ebook_des_1') }}


    <div class="mt-8 pt-6 border-t font-medium text-indigo-600">
        <a href="{{ url('/') }}">&larr; Back to Home</a>
    </div>
</article>

{!! get_footer() !!}