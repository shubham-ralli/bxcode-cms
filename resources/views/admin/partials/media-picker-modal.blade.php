<!-- Reusable Media Picker Modal -->
<div id="mediaPickerModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title"
    role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"
            onclick="closeMediaPicker()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div
            class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full h-[85vh] flex flex-col">

            <!-- Header -->
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 border-b flex justify-between items-center shrink-0">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Select Media</h3>
                <button onclick="closeMediaPicker()" class="text-gray-400 hover:text-gray-500">
                    <span class="text-2xl">&times;</span>
                </button>
            </div>

            <!-- Tabs -->
            <div class="bg-gray-50 border-b px-6 flex gap-4 shrink-0">
                <button type="button" onclick="switchTab('upload')" id="tab-upload"
                    class="py-3 px-2 border-b-2 border-transparent text-gray-500 hover:text-gray-700 text-sm font-medium">Upload
                    Files</button>
                <button type="button" onclick="switchTab('library')" id="tab-library"
                    class="py-3 px-2 border-b-2 border-indigo-500 text-indigo-600 text-sm font-medium">Media
                    Library</button>
            </div>

            <!-- Content -->
            <div class="flex-1 overflow-hidden bg-gray-100 flex relative">

                <!-- Upload Tab -->
                <div id="view-upload"
                    class="hidden w-full h-full flex flex-col items-center justify-center border-2 border-dashed border-gray-300 bg-white m-4 rounded-lg">
                    <p class="text-gray-500 mb-4">Drop files to upload</p>
                    <p class="text-gray-400 text-sm mb-4">or</p>
                    <label
                        class="bg-indigo-600 text-white px-4 py-2 rounded cursor-pointer hover:bg-indigo-700 transition-colors">
                        Select Files
                        <input type="file" id="mediaUploadInput" class="hidden" multiple
                            onchange="handleFileUpload(this)">
                    </label>
                </div>

                <!-- Library Tab (Split View) -->
                <div id="view-library" class="w-full h-full flex flex-col">

                    <!-- Toolbar -->
                    <div class="bg-white border-b px-4 py-3 flex gap-4 items-center shrink-0 flex-wrap">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" id="pickerSearch" placeholder="Search by name, title..."
                                oninput="debouncePickerSearch()"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                        </div>
                        <select id="pickerTypeFilter" onchange="loadPickerMedia(true)"
                            class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="all">All media items</option>
                            <option value="image">Images</option>
                            <option value="audio">Audio</option>
                            <option value="video">Video</option>
                            <option value="document">Documents</option>
                        </select>
                        <select id="pickerDateFilter" onchange="loadPickerMedia(true)"
                            class="border-gray-300 rounded-md shadow-sm text-sm">
                            <option value="all">All dates</option>
                            @foreach(\App\Models\Media::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as date, DATE_FORMAT(created_at, "%M %Y") as label')->distinct()->orderBy('created_at', 'desc')->get() as $date)
                                <option value="{{ $date->date }}">{{ $date->label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-1 overflow-hidden">
                        <!-- Grid -->
                        <div class="flex-1 overflow-y-auto p-4" id="pickerGridContainer">
                            <div id="pickerGrid"
                                class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 content-start">
                                <!-- Items loaded via AJAX -->
                            </div>
                            <div id="pickerLoadMore" class="text-center mt-4 hidden pb-4">
                                <button type="button" onclick="loadPickerMedia()"
                                    class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">Load More</button>
                            </div>
                        </div>

                        <!-- Sidebar (Attachment Details) -->
                        <div id="pickerSidebar"
                            class="w-80 bg-gray-50 border-l overflow-y-auto p-4 hidden shrink-0 flex flex-col">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Attachment Details</h3>

                            <!-- Metadata -->
                            <div class="text-sm text-gray-600 space-y-1 mb-6 border-b border-gray-200 pb-4">
                                <p><span class="font-semibold text-gray-700">Uploaded on:</span> <span
                                        id="sidebarUploadedOn"></span></p>
                                <p><span class="font-semibold text-gray-700">File name:</span> <span
                                        id="sidebarFilename" class="break-all"></span></p>
                                <p><span class="font-semibold text-gray-700">File type:</span> <span
                                        id="sidebarFileType"></span></p>
                                <p id="sidebarSize" class="hidden"></p>
                            </div>

                            <!-- Inputs -->
                            <div class="space-y-4 flex-1">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alternative
                                        Text</label>
                                    <input type="text" id="sidebarAlt" oninput="debouncedSidebarSave()"
                                        class="block w-full border-gray-300 rounded-md shadow-sm text-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="Describe the purpose of the image">
                                    <p class="text-xs text-gray-500 mt-1">Describe the purpose of the image. Leave empty
                                        if the image is purely decorative.</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">Title</label>
                                    <input type="text" id="sidebarTitle" oninput="debouncedSidebarSave()"
                                        class="block w-full border-gray-300 rounded-md shadow-sm text-sm p-2 border focus:ring-indigo-500 focus:border-indigo-500">
                                </div>

                                <div class="border-t border-gray-200 pt-4 mt-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-1">File URL</label>
                                    <div class="flex rounded-md shadow-sm">
                                        <input type="text" id="sidebarUrl" readonly
                                            class="flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-l-md border-gray-300 text-sm border bg-gray-50 text-gray-500 focus:ring-indigo-500 focus:border-indigo-500">
                                        <button type="button" onclick="copyPickerLink()"
                                            class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-700 text-sm font-medium hover:bg-gray-100 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500">
                                            Copy URL
                                        </button>
                                    </div>
                                    <div id="sidebarCopyStatus" class="text-xs text-green-600 hidden mt-1 font-medium">
                                        Link copied to clipboard.</div>
                                </div>

                                <div id="sidebarSaveStatus"
                                    class="text-xs text-green-600 mt-2 hidden transition-opacity duration-1000 font-medium">
                                    Saved.</div>
                            </div>

                            <!-- Delete Action -->
                            <div class="mt-6 text-right">
                                <button type="button" onclick="deletePickerMedia()"
                                    class="text-red-600 hover:text-red-800 text-sm font-medium hover:underline">
                                    Delete permanently
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div class="bg-white px-4 py-3 sm:px-6 flex justify-between items-center border-t shrink-0">
                <div class="text-xs text-gray-500">
                    <span id="pickerCount">0 media items</span> selected
                </div>
                <div class="flex gap-2">
                    <button type="button" onclick="closeMediaPicker()"
                        class="inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:text-sm">
                        Cancel
                    </button>
                    <button type="button" disabled id="pickerSelectBtn" onclick="confirmSelection()"
                        class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        Select
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Hide checkboxes in picker mode */
    #pickerGrid .media-checkbox,
    #pickerGrid .absolute.top-2.left-2.z-10 {
        display: none !important;
    }
</style>

<script>
    let pickerPage = 1;
    let pickerSelectedId = null;
    let pickerSelectedUrl = null;
    let pickerData = {}; // Store loaded items for quick access
    let targetInputId = null;
    let targetPreviewId = null;
    let searchTimeout = null;
    let pickerSaveTimeout = null;

    // Event Delegation for Picker Grid
    document.addEventListener('DOMContentLoaded', function () {
        // Debounce setup for sidebar inputs was moved to inline handlers

        const grid = document.getElementById('pickerGrid');
        if (grid) {
            grid.addEventListener('click', function (e) {
                const clickArea = e.target.closest('.cursor-pointer');
                if (clickArea) {
                    e.preventDefault();
                    e.stopPropagation();

                    const item = clickArea.closest('.media-item');
                    if (item) {
                        const id = item.getAttribute('data-id');
                        const img = item.querySelector('img');
                        const url = img ? img.src : '';

                        const innerElement = clickArea.querySelector('.aspect-square') || clickArea.firstElementChild;
                        selectMediaItem(id, url, innerElement);
                    }
                }
            });
        }
    });

    function debouncePickerSearch() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadPickerMedia(true);
        }, 300);
    }

    function openMediaPicker(inputId, previewId) {
        targetInputId = inputId;
        targetPreviewId = previewId;
        document.getElementById('mediaPickerModal').classList.remove('hidden');
        switchTab('library');

        // Always reload if empty or stale
        if (document.getElementById('pickerGrid').children.length <= 1 || Object.keys(pickerData).length === 0) {
            pickerPage = 1;
            loadPickerMedia(true);
        }
    }

    // --- Auto Save Logic (Matching Main Library) ---
    function debouncedSidebarSave() {
        const status = document.getElementById('sidebarSaveStatus');
        status.textContent = 'Saving...';
        status.classList.remove('hidden', 'text-green-600', 'text-red-600', 'opacity-0');
        status.classList.add('text-gray-500');
        status.style.opacity = '1';

        clearTimeout(pickerSaveTimeout);
        pickerSaveTimeout = setTimeout(saveMediaDetails, 1000);
    }
    // ---------------------------------------------

    function closeMediaPicker() {
        document.getElementById('mediaPickerModal').classList.add('hidden');
    }

    function switchTab(tab) {
        const uploadView = document.getElementById('view-upload');
        const libraryView = document.getElementById('view-library');
        const uploadTab = document.getElementById('tab-upload');
        const libraryTab = document.getElementById('tab-library');

        if (tab === 'upload') {
            uploadView.classList.remove('hidden');
            libraryView.classList.add('hidden');
            uploadTab.classList.add('border-indigo-500', 'text-indigo-600');
            libraryTab.classList.remove('border-indigo-500', 'text-indigo-600');
        } else {
            uploadView.classList.add('hidden');
            libraryView.classList.remove('hidden');
            libraryTab.classList.add('border-indigo-500', 'text-indigo-600');
            uploadTab.classList.remove('border-indigo-500', 'text-indigo-600');
        }
    }

    function loadPickerMedia(reset = false, preSelectId = null) {
        if (reset) {
            document.getElementById('pickerGrid').innerHTML = '<div class="col-span-full text-center text-gray-500 py-10">Loading...</div>';
            pickerPage = 1;
            // Don't fully reset pickerData, just merge new ones or reset if strict
            if (reset) pickerData = {};

            // Hide sidebar on reset unless we are pre-selecting
            if (!preSelectId) {
                document.getElementById('pickerSidebar').classList.add('hidden');
                pickerSelectedId = null;
                document.getElementById('pickerSelectBtn').disabled = true;
            }
        }

        const type = document.getElementById('pickerTypeFilter').value;
        const date = document.getElementById('pickerDateFilter').value;
        const search = document.getElementById('pickerSearch').value;

        const url = new URL("{{ route('admin.media.index') }}");
        url.searchParams.set('page', pickerPage);
        if (type !== 'all') url.searchParams.set('type', type);
        if (date !== 'all') url.searchParams.set('date', date);
        if (search) url.searchParams.set('search', search);

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(res => res.json())
            .then(data => {
                if (reset) document.getElementById('pickerGrid').innerHTML = '';

                // Add items to local store from the raw JSON data
                if (data.items && Array.isArray(data.items)) {
                    data.items.forEach(item => {
                        pickerData[item.id] = item;
                    });
                }

                // Render HTML
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;

                const items = tempDiv.querySelectorAll('.media-item');

                items.forEach(item => {
                    const clickArea = item.querySelector('.cursor-pointer');
                    if (clickArea) {
                        // Remove onclick to prevent edit modal opening
                        clickArea.removeAttribute('onclick');
                    }
                    document.getElementById('pickerGrid').appendChild(item);
                });

                if (data.next_page_url) {
                    pickerPage++;
                    document.getElementById('pickerLoadMore').classList.remove('hidden');
                } else {
                    document.getElementById('pickerLoadMore').classList.add('hidden');
                }

                document.getElementById('pickerCount').innerText = `Showing ${data.total} items`;

                // Handle Auto-Selection if ID is provided
                if (preSelectId) {
                    // Simple timeout to ensure UI is ready
                    setTimeout(() => {
                        selectMediaItem(preSelectId);

                        // Scroll to it
                        const newItem = document.querySelector(`.media-item[data-id="${preSelectId}"]`);
                        if (newItem) newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 100);
                }
            })
            .catch(err => {
                console.error('Error loading media:', err);
                if (reset) document.getElementById('pickerGrid').innerHTML = '<div class="col-span-full text-center text-red-500 py-10">Error loading media. Please try again.</div>';
            });
    }

    function selectMediaItem(id, url = null, element = null) {
        // 1. Clear previous selections
        document.querySelectorAll('.media-item').forEach(el => el.classList.remove('active-selection'));
        document.querySelectorAll('#pickerGrid .ring-4').forEach(el => el.classList.remove('ring-4', 'ring-indigo-500', 'active'));

        pickerSelectedId = id;

        // 2. Find and highlight the specific item by ID
        // Note: We use the ID to find the element reliably
        const container = document.querySelector(`.media-item[data-id="${id}"]`);

        if (container) {
            // Add class to the outer container as requested
            container.classList.add('active-selection');

            // Add ring to the inner visual element (aspect-square)
            const inner = container.querySelector('.aspect-square') || container.querySelector('.cursor-pointer > div');
            if (inner) {
                inner.classList.add('ring-4', 'ring-indigo-500', 'active');
            }
        }

        // 3. Update Sidebar Data
        const item = pickerData[id];

        if (item) {
            // Ensure sidebar is visible
            const sidebar = document.getElementById('pickerSidebar');
            sidebar.classList.remove('hidden');

            // Determine URL
            if (!url || url === '') {
                pickerSelectedUrl = item.full_url || item.path;
            } else {
                pickerSelectedUrl = url;
            }

            // Enable Select Button
            const btn = document.getElementById('pickerSelectBtn');
            if (btn) btn.disabled = false;

            // Populate Sidebar details
            const dateStr = item.created_at ? new Date(item.created_at).toLocaleDateString() : '';

            document.getElementById('sidebarUploadedOn').textContent = dateStr;
            document.getElementById('sidebarFilename').textContent = item.filename || 'Unknown';
            document.getElementById('sidebarFileType').textContent = item.mime_type || 'Unknown';

            document.getElementById('sidebarAlt').value = item.alt_text || '';
            document.getElementById('sidebarTitle').value = item.title || '';
            document.getElementById('sidebarUrl').value = pickerSelectedUrl || '';
        }
    }

    function saveMediaDetails() {
        if (!pickerSelectedId) return;

        const alt = document.getElementById('sidebarAlt').value;
        const title = document.getElementById('sidebarTitle').value;

        // Show saving status immediately
        const status = document.getElementById('sidebarSaveStatus');
        status.textContent = 'Saving...';
        status.classList.remove('hidden', 'text-green-600', 'text-red-600', 'opacity-0');
        status.classList.add('text-gray-500');
        status.style.opacity = '1';

        const baseUrl = "{{ route('admin.media.index') }}";

        fetch(`${baseUrl}/${pickerSelectedId}`, {
            method: 'POST', // Use POST for method spoofing
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                _method: 'PUT',
                alt_text: alt,
                title: title
            })
        })
            .then(async res => {
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(res.status + ': ' + text);
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    status.textContent = 'Saved.';
                    status.classList.remove('text-gray-500');
                    status.classList.add('text-green-600');

                    // Fade out
                    setTimeout(() => {
                        status.style.opacity = '0';
                        setTimeout(() => status.classList.add('hidden'), 1000);
                    }, 2000);

                    // Update local data
                    if (pickerData[pickerSelectedId]) {
                        pickerData[pickerSelectedId].alt_text = alt;
                        pickerData[pickerSelectedId].title = title;
                    }
                }
            })
            .catch(err => {
                console.error('Error saving media details:', err);
                status.textContent = 'Error saving.';
                status.classList.remove('text-gray-500');
                status.classList.add('text-red-600');
            });
    }

    function copyPickerLink() {
        const input = document.getElementById('sidebarUrl');
        if (!input || !input.value) return;

        navigator.clipboard.writeText(input.value).then(() => {
            const status = document.getElementById('sidebarCopyStatus');
            status.classList.remove('hidden');
            setTimeout(() => {
                status.classList.add('hidden');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }

    function deletePickerMedia() {
        if (!pickerSelectedId) return;

        if (!confirm('Are you sure you want to delete this file permanently? This usage will be broken.')) {
            return;
        }

        const deleteUrl = "{{ route('admin.media.index') }}/" + pickerSelectedId;

        fetch(deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
            .then(async res => {
                if (!res.ok) {
                    const text = await res.text();
                    throw new Error(res.status + ': ' + text);
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    // Remove from DOM
                    const itemEl = document.querySelector(`.media-item[data-id="${pickerSelectedId}"]`);
                    if (itemEl) itemEl.remove();

                    // Clear selection
                    pickerSelectedId = null;
                    pickerSelectedUrl = null;
                    document.getElementById('pickerSidebar').classList.add('hidden');
                    document.getElementById('pickerSelectBtn').disabled = true;

                    // Update count visualization (basic)
                    const countEl = document.getElementById('pickerCount');
                    if (countEl && countEl.innerText.includes('Showing')) {
                        // Reload media to get accurate count and refresh grid
                        pickerPage = 1;
                        loadPickerMedia(true);
                    }
                } else {
                    alert('Error deleting file: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error deleting file: ' + err.message);
            });
    }

    function confirmSelection() {
        if (!pickerSelectedId) return;

        const input = document.getElementById(targetInputId);
        const preview = document.getElementById(targetPreviewId);
        if (input) input.value = pickerSelectedId;
        if (preview && pickerSelectedUrl) {
            // We need to make sure the URL is correct for display
            // If it's a relative path starting with /storage, verify the prefix
            // The backend sends full relative uri like /storage/uploads/...
            // Browser treats it correctly as relative to domain root.
            preview.src = pickerSelectedUrl;
            preview.classList.remove('hidden');
        }

        // Call the global callback if it exists (for custom preview logic)
        if (typeof window.mediaPickerCallback === 'function') {
            window.mediaPickerCallback(targetInputId, targetPreviewId);
        }

        closeMediaPicker();
    }

    function handleFileUpload(input) {
        if (input.files.length === 0) return;

        const formData = new FormData();
        formData.append('image', input.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        const statusDiv = document.createElement('div');
        statusDiv.innerText = 'Uploading...';
        statusDiv.className = 'text-indigo-600 font-medium mt-2';
        input.parentElement.parentElement.appendChild(statusDiv);

        fetch("{{ route('admin.media.store') }}", {
            method: 'POST',
            body: formData,
            headers: { 'Accept': 'application/json' }
        })
            .then(res => res.json())
            .then(data => {
                statusDiv.remove();
                if (data.success && data.data) {
                    // Update input to clear it so one can upload same file again if needed
                    input.value = '';

                    // Switch tab and auto-select
                    switchTab('library');
                    loadPickerMedia(true, data.data.id);
                } else {
                    alert('Upload failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(err => {
                console.error(err);
                statusDiv.innerText = 'Error uploading';
            });
    }
</script>