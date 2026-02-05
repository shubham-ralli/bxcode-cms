@extends('admin.components.admin')

@section('title', 'Edit Taxonomy')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Taxonomy: {{ $taxonomy->plural_label }}</h1>
            <a href="{{ route('admin.acf.taxonomies.index') }}"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50 bg-white">
                &larr; Back to List
            </a>
        </div>

        <form action="{{ route('admin.acf.taxonomies.update', $taxonomy->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                        </nav>
                    </div>

                    <div class="p-6">
                        
                        <!-- TAB: GENERAL -->
                        <div x-show="tab === 'general'" class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Plural Label <span class="text-red-500">*</span></label>
                                    <input type="text" name="plural_label" value="{{ old('plural_label', $taxonomy->plural_label) }}" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                        placeholder="e.g., Genres">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Singular Label <span class="text-red-500">*</span></label>
                                    <input type="text" name="singular_label" value="{{ old('singular_label', $taxonomy->singular_label) }}" required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                        placeholder="e.g., Genre">
                                </div>
                            </div>

                            <div class="mt-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Taxonomy Key <span class="text-red-500">*</span></label>
                                <input type="text" name="key" value="{{ old('key', $taxonomy->key) }}" required
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                    placeholder="e.g., genre" pattern="[a-z_]+" title="lowercase letters and underscores only">
                                <p class="text-xs text-gray-500 mt-1">Lowercase letters and underscores only. Max 20 characters.</p>
                                @error('key')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-6 pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Post Types</h3>
                                <p class="text-sm text-gray-600 mb-4">Select which post types this taxonomy should apply to.</p>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-200">
                                        <input type="checkbox" name="post_types[]" value="post"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                            {{ in_array('post', $taxonomy->post_types ?? []) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">Posts (Standard)</span>
                                    </label>
                                    <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-200">
                                        <input type="checkbox" name="post_types[]" value="page"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                            {{ in_array('page', $taxonomy->post_types ?? []) ? 'checked' : '' }}>
                                        <span class="text-sm text-gray-700">Pages</span>
                                    </label>
                                    @foreach($postTypes as $type)
                                        <label class="flex items-center space-x-3 cursor-pointer p-2 hover:bg-gray-50 rounded border border-transparent hover:border-gray-200">
                                            <input type="checkbox" name="post_types[]" value="{{ $type->key }}"
                                                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 h-4 w-4"
                                                {{ in_array($type->key, $taxonomy->post_types ?? []) ? 'checked' : '' }}>
                                            <span class="text-sm text-gray-700">{{ $type->plural_label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- TAB: SETTINGS -->
                        <div x-show="tab === 'settings'" class="space-y-6" x-cloak>
                            
                            <!-- Hierarchy -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Hierarchy</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="radio" name="hierarchical" value="1" 
                                            {{ $taxonomy->hierarchical ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Hierarchical (Like Categories)</span>
                                            <span class="block text-xs text-gray-500">Allows parent-child relationships. Checkbox inputs.</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="radio" name="hierarchical" value="0" 
                                            {{ !$taxonomy->hierarchical ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Non-Hierarchical (Like Tags)</span>
                                            <span class="block text-xs text-gray-500">Flat structure. Tag input.</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <div class="pt-6 border-t">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Public Visibility</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="radio" name="publicly_queryable" value="1" 
                                            {{ $taxonomy->publicly_queryable ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Public (Show View Link)</span>
                                            <span class="block text-xs text-gray-500">Accessible on frontend. Shows "View" link
                                                in admin.</span>
                                        </div>
                                    </label>

                                    <label class="flex items-center space-x-3 cursor-pointer p-3 border rounded hover:bg-gray-50">
                                        <input type="radio" name="publicly_queryable" value="0"
                                            {{ !$taxonomy->publicly_queryable ? 'checked' : '' }}
                                            class="text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                                        <div>
                                            <span class="block text-sm font-medium text-gray-900">Private (Admin Only)</span>
                                            <span class="block text-xs text-gray-500">Validation 404 on frontend. No "View" link.</span>
                                        </div>
                                    </label>
                                </div>
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
                                Active
                            </span>
                        </div>
                        <div class="border-t pt-4 mt-4">
                            <button type="submit"
                                class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium shadow-sm transition-colors">
                                Update Taxonomy
                            </button>
                        </div>
                        <div class="mt-4 pt-4 border-t">
                             <!-- Delete Button -->
                             <button type="button" form="delete-form" onclick="if(confirm('Are you sure?')) document.getElementById('delete-form').submit();" 
                                class="text-red-600 hover:text-red-900 text-sm">
                                Move to Trash
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </form>

        <form id="delete-form" action="{{ route('admin.acf.taxonomies.destroy', $taxonomy->id) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    </div>
@endsection
