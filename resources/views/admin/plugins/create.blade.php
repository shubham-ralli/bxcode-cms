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

            <form id="uploadForm" action="{{ route('admin.plugins.upload') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Choose Plugin File
                    </label>
                    <div class="flex items-center gap-4">
                        <input type="file" name="plugin_file" accept=".zip" required id="pluginFile" class="block w-full text-sm text-gray-500
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

                <!-- Progress Bar Container -->
                <div id="progressContainer" class="hidden mb-6">
                    <div class="flex justify-between mb-1">
                        <span class="text-sm font-medium text-indigo-700">Uploading...</span>
                        <span class="text-sm font-medium text-indigo-700" id="progressPercent">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%" id="progressBar"></div>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="errorMessage"
                    class="hidden mb-6 p-4 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200"></div>

                <!-- Success Action Container -->
                <div id="successContainer" class="hidden mb-6">
                    <div
                        class="p-4 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span id="successMessage">Upload Complete!</span>
                    </div>
                    <div>
                        <a href="{{ route('admin.plugins.index') }}"
                            class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-colors w-full sm:w-auto">
                            Go to Plugin List to Activate
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>

                <div class="flex items-center gap-4" id="submitContainer">
                    <button type="submit" id="submitBtn"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
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

            <script>
                document.getElementById('uploadForm').addEventListener('submit', function (e) {
                    e.preventDefault();

                    const fileInput = document.getElementById('pluginFile');
                    if (!fileInput.files.length) return;

                    const formData = new FormData(this);
                    const xhr = new XMLHttpRequest();

                    // UI Elements
                    const submitContainer = document.getElementById('submitContainer');
                    const progressContainer = document.getElementById('progressContainer');
                    const progressBar = document.getElementById('progressBar');
                    const progressPercent = document.getElementById('progressPercent');
                    const successContainer = document.getElementById('successContainer');
                    const errorMessage = document.getElementById('errorMessage');
                    const submitBtn = document.getElementById('submitBtn');

                    // Reset UI
                    errorMessage.classList.add('hidden');
                    successContainer.classList.add('hidden');
                    progressContainer.classList.remove('hidden');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Uploading...';

                    xhr.upload.addEventListener('progress', function (e) {
                        if (e.lengthComputable) {
                            const percent = Math.round((e.loaded / e.total) * 100);
                            progressBar.style.width = percent + '%';
                            progressPercent.innerText = percent + '%';

                            if (percent === 100) {
                                submitBtn.innerHTML = 'Extracting...';
                            }
                        }
                    });

                    xhr.addEventListener('load', function () {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = `
                                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Install Plugin
                            `;

                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    // Hide form buttons, show success
                                    submitContainer.classList.add('hidden');
                                    progressContainer.classList.add('hidden');
                                    successContainer.classList.remove('hidden');
                                    document.getElementById('successMessage').innerText = response.message;
                                } else {
                                    throw new Error(response.message || 'Upload failed');
                                }
                            } catch (err) {
                                errorMessage.innerText = err.message;
                                errorMessage.classList.remove('hidden');
                                progressContainer.classList.add('hidden');
                            }
                        } else {
                            let msg = 'Upload failed. Please try again.';
                            try {
                                const errRes = JSON.parse(xhr.responseText);
                                if (errRes.message) msg = errRes.message;
                            } catch (e) { }

                            errorMessage.innerText = msg;
                            errorMessage.classList.remove('hidden');
                            progressContainer.classList.add('hidden');
                        }
                    });

                    xhr.addEventListener('error', function () {
                        progressBar.style.width = '0%';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Install Plugin';
                        errorMessage.innerText = 'Network error occurred. Please try again.';
                        errorMessage.classList.remove('hidden');
                        progressContainer.classList.add('hidden');
                    });

                    xhr.open('POST', this.action, true);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.send(formData);
                });
            </script>
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