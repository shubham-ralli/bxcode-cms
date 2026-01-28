@extends('admin.components.admin')

@section('header')
    General Settings
@endsection

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">


        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <!-- General Settings -->
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Site Identity</h3>
            <div class="mb-4">
                <label for="site_title" class="block text-gray-700 text-sm font-bold mb-2">Site Title</label>
                <input type="text" name="site_title" id="site_title" value="{{ $settings['site_title'] ?? '' }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label for="tagline" class="block text-gray-700 text-sm font-bold mb-2">Tagline</label>
                <input type="text" name="tagline" id="tagline" value="{{ $settings['tagline'] ?? '' }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                <p class="text-gray-500 text-xs mt-1">In a few words, explain what this site is about.</p>
            </div>

            <div class="mb-6">
                <label for="site_url" class="block text-gray-700 text-sm font-bold mb-2">Site Address (URL)</label>
                <input type="url" name="site_url" id="site_url" value="{{ $settings['site_url'] ?? config('app.url') }}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Site Icon</label>
                <p class="text-gray-500 text-xs mb-3">The site icon is used as a browser and app icon for your site. Icons
                    must be square and at least 512 × 512 pixels.</p>

                @php
                    $siteIconId = $settings['site_icon'] ?? null;
                    $siteIcon = $siteIconId ? \App\Models\Media::find($siteIconId) : null;
                @endphp

                <div class="flex items-start gap-4">
                    <!-- Icon Preview -->
                    <div
                        class="w-24 h-24 border-2 border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 overflow-hidden">
                        @if($siteIcon)
                            <img id="siteIconPreview" src="{{ asset($siteIcon->path) }}" alt="Site Icon"
                                class="w-full h-full object-cover">
                        @else
                            <svg id="siteIconPlaceholder" class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                        @endif
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-col gap-2">
                        <button type="button" onclick="openMediaPicker('site_icon', 'siteIconPreview')"
                            class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded text-sm">
                            {{ $siteIcon ? 'Change Site Icon' : 'Select Site Icon' }}
                        </button>
                        @if($siteIcon)
                            <button type="button" onclick="removeSiteIcon()"
                                class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-4 rounded text-sm">
                                Remove Site Icon
                            </button>
                        @endif
                    </div>
                </div>

                <input type="hidden" name="site_icon" id="site_icon" value="{{ $siteIconId }}">
            </div>

            <!-- Reading Settings Removed -->

            <!-- Code Injection -->
            <h3 class="text-lg font-semibold mb-4 border-b pb-2 mt-8">Header & Footer Scripts</h3>
            <div class="mb-4">
                <label for="header_scripts" class="block text-gray-700 text-sm font-bold mb-2">Header Scripts (in
                    <code>&lt;head&gt;</code>)</label>
                <textarea name="header_scripts" id="header_scripts" rows="5"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm"
                    placeholder="<script>...</script>">{{ $settings['header_scripts'] ?? '' }}</textarea>
                <p class="text-gray-500 text-xs mt-1">Useful for Google Analytics, Meta tags, verification codes.</p>
            </div>

            <div class="mb-4">
                <label for="footer_scripts" class="block text-gray-700 text-sm font-bold mb-2">Footer Scripts (before
                    <code>&lt;/body&gt;</code>)</label>
                <textarea name="footer_scripts" id="footer_scripts" rows="5"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline font-mono text-sm"
                    placeholder="<script>...</script>">{{ $settings['footer_scripts'] ?? '' }}</textarea>
            </div>

            <div class="flex items-center justify-between mt-8">
                <button
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                    type="submit">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    <script>
        // Global media picker callback function
        window.mediaPickerCallback = function (inputId, previewId) {
            // This function is called after selection in media picker modal
            // The confirmSelection() in media-picker-modal already:
            // 1. Sets input.value = pickerSelectedId
            // 2. Sets preview.src = pickerSelectedUrl  
            // 3. Removes 'hidden' class from preview

            // For site icon, we need additional logic
            if (inputId === 'site_icon') {
                const preview = document.getElementById('siteIconPreview');
                const placeholder = document.getElementById('siteIconPlaceholder');

                // Ensure preview image exists (it should be set already by confirmSelection)
                if (preview && preview.src && placeholder) {
                    preview.style.display = 'block';
                    placeholder.style.display = 'none';
                }
            }
        };

        function removeSiteIcon() {
            document.getElementById('site_icon').value = '';
            const preview = document.getElementById('siteIconPreview');
            const placeholder = document.getElementById('siteIconPlaceholder');

            if (preview) {
                preview.style.display = 'none';
                preview.src = '';
            }
            if (placeholder) {
                placeholder.style.display = 'block';
            }

            alert('Site icon will be removed when you save changes.');
        }
    </script>

    @include('admin.partials.media-picker-modal')
@endsection