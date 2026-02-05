@extends('admin.components.admin')

@section('title', 'Add New Post Type')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Add New Post Type</h1>
            <a href="{{ route('admin.acf.post-types.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 bg-white">
                &larr; Back to List
            </a>
        </div>

        <form action="{{ route('admin.acf.post-types.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column (Tabs) -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow" x-data="{ tab: 'general' }">
                    
                    <!-- Tabs Navigation -->
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px" aria-label="Tabs">
                            <button type="button" @click="tab = 'general'"
                                :class="tab === 'general' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                                General
                            </button>
                            <button type="button" @click="tab = 'settings'"
                                :class="tab === 'settings' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                                Settings
                            </button>
                            <button type="button" @click="tab = 'labels'"
                                :class="tab === 'labels' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/3 py-4 px-1 text-center border-b-2 font-medium text-sm">
                                Labels
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        
                        <!-- TAB: GENERAL -->
                        <div x-show="tab === 'general'" class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Plural Label <span class="text-red-500">*</span></label>
                                    <input type="text" name="plural_label" value="{{ old('plural_label') }}" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                        placeholder="e.g., Books">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Singular Label <span class="text-red-500">*</span></label>
                                    <input type="text" name="singular_label" value="{{ old('singular_label') }}" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                        placeholder="e.g., Book">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Post Type Key <span class="text-red-500">*</span></label>
                                <input type="text" name="key" value="{{ old('key') }}" required
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                    placeholder="e.g., book" pattern="[a-z_]+" title="lowercase letters and underscores only">
                                <p class="text-xs text-gray-500 mt-1">Lowercase letters and underscores only. Max 20 characters.</p>
                                @error('key')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Taxonomies</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($taxonomies as $taxonomy)
                                        <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="taxonomies[]" value="{{ $taxonomy->id }}"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" 
                                                {{ in_array($taxonomy->id, old('taxonomies', [])) ? 'checked' : '' }}>
                                            <span class="text-sm text-gray-700">{{ $taxonomy->plural_label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Supports</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    @foreach($supports as $key => $label)
                                        @if(in_array($key, ['comments', 'revisions', 'trackbacks', 'page_attributes', 'post_formats', 'custom_fields', 'author'])) @continue @endif
                                        <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="supports[]" value="{{ $key }}"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4" 
                                                {{ in_array($key, ['title', 'editor', 'featured_image']) ? 'checked' : '' }}>
                                            <span class="text-sm text-gray-700">{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- TAB: SETTINGS -->
                        <div x-show="tab === 'settings'" class="space-y-6" x-cloak>
                            
                            <!-- Visibility -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Visibility</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="checkbox" name="settings[show_in_menu]" value="1" checked
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Show in Admin Menu</span>
                                            <span class="block text-xs text-gray-500">Display in the sidebar</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="checkbox" name="settings[show_in_admin_bar]" value="1" checked
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Show in Admin Bar</span>
                                            <span class="block text-xs text-gray-500">Quick access menu</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="checkbox" name="settings[publicly_queryable]" value="1" checked
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Publicly Queryable</span>
                                            <span class="block text-xs text-gray-500">Available on frontend</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- URLs -->
                            <div class="pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">URLs & Archives</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                                        <div class="flex rounded-md shadow-sm">
                                            <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">/</span>
                                            <input type="text" name="settings[slug]" value="{{ old('settings.slug') }}"
                                                class="focus:ring-indigo-500 focus:border-indigo-500 flex-1 block w-full rounded-none rounded-r-md sm:text-sm border-gray-300 bg-gray-50 p-3"
                                                placeholder="Defaults to Key">
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-end">
                                        <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50 w-full h-10">
                                            <input type="checkbox" name="settings[has_archive]" value="1" checked
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                            <span class="text-sm font-medium text-gray-900">Has Archive Page</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Permissions -->
                            <div class="pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Permissions</h3>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Capability Type</label>
                                    <select name="settings[capability_type]" 
                                        class="mt-1 block w-full md:w-1/2 py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-gray-50 p-3">
                                        <option value="post" selected>Post (Standard)</option>
                                        <option value="page">Page (Hierarchical)</option>
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Determines user permissions and hierarchy behavior.</p>
                                </div>
                            </div>

                        </div>

                        <!-- TAB: LABELS -->
                        <div x-show="tab === 'labels'" x-cloak>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Custom Labels</h3>
                            <p class="text-sm text-gray-600 mb-6">Customize the text displayed in the admin interface. (Optional - defaults will be generated)</p>
                            
                            @php
                                $labelFields = [
                                    'menu_name' => 'Menu Name',
                                    'all_items' => 'All Items',
                                    'add_new' => 'Add New'
                                ];
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($labelFields as $key => $label)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase mb-1">{{ $label }}</label>
                                        <input type="text" name="labels[{{ $key }}]"
                                            value="{{ old('labels.' . $key) }}"
                                            class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right Column (Sidebar) -->
                <div class="space-y-6">
                    
                    <!-- Update Action -->
                    <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Publish</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                New
                            </span>
                        </div>
                        <div class="border-t pt-4 mt-4">
                            <button type="submit"
                                class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium shadow-sm transition-colors">
                                Create Post Type
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
@endsection