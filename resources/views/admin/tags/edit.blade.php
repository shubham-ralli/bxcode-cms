@extends('admin.components.admin')

@section('title', 'Edit Tag')
@section('header', 'Edit Tag')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="mb-6 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Edit Tag: {{ $tag->name }}</h3>
                <a href="{{ route('admin.tags.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">&larr; Back
                    to Tags</a>
            </div>

            <form action="{{ route('admin.tags.update', $tag->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="from_edit_page" value="1">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" name="name" value="{{ old('name', $tag->name) }}" required
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5">
                    <p class="text-xs text-gray-500 mt-1">The name is how it appears on your site.</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $tag->slug) }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5">
                    <p class="text-xs text-gray-500 mt-1">The "slug" is the URL-friendly version of the name. It is usually
                        all lowercase and contains only letters, numbers, and hyphens.</p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="5"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2.5">{{ old('description', $tag->description) }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">The description is not prominent by default; however, some themes
                        may show it.</p>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded shadow-sm text-sm transition-colors">
                        Update
                    </button>
                    <a href="{{ route('admin.tags.index') }}" class="text-gray-500 text-sm hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection