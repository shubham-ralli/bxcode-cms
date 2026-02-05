@extends('admin.components.admin')

@section('title', 'Permalink Settings')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Permalink Settings</h1>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column (Main Content) -->
                <div class="lg:col-span-2 space-y-6">
                    
                    @php 
                        $currentStruct = $settings['permalink_structure'] ?? '/%postname%/';
                        $siteUrl = config('app.url');
                    @endphp

                    <!-- Card 1: Common Settings -->
                    <div class="bg-white rounded-lg shadow" 
                         x-data="{ 
                            structure: '{{ $currentStruct }}', 
                            custom: '{{ $currentStruct }}',
                            updateCustom(val) { this.structure = val; }
                         }">
                         <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Common Settings</h3>
                            
                            <div class="space-y-4">
                                <!-- Plain -->
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="radio" name="permalink_structure" value="?p=%post_id%" 
                                           x-model="structure"
                                           class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Plain</span>
                                        <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">{{ $siteUrl }}/?p=123</code>
                                    </div>
                                </label>

                                <!-- Day and name -->
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="radio" name="permalink_structure" value="/%year%/%monthnum%/%day%/%postname%/" 
                                           x-model="structure"
                                           class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Day and name</span>
                                        <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">{{ $siteUrl }}/2026/01/27/sample-post/</code>
                                    </div>
                                </label>

                                <!-- Month and name -->
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="radio" name="permalink_structure" value="/%year%/%monthnum%/%postname%/" 
                                           x-model="structure"
                                           class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Month and name</span>
                                        <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">{{ $siteUrl }}/2026/01/sample-post/</code>
                                    </div>
                                </label>

                                <!-- Numeric -->
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="radio" name="permalink_structure" value="/archives/%post_id%" 
                                           x-model="structure"
                                           class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Numeric</span>
                                        <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">{{ $siteUrl }}/archives/123</code>
                                    </div>
                                </label>

                                <!-- Post name -->
                                <label class="flex items-start space-x-3 cursor-pointer">
                                    <input type="radio" name="permalink_structure" value="/%postname%/" 
                                           x-model="structure"
                                           class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div>
                                        <span class="block text-sm font-medium text-gray-900">Post name</span>
                                        <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded border border-gray-200">{{ $siteUrl }}/sample-post/</code>
                                    </div>
                                </label>

                                <!-- Custom Structure -->
                                <div class="flex items-start space-x-3 mt-4 pt-4 border-t border-gray-100">
                                    <input type="radio" name="custom_selection" value="custom" 
                                           :checked="['?p=%post_id%', '/%year%/%monthnum%/%day%/%postname%/', '/%year%/%monthnum%/%postname%/', '/archives/%post_id%', '/%postname%/'].indexOf(structure) === -1"
                                           @click="structure = custom"
                                           class="mt-3 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                                    <div class="flex-1">
                                        <span class="block text-sm font-medium text-gray-900 mb-2">Custom Structure</span>
                                        <div class="flex items-center">
                                            <span class="text-sm text-gray-500 bg-gray-50 border border-r-0 border-gray-200 rounded-l-md py-3 px-3">{{ $siteUrl }}</span>
                                            <input type="text" name="permalink_structure" 
                                                   x-model="structure"
                                                   @input="custom = structure"
                                                   class="flex-1 min-w-0 block w-full px-3 py-3 rounded-none rounded-r-md border border-gray-200 bg-gray-50 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        </div>
                                        
                                        <!-- Tags -->
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            @foreach(['%year%', '%monthnum%', '%day%', '%hour%', '%minute%', '%second%', '%post_id%', '%postname%', '%category%', '%author%'] as $tag)
                                                <button type="button" 
                                                        @click="structure += '{{ $tag }}/'; custom = structure"
                                                        class="inline-flex items-center px-2 py-1 border border-gray-200 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none transition-colors">
                                                    {{ $tag }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>

                    <!-- Card 2: Optional -->
                    <div class="bg-white rounded-lg shadow">
                         <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Optional</h3>
                            <p class="text-sm text-gray-500 mb-6">If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>{{ $siteUrl }}/topics/uncategorized/</code>. If you leave these blank the defaults will be used.</p>
                            
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Category base</label>
                                    <input type="text" name="category_base" value="{{ $settings['category_base'] ?? '' }}"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 shadow-none focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tag base</label>
                                    <input type="text" name="tag_base" value="{{ $settings['tag_base'] ?? '' }}"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 shadow-none focus:border-indigo-500 focus:ring-indigo-500">
                                </div>
                            </div>
                         </div>
                    </div>

                </div>

                <!-- Right Column (Sidebar) -->
                <div class="space-y-6">
                    
                    <!-- Save Action -->
                    <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Save Settings</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                Permalinks
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">
                            Save your changes to update the URL structure.
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
