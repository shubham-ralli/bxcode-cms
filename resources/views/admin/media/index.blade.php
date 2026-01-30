@extends('admin.components.admin')

@section('title', 'Media Library')
@section('header', 'Media Library')

@section('content')
    <div class="space-y-6">

        <!-- Top Controls: Upload & Filter -->
        <div
            class="bg-white rounded-lg shadow-sm p-4 border border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">

            <!-- Filters -->
            <div class="flex items-center gap-3 w-full md:w-auto overflow-x-auto pb-2 md:pb-0">
                <select id="filterType" onchange="applyFilters()"
                    class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-600 bg-gray-50 hover:bg-white transition-colors cursor-pointer">
                    <option value="all">All File Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                    <option value="audio" {{ request('type') == 'audio' ? 'selected' : '' }}>Audio</option>
                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Video</option>
                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Documents</option>
                </select>

                <select id="filterDate" onchange="applyFilters()"
                    class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-600 bg-gray-50 hover:bg-white transition-colors cursor-pointer">
                    <option value="all">All Dates</option>
                    @foreach($dates as $date)
                        <option value="{{ $date->date }}" {{ request('date') == $date->date ? 'selected' : '' }}>
                            {{ $date->label }}
                        </option>
                    @endforeach
                </select>

                <!-- Bulk Actions -->
                <div id="bulkActions" class="hidden flex items-center gap-2 border-l pl-3 ml-2 border-gray-200">
                    <span class="text-xs font-semibold text-gray-500"><span id="selectedCount">0</span> Selected</span>
                    <button onclick="bulkDelete()"
                        class="text-red-600 hover:text-red-800 text-xs font-medium border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded transition-colors">
                        Delete
                    </button>
                    <button onclick="toggleBulkSelection()" class="text-gray-400 hover:text-gray-600 text-xs px-2">
                        Cancel
                    </button>
                </div>
            </div>

            <!-- Upload Button -->
            <div class="flex-shrink-0">
                <button onclick="document.getElementById('uploadInput').click()"
                    class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg shadow-sm text-sm transition-all transform hover:scale-105 active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    Upload New
                </button>
                <div class="hidden">
                    <input type="file" id="uploadInput" name="image" multiple onchange="handleFiles(this)"
                        accept="image/*,audio/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.txt,.zip">
                </div>
            </div>
        </div>

        <!-- Media Grid Info -->
        <div class="flex justify-between items-center px-2">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide">
                Showing {{ $media->firstItem() ?? 0 }} - {{ $media->lastItem() ?? 0 }} of {{ $media->total() }} items
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="selectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onclick="toggleAllCheckboxes(this)">
                <label for="selectAll" class="text-xs text-gray-500 cursor-pointer select-none">Select All</label>
            </div>
        </div>

        <!-- The Media Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6" id="mediaGrid">
            @include('admin.media.partials.media-items')
        </div>

        <!-- Load More -->
        @if($media->hasMorePages())
            <div class="mt-12 text-center pb-12" id="loadMoreContainer">
                <button onclick="loadMore()"
                    class="bg-white border border-gray-200 text-gray-600 hover:text-indigo-600 hover:border-indigo-200 font-medium py-2.5 px-8 rounded-full shadow-sm hover:shadow transition-all group">
                    <span class="group-hover:hidden">Load More</span>
                    <span class="hidden group-hover:inline-block">Show More Media ↓</span>
                </button>
            </div>
        @endif

    </div>

    <!-- Details Modal (Preserved Logic, Updated UI) -->
    <div id="mediaModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog"
        aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true"
                onclick="closeMediaModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div
                class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:w-full sm:max-w-5xl">
                <!-- Close Button -->
                <button onclick="closeMediaModal()"
                    class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 z-10 p-2 bg-white rounded-full shadow-sm hover:shadow">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <div class="bg-white flex flex-col md:flex-row h-[80vh] md:h-[600px]">

                    <!-- Left: Preview (Dark Background) -->
                    <div
                        class="w-full md:w-2/3 bg-gray-100 flex items-center justify-center p-8 relative overflow-hidden group">
                        <!-- Background Pattern -->
                        <div class="absolute inset-0 opacity-5"
                            style="background-image: radial-gradient(#6b7280 1px, transparent 1px); background-size: 20px 20px;">
                        </div>

                        <!-- Media Content -->
                        <img id="modalImage" src=""
                            class="max-w-full max-h-full object-contain shadow-lg rounded hidden z-10 transition-transform duration-300">
                        <video id="modalVideo" controls class="max-w-full max-h-full shadow-lg rounded hidden z-10"></video>
                        <audio id="modalAudio" controls
                            class="w-full max-w-md mt-4 hidden z-10 shadow-md rounded-full bg-white"></audio>

                        <div id="modalFileIcon" class="text-center hidden z-10">
                            <span class="text-8xl drop-shadow-md" id="modalFileEmoji">📄</span>
                            <div class="mt-4">
                                <button onclick="window.open(document.getElementById('modalUrl').value, '_blank')"
                                    class="bg-white text-gray-800 font-medium py-2 px-6 rounded-full shadow hover:shadow-lg transition-all text-sm">
                                    Download File
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Info Panel -->
                    <div class="w-full md:w-1/3 p-6 md:p-8 flex flex-col bg-white overflow-y-auto">
                        <h3 class="text-xl font-bold text-gray-900 mb-1" id="modal-title">Attachment Details</h3>

                        <!-- Metadata -->
                        <div
                            class="text-xs text-gray-500 mb-6 flex flex-wrap gap-x-4 gap-y-1 border-b border-gray-100 pb-4">
                            <p><strong class="font-medium text-gray-700">Type:</strong> <span id="modalMime"></span></p>
                            <p><strong class="font-medium text-gray-700">Size:</strong> <span id="modalSize">--</span></p>
                            <p><strong class="font-medium text-gray-700">Uploaded:</strong> <span id="modalDate"></span></p>
                        </div>

                        <!-- Edit Form -->
                        <form id="mediaUpdateForm" class="flex-1 space-y-4">
                            <input type="hidden" id="modalMediaId">
                            <input type="hidden" id="modalFilenameInput">

                            <div>
                                <label
                                    class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Title</label>
                                <input type="text" id="modalTitle"
                                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors bg-gray-50 focus:bg-white"
                                    oninput="debouncedAutoSave()">
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Alt
                                    Text</label>
                                <textarea id="modalAlt" rows="3"
                                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 transition-colors bg-gray-50 focus:bg-white resize-none"
                                    oninput="debouncedAutoSave()"></textarea>
                                <p class="mt-1.5 text-xs text-gray-400">Essential for SEO and accessibility.</p>
                            </div>

                            <!-- Auto-Save Indicator -->
                            <div class="h-6 flex items-center">
                                <span id="saveStatus"
                                    class="text-xs font-medium text-green-600 transition-opacity opacity-0 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Changes Saved
                                </span>
                            </div>
                        </form>

                        <!-- Static Actions -->
                        <div class="pt-6 mt-auto space-y-3 border-t border-gray-100">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">File
                                    Link</label>
                                <div class="flex rounded-md shadow-sm">
                                    <input type="text" id="modalUrl" readonly
                                        class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-lg border border-gray-300 bg-gray-50 text-gray-500 sm:text-xs">
                                    <button type="button" onclick="copyUrl()"
                                        class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-lg bg-gray-50 hover:bg-gray-100 text-xs font-medium text-gray-700 transition-colors">
                                        Copy
                                    </button>
                                </div>
                                <p id="copySuccess" class="text-green-600 text-xs mt-1 hidden text-right">Copied!</p>
                            </div>

                            <form id="mediaDeleteForm" method="POST" onsubmit="return confirm('Delete permanently?');"
                                class="pt-2 text-right">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="text-red-500 hover:text-red-700 text-xs font-medium hover:underline transition-colors">
                                    Delete Permanently
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reuse Existing Scripts, Refined -->
    <script>
        // Inject current page items for lookup
        const currentMediaItems = @json($media->items());

        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const itemId = params.get('item');
            if (itemId) {
                const item = currentMediaItems.find(m => m.id == itemId);
                if (item) {
                    openMediaModal(item);
                }
            }
        });

        // --- Same Logic as Before, Just Cleaner Bindings ---
        let saveTimeout;
        const saveStatus = document.getElementById('saveStatus');

        function debouncedAutoSave() {
            saveStatus.innerHTML = '<span class="text-gray-400">Saving...</span>';
            saveStatus.classList.remove('opacity-0');
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(saveMediaDetails, 1000);
        }

        function saveMediaDetails() {
            const id = document.getElementById('modalMediaId').value;
            const itemsUrl = "{{ route('admin.media.index') }}";

            fetch(`${itemsUrl}/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    _method: 'PUT',
                    alt_text: document.getElementById('modalAlt').value,
                    title: document.getElementById('modalTitle').value
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        saveStatus.innerHTML = '<span class="text-green-600 flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Saved</span>';
                        setTimeout(() => saveStatus.classList.add('opacity-0'), 2000);
                    }
                });
        }

        function openMediaModal(media) {
            // Paths
            const cleanPath = media.path.startsWith('/') ? media.path.substring(1) : media.path;
            const fullUrl = "{{ asset('') }}" + cleanPath;

            // Reset
            ['modalImage', 'modalVideo', 'modalAudio', 'modalFileIcon'].forEach(id => document.getElementById(id).classList.add('hidden'));

            if (media.mime_type.startsWith('image/')) {
                const img = document.getElementById('modalImage');
                img.src = fullUrl;
                img.classList.remove('hidden');
            } else if (media.mime_type.startsWith('video/')) {
                const v = document.getElementById('modalVideo');
                v.src = fullUrl;
                v.classList.remove('hidden');
            } else if (media.mime_type.startsWith('audio/')) {
                const a = document.getElementById('modalAudio');
                a.src = fullUrl;
                a.classList.remove('hidden');
            } else {
                document.getElementById('modalFileIcon').classList.remove('hidden');
            }

            // Fill Data
            document.getElementById('modalFilenameInput').value = media.filename; // Hidden backup
            document.getElementById('modalMime').textContent = media.mime_type;
            document.getElementById('modalDate').textContent = new Date(media.created_at).toLocaleDateString();
            document.getElementById('modalSize').textContent = formatBytes(media.size || 0);

            document.getElementById('modalMediaId').value = media.id;
            document.getElementById('modalTitle').value = media.title || '';
            document.getElementById('modalAlt').value = media.alt_text || '';
            document.getElementById('modalUrl').value = fullUrl;

            // Delete Action
            document.getElementById('mediaDeleteForm').action = "{{ route('admin.media.index') }}/" + media.id;

            // Deep Linking
            const url = new URL(window.location);
            url.searchParams.set('item', media.id);
            window.history.pushState({ path: url.href }, '', url.href);

            document.getElementById('mediaModal').classList.remove('hidden');
        }

        function closeMediaModal() {
            document.getElementById('mediaModal').classList.add('hidden');
            // Stop media
            document.getElementById('modalVideo').pause();
            document.getElementById('modalAudio').pause();

            // Remove Deep Link
            const url = new URL(window.location);
            url.searchParams.delete('item');
            window.history.pushState({ path: url.href }, '', url.href);
        }

        // Auto-open from URL on load (if item exists on page)
        window.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(window.location.search);
            const itemId = params.get('item');
            if (itemId) {
                // Find data object from the rendered grid
                // We need to access the media data. Since we passed it to the onclick handler,
                // we can look for the DOM element.
                // Or better, we can dump the current page items to a JS variable.
            }
        });

        function formatBytes(bytes, decimals = 2) {
            if (!+bytes) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return `${parseFloat((bytes / Math.pow(k, i)).toFixed(dm))} ${sizes[i]}`;
        }

        function copyUrl() {
            const copyText = document.getElementById("modalUrl");
            copyText.select();
            navigator.clipboard.writeText(copyText.value).then(() => {
                const msg = document.getElementById('copySuccess');
                msg.classList.remove('hidden');
                setTimeout(() => msg.classList.add('hidden'), 2000);
            });
        }

        // --- Filters & Pagination (Preserved) ---
        function applyFilters() {
            const type = document.getElementById('filterType').value;
            const date = document.getElementById('filterDate').value;
            const url = new URL(window.location.href);
            if (type !== 'all') url.searchParams.set('type', type); else url.searchParams.delete('type');
            if (date !== 'all') url.searchParams.set('date', date); else url.searchParams.delete('date');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        let nextPageUrl = "{{ $media->nextPageUrl() }}";
        let isLoading = false;
        function loadMore() {
            if (!nextPageUrl || isLoading) return;
            isLoading = true;
            const btnText = document.querySelector('#loadMoreContainer button span:last-child');
            const original = btnText.innerText;
            btnText.innerText = 'Loading...';

            const url = new URL(nextPageUrl);
            // Re-append filters
            const type = document.getElementById('filterType').value;
            const date = document.getElementById('filterDate').value;
            if (type !== 'all') url.searchParams.set('type', type);
            if (date !== 'all') url.searchParams.set('date', date);

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    document.getElementById('mediaGrid').insertAdjacentHTML('beforeend', data.html);
                    nextPageUrl = data.next_page_url;
                    if (!nextPageUrl) document.getElementById('loadMoreContainer').remove();
                    else btnText.innerText = original;
                    isLoading = false;
                });
        }

        // Mass Select
        function toggleAllCheckboxes(source) {
            document.querySelectorAll('.media-checkbox').forEach(cb => {
                cb.checked = source.checked;
                // Manually trigger onchange logic if needed, or just update UI
            });
            updateBulkUI();
        }

        // Listen for individual changes
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('media-checkbox')) {
                updateBulkUI();
            }
        });

        function updateBulkUI() {
            const count = document.querySelectorAll('.media-checkbox:checked').length;
            document.getElementById('selectedCount').innerText = count;
            const bulkDiv = document.getElementById('bulkActions');
            if (count > 0) bulkDiv.classList.remove('hidden');
            else bulkDiv.classList.add('hidden');
        }

        function toggleBulkSelection() {
            document.getElementById('selectAll').checked = false;
            document.querySelectorAll('.media-checkbox').forEach(cb => cb.checked = false);
            updateBulkUI();
        }

        function bulkDelete() {
            if (!confirm('Delete selected?')) return;
            const ids = Array.from(document.querySelectorAll('.media-checkbox:checked')).map(cb => cb.value);
            // Fetch delete logic...
            fetch("{{ route('admin.media.bulk-delete') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ _method: 'DELETE', ids: ids })
            }).then(r => r.json()).then(d => {
                if (d.success) window.location.reload();
            });
        }

        // Upload Handling
        function handleFiles(input) {
            const files = Array.from(input.files);
            if (files.length === 0) return;

            files.forEach(file => uploadFile(file));

            // Clear input so same file can be selected again
            input.value = '';
        }

        function uploadFile(file) {
            // Create Placeholder ID
            const tempId = 'upload-' + Math.random().toString(36).substr(2, 9);

            // Generate Placeholder HTML
            const placeholderHtml = `
                        <div id="${tempId}" class="relative group aspect-square bg-white rounded-lg border border-indigo-100 shadow-sm p-2 flex flex-col items-center justify-center relative overflow-hidden">
                           <!-- Icon -->
                           <div class="mb-3 text-indigo-500">
                                <svg class="w-8 h-8 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                           </div>
                           <!-- Progress Bar -->
                           <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden mb-1">
                                <div class="progress-bar bg-indigo-500 h-full rounded-full transition-all duration-200" style="width: 0%"></div>
                           </div>
                           <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wide">Uploading...</span>

                           <!-- Overlay for image preview if available -->
                           ${file.type.startsWith('image/') ? `<img src="${URL.createObjectURL(file)}" class="absolute inset-0 w-full h-full object-cover opacity-20 -z-0">` : ''}
                        </div>
                    `;

            // Prepend to Grid
            const grid = document.getElementById('mediaGrid');
            grid.insertAdjacentHTML('afterbegin', placeholderHtml);

            const card = document.getElementById(tempId);
            const progressBar = card.querySelector('.progress-bar');

            // Form Data
            const formData = new FormData();
            formData.append('image', file);
            formData.append('return_html', '1');

            // XHR Upload
            const xhr = new XMLHttpRequest();
            xhr.open('POST', "{{ route('admin.media.store') }}", true);
            xhr.setRequestHeader('X-CSRF-TOKEN', "{{ csrf_token() }}");
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.upload.onprogress = function (e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    progressBar.style.width = percentComplete + '%';
                }
            };

            xhr.onload = function () {
                if (xhr.status === 200) {
                    const data = JSON.parse(xhr.responseText);
                    if (data.html) {
                        card.outerHTML = data.html;
                        // Re-bind listeners if needed (not needed since onclick is inline)
                    } else {
                        showError(card, 'Invalid Response');
                    }
                } else {
                    showError(card, 'Upload Failed');
                }
            };

            xhr.onerror = function () {
                showError(card, 'Network Error');
            };

            xhr.send(formData);
        }

        function showError(card, message) {
            card.innerHTML = `
                        <div class="text-center text-red-500 p-2">
                            <svg class="w-8 h-8 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-[10px] font-bold block">${message}</span>
                        </div>
                    `;
            setTimeout(() => card.remove(), 3000);
        }

    </script>
@endsection