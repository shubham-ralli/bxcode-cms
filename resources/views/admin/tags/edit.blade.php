@extends('admin.components.admin')

@php
    $taxonomy = $tag->taxonomy;
    $taxLabel = 'Tags';
    $taxSingular = 'Tag';

    if ($taxonomy === 'category') {
        $taxLabel = 'Categories';
        $taxSingular = 'Category';
    } elseif ($taxonomy === 'post_tag') {
        $taxLabel = 'Tags';
        $taxSingular = 'Tag';
    } else {
        // Try to find Custom Taxonomy Label
        if (class_exists('\Plugins\ACF\src\Models\CustomTaxonomy')) {
            $customTax = \Plugins\ACF\src\Models\CustomTaxonomy::where('key', $taxonomy)->first();
            if ($customTax) {
                $taxLabel = $customTax->plural_label;
                $taxSingular = $customTax->singular_label;
            } else {
                $taxLabel = ucfirst($taxonomy);
                $taxSingular = ucfirst($taxonomy);
            }
        } else {
            $taxLabel = ucfirst($taxonomy);
            $taxSingular = ucfirst($taxonomy);
        }
    }
@endphp

@section('title', 'Edit ' . $taxSingular . ': ' . $tag->name)
@section('title', 'Edit ' . $taxSingular . ': ' . $tag->name)

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit {{ $taxSingular }}: {{ $tag->name }}</h1>
        <a href="{{ route('admin.tags.index', ['taxonomy' => $tag->taxonomy, 'type' => request('post_type', request('type'))]) }}"
            class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 bg-white">&larr; Back
            to {{ $taxLabel }}</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="p-6">
                <!-- Inner header removed -->
                <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="from_edit_page" value="1">
                    @if(request()->has('post_type') || request()->has('type'))
                        <input type="hidden" name="type" value="{{ request('post_type', request('type')) }}">
                    @endif
                    @if(request()->has('tag_ID'))
                        <input type="hidden" name="tag_ID" value="{{ request('tag_ID') }}">
                    @endif

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name', $tag->name) }}" required
                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">The name is how it appears on your site.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $tag->slug) }}"
                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">The "slug" is the URL-friendly version of the name. It is
                            usually
                            all lowercase and contains only letters, numbers, and hyphens.</p>
                    </div>

                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="5"
                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $tag->description) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">The description is not prominent by default; however, some
                            themes
                            may show it.</p>
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded shadow-sm text-sm transition-colors">
                            Update
                        </button>
                        <a href="{{ route('admin.tags.index', ['taxonomy' => $tag->taxonomy, 'type' => request('type')]) }}"
                            class="text-gray-500 text-sm hover:text-gray-700">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection