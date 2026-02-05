@extends('admin.components.admin')

@section('title', 'SEO Settings')
@section('header', 'SEO Configuration')

@section('content')
    <div class="max-w-4xl mx-auto">
        <form action="{{ route('admin.seo.update') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Sitemap Settings -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                        </path>
                    </svg>
                    XML Sitemap
                </h3>

                <div class="flex items-center justify-between">
                    <div>
                        <label class="font-medium text-gray-700">Enable Sitemap</label>
                        <p class="text-sm text-gray-500">Automatically generate sitemap.xml for your site.</p>
                    </div>

                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="sitemap_enabled" id="toggle" value="1"
                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                            {{ plugin_get_setting('sitemap_enabled', '0') == '1' ? 'checked' : '' }} />
                        <label for="toggle"
                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                    </div>
                </div>

                @if(plugin_get_setting('sitemap_enabled', '0') == '1')
                    <div class="mt-4 p-3 bg-indigo-50 text-indigo-700 rounded text-sm flex items-center justify-between">
                        <span>Your sitemap is available at:</span>
                        <a href="{{ url('sitemap.xml') }}" target="_blank" class="font-bold hover:underline flex items-center">
                            {{ url('sitemap.xml') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                @endif

                <style>
                    .toggle-checkbox:checked {
                        right: 0;
                        border-color: #6875f5;
                    }

                    .toggle-checkbox:checked+.toggle-label {
                        background-color: #6875f5;
                    }

                    .toggle-checkbox {
                        right: auto;
                        left: 0;
                        transition: all 0.2s;
                    }

                    .toggle-label {
                        width: 3rem;
                    }

                    .toggle-checkbox:checked {
                        left: auto;
                        right: 0;
                    }
                </style>
            </div>

            <!-- Robots.txt Editor -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Robots.txt Editor
                </h3>

                <div class="flex items-center justify-between mb-4">
                    <div>
                        <label class="font-medium text-gray-700">Enable Robots.txt</label>
                        <p class="text-sm text-gray-500">Serve a virtual robots.txt file.</p>
                    </div>

                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                        <input type="checkbox" name="robots_enabled" id="toggle_robots" value="1"
                            class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"
                            {{ plugin_get_setting('robots_enabled', '0') == '1' ? 'checked' : '' }} />
                        <label for="toggle_robots"
                            class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                    </div>
                </div>

                @if(plugin_get_setting('robots_enabled', '0') == '1')
                    <div class="mb-4 p-3 bg-indigo-50 text-indigo-700 rounded text-sm flex items-center justify-between">
                        <span>File is active at:</span>
                        <a href="{{ url('robots.txt') }}" target="_blank" class="font-bold hover:underline flex items-center">
                            {{ url('robots.txt') }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Editor Content</label>
                    <textarea name="robots_content" rows="8"
                        class="w-full bg-gray-900 text-green-400 font-mono text-sm p-4 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none">{{ $robotsContent }}</textarea>
                    <p class="mt-2 text-xs text-gray-500">Be careful editing this file. It controls how search engines crawl
                        your site.</p>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-6 rounded shadow transition-colors">
                    Save Changes
                </button>
            </div>

        </form>
    </div>
@endsection