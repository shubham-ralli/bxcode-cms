@extends('admin.components.admin')

@section('title', 'SEO Settings')
@section('header', 'SEO Settings')

@section('content')
    <!-- Tabs Navigation -->
    <div x-data="{ activeTab: 'posts' }" class="space-y-6">
        <div class="border-b border-gray-200 bg-white rounded-t-lg shadow-sm">
            <nav class="flex -mb-px px-6 pt-4" aria-label="Tabs">
                <button @click="activeTab = 'posts'" type="button"
                    :class="activeTab === 'posts' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Posts
                </button>
                <button @click="activeTab = 'pages'" type="button"
                    :class="activeTab === 'pages' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Pages
                </button>
                <button @click="activeTab = 'categories'" type="button"
                    :class="activeTab === 'categories' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm transition-colors">
                    Categories
                </button>
            </nav>
        </div>

        <form action="{{ route('admin.seo.settings.update') }}" method="POST">
            @csrf

            <!-- Posts Tab -->
            <div x-show="activeTab === 'posts'" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Posts Search Appearance</h3>
                <p class="text-sm text-gray-600 mb-6">Determine what your posts should look like in the search results by default.</p>

                <div class="space-y-6">
                    <!-- SEO Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SEO Title Template</label>
                        <input type="text" name="seo_post_title_template" value="{{ $settings['seo_post_title_template'] }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" onclick="insertVariable('seo_post_title_template', '%%title%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%title%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_post_title_template', '%%sitename%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%sitename%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_post_title_template', '%%sep%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%sep%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_post_title_template', '%%date%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%date%%
                            </button>
                        </div>
                    </div>

                    <!-- Meta Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description Template</label>
                        <textarea name="seo_post_description_template" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['seo_post_description_template'] }}</textarea>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" onclick="insertVariable('seo_post_description_template', '%%excerpt%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%excerpt%%
                            </button>
                        </div>
                    </div>

                    <!-- Show in Search -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="seo_post_show_in_search" value="1" 
                                {{ $settings['seo_post_show_in_search'] == '1' ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-700">Show posts in search results</label>
                            <p class="text-xs text-gray-500">Disabling this means posts will not be indexed by search engines.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pages Tab -->
            <div x-show="activeTab === 'pages'" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Pages Search Appearance</h3>
                <p class="text-sm text-gray-600 mb-6">Determine what your pages should look like in the search results by default.</p>

                <div class="space-y-6">
                    <!-- SEO Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SEO Title Template</label>
                        <input type="text" name="seo_page_title_template" value="{{ $settings['seo_page_title_template'] }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" onclick="insertVariable('seo_page_title_template', '%%title%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%title%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_page_title_template', '%%sitename%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%sitename%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_page_title_template', '%%sep%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%sep%%
                            </button>
                        </div>
                    </div>

                    <!-- Meta Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description Template</label>
                        <textarea name="seo_page_description_template" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['seo_page_description_template'] }}</textarea>
                    </div>

                    <!-- Show in Search -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="seo_page_show_in_search" value="1"
                                {{ $settings['seo_page_show_in_search'] == '1' ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-700">Show pages in search results</label>
                            <p class="text-xs text-gray-500">Disabling this means pages will not be indexed by search engines.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Tab -->
            <div x-show="activeTab === 'categories'" class="bg-white rounded-lg shadow-sm border border-gray-100 p-6" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Categories Search Appearance</h3>
                <p class="text-sm text-gray-600 mb-6">Determine what your categories should look like in the search results by default.</p>

                <div class="space-y-6">
                    <!-- SEO Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">SEO Title Template</label>
                        <input type="text" name="seo_category_title_template" value="{{ $settings['seo_category_title_template'] }}"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" onclick="insertVariable('seo_category_title_template', '%%title%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%title%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_category_title_template', '%%sitename%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%sitename%%
                            </button>
                            <button type="button" onclick="insertVariable('seo_category_title_template', '%%sep%%')"
                                class="text-xs px-3 py-1.5 bg-gray-100 hover:bg-gray-200 rounded-md border border-gray-300 transition-colors">
                                %%sep%%
                            </button>
                        </div>
                    </div>

                    <!-- Meta Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meta Description Template</label>
                        <textarea name="seo_category_description_template" rows="3"
                            class="w-full border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $settings['seo_category_description_template'] }}</textarea>
                    </div>

                    <!-- Show in Search -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="seo_category_show_in_search" value="1"
                                {{ $settings['seo_category_show_in_search'] == '1' ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="ml-3">
                            <label class="text-sm font-medium text-gray-700">Show categories in search results</label>
                            <p class="text-xs text-gray-500">Disabling this means that archive pages for categories will not be indexed by search engines and will be excluded from XML sitemaps.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-colors">
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    <script>
        function insertVariable(fieldName, variable) {
            const input = document.querySelector(`[name="${fieldName}"]`);
            if (input) {
                const cursorPos = input.selectionStart;
                const textBefore = input.value.substring(0, cursorPos);
                const textAfter = input.value.substring(cursorPos);
                input.value = textBefore + variable + textAfter;
                input.focus();
                input.selectionStart = input.selectionEnd = cursorPos + variable.length;
            }
        }
    </script>
@endsection
