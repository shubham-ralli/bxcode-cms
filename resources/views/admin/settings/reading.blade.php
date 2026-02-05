@extends('admin.components.admin')

@section('title', 'Reading Settings')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Reading Settings</h1>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column (Main Content) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Card 1: Homepage Displays -->
                    <div class="bg-white rounded-lg shadow"
                        x-data="{ showOnFront: '{{ $settings['show_on_front'] ?? 'posts' }}' }">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Your homepage displays</h3>

                            <div class="space-y-4">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="show_on_front" value="posts" id="show_posts"
                                        x-model="showOnFront"
                                        class="text-indigo-600 focus:ring-indigo-500 h-4 w-4 border-gray-300">
                                    <label for="show_posts" class="text-sm font-medium text-gray-700">Your latest
                                        posts</label>
                                </div>

                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="show_on_front" value="page" id="show_page"
                                        x-model="showOnFront"
                                        class="text-indigo-600 focus:ring-indigo-500 h-4 w-4 border-gray-300">
                                    <label for="show_page" class="text-sm font-medium text-gray-700">A static page (select
                                        below)</label>
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t">
                                <label for="posts_per_page" class="block text-sm font-medium text-gray-700 mb-2">Blog pages
                                    show at most</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="posts_per_page" id="posts_per_page"
                                        value="{{ $settings['posts_per_page'] ?? 10 }}" min="1" max="100"
                                        class="w-24 border border-gray-200 rounded-md bg-gray-50 p-3">
                                    <span class="text-sm text-gray-500">posts</span>
                                </div>
                            </div>

                            <!-- Dependent Options -->
                            <div x-show="showOnFront === 'page'" class="mt-6 pl-7 space-y-4" x-cloak>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="page_on_front"
                                            class="block text-sm font-medium text-gray-700 mb-2">Homepage:</label>
                                        <select name="page_on_front" id="page_on_front"
                                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-3">
                                            <option value="">- Select -</option>
                                            @foreach($pages as $page)
                                                <option value="{{ $page->id }}" {{ ($settings['page_on_front'] ?? '') == $page->id ? 'selected' : '' }}>
                                                    {{ $page->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="page_for_posts"
                                            class="block text-sm font-medium text-gray-700 mb-2">Posts page:</label>
                                        <select name="page_for_posts" id="page_for_posts"
                                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-3">
                                            <option value="">- Select -</option>
                                            @foreach($pages as $page)
                                                <option value="{{ $page->id }}" {{ ($settings['page_for_posts'] ?? '') == $page->id ? 'selected' : '' }}>
                                                    {{ $page->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mt-2">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-blue-700">
                                                Tip: Ensure the selected pages are <strong>Published</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional standard WP-like reading settings (optional/future) -->
                    {{--
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Feed Settings</h3>
                            ...
                        </div>
                    </div>
                    --}}

                </div>

                <!-- Right Column (Sidebar) -->
                <div class="space-y-6">

                    <!-- Save Action -->
                    <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Save Settings</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Reading
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">
                            Save your changes to update how your site content is displayed.
                        </p>
                        <div class="border-t pt-4">
                            <button type="submit"
                                class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium shadow-sm transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection