@extends('admin.components.admin')

@section('title', 'Add New Plugin')
@section('header', 'Add New Plugin')

@section('content')
    <div class="max-w-4xl">
        <!-- Upload Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Upload Plugin</h3>
            <p class="text-sm text-gray-600 mb-6">
                Upload a plugin in .zip format. The plugin will be extracted to the plugins directory.
            </p>

            <form action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Choose Plugin File
                    </label>
                    <div class="flex items-center gap-4">
                        <input type="file" name="plugin_file" accept=".zip" required class="block w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-indigo-50 file:text-indigo-700
                                hover:file:bg-indigo-100
                                cursor-pointer">
                    </div>
                    <p class="mt-2 text-xs text-gray-500">
                        Maximum file size: 10MB. Only .zip files are allowed.
                    </p>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-colors">
                        <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        Install Plugin
                    </button>
                    <a href="{{ route('admin.plugins.index') }}"
                        class="text-gray-600 hover:text-gray-900 font-medium py-2 px-4 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h4 class="text-sm font-semibold text-blue-900 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd"></path>
                </svg>
                Plugin Directory Structure
            </h4>
            <div class="text-sm text-blue-800 space-y-2">
                <p>Your plugin ZIP file should contain a folder structure like this:</p>
                <pre class="bg-white border border-blue-200 rounded p-3 mt-2 text-xs font-mono overflow-x-auto">PluginName/
    ├── plugin.json      (Plugin metadata)
    ├── functions.php    (Plugin code)
    └── views/          (Optional blade views)</pre>

                <p class="mt-3"><strong>Example plugin.json:</strong></p>
                <pre class="bg-white border border-blue-200 rounded p-3 mt-2 text-xs font-mono overflow-x-auto">{
        "name": "My Plugin",
        "description": "Plugin description",
        "version": "1.0.0",
        "author": "Your Name"
    }</pre>
            </div>
        </div>
    </div>
@endsection