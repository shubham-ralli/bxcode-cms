@extends('admin.components.admin')

@section('title', 'Permalink Settings')
@section('header', 'Permalink Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf
            
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Common Settings</h3>
                
                @php 
                    $currentStruct = $settings['permalink_structure'] ?? '/%postname%/';
                    $siteUrl = config('app.url');
                @endphp

                <div class="space-y-4" x-data="{ 
                    structure: '{{ $currentStruct }}',
                    custom: '{{ $currentStruct }}',
                    updateCustom(val) {
                        this.structure = val;
                        // if custom logic needed
                    }
                }">
                    
                    <!-- Plain -->
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="permalink_structure" value="?p=%post_id%" 
                               x-model="structure"
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900">Plain</span>
                            <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded">{{ $siteUrl }}/?p=123</code>
                        </div>
                    </label>

                    <!-- Day and name -->
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="permalink_structure" value="/%year%/%monthnum%/%day%/%postname%/" 
                               x-model="structure"
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900">Day and name</span>
                            <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded">{{ $siteUrl }}/2026/01/27/sample-post/</code>
                        </div>
                    </label>

                    <!-- Month and name -->
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="permalink_structure" value="/%year%/%monthnum%/%postname%/" 
                               x-model="structure"
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900">Month and name</span>
                            <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded">{{ $siteUrl }}/2026/01/sample-post/</code>
                        </div>
                    </label>

                    <!-- Numeric -->
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="permalink_structure" value="/archives/%post_id%" 
                               x-model="structure"
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900">Numeric</span>
                            <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded">{{ $siteUrl }}/archives/123</code>
                        </div>
                    </label>

                    <!-- Post name -->
                    <label class="flex items-start space-x-3 cursor-pointer">
                        <input type="radio" name="permalink_structure" value="/%postname%/" 
                               x-model="structure"
                               class="mt-1 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div>
                            <span class="block text-sm font-medium text-gray-900">Post name</span>
                            <code class="text-xs text-gray-500 bg-gray-100 px-1 py-0.5 rounded">{{ $siteUrl }}/sample-post/</code>
                        </div>
                    </label>

                    <!-- Custom Structure -->
                    <div class="flex items-start space-x-3">
                        <input type="radio" name="custom_selection" value="custom" 
                               :checked="['?p=%post_id%', '/%year%/%monthnum%/%day%/%postname%/', '/%year%/%monthnum%/%postname%/', '/archives/%post_id%', '/%postname%/'].indexOf(structure) === -1"
                               @click="structure = custom"
                               class="mt-3 h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                        <div class="flex-1">
                            <span class="block text-sm font-medium text-gray-900 mb-1">Custom Structure</span>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500 bg-gray-50 border border-r-0 border-gray-300 rounded-l py-2 px-3">{{ $siteUrl }}</span>
                                <input type="text" name="permalink_structure" 
                                       x-model="structure"
                                       @input="custom = structure"
                                       class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            </div>
                            
                            <!-- Tags -->
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach(['%year%', '%monthnum%', '%day%', '%hour%', '%minute%', '%second%', '%post_id%', '%postname%', '%category%', '%author%'] as $tag)
                                    <button type="button" 
                                            @click="structure += '{{ $tag }}/'; custom = structure"
                                            class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none">
                                        {{ $tag }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="border-t border-gray-200 pt-8 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Optional</h3>
                <p class="text-sm text-gray-500 mb-4">If you like, you may enter custom structures for your category and tag URLs here. For example, using <code>topics</code> as your category base would make your category links like <code>{{ $siteUrl }}/topics/uncategorized/</code>. If you leave these blank the defaults will be used.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category base</label>
                        <input type="text" name="category_base" value="{{ $settings['category_base'] ?? '' }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tag base</label>
                        <input type="text" name="tag_base" value="{{ $settings['tag_base'] ?? '' }}"
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                </div>
            </div>

            <div class="flex justify-start">
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded shadow-sm text-sm transition-colors">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
