<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @php
        $siteIconId = \App\Models\Setting::get('site_icon');
        $siteIcon = $siteIconId ? \App\Models\Media::find($siteIconId) : null;
    @endphp

    @if($siteIcon)
        <link rel="icon" type="{{ $siteIcon->mime_type }}" href="{{ asset($siteIcon->path) }}">
        <link rel="apple-touch-icon" href="{{ asset($siteIcon->path) }}">
    @endif

    {!! wp_head() !!}
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            padding: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }

        img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body id="{{ $post->type }}-{{ $post->id }}" class="{{ $post->type }}-{{ $post->id }}">
    @include('partials.admin-bar')
    <header>
        <h1>{{ $post->title }}</h1>
    </header>
    <main>
        {!! $content !!}
    </main>
</body>

</html>