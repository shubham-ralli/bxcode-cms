@extends('admin.components.admin')

@section('title', 'General Settings')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">General Settings</h1>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column (Main Content) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Card 1: Site Identity -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Site Identity</h3>

                            <div class="space-y-6">
                                <div>
                                    <label for="site_title" class="block text-sm font-medium text-gray-700 mb-2">Site
                                        Title</label>
                                    <input type="text" name="site_title" id="site_title"
                                        value="{{ $settings['site_title'] ?? '' }}"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3"
                                        placeholder="My Awesome Site">
                                </div>

                                <div>
                                    <label for="tagline"
                                        class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
                                    <input type="text" name="tagline" id="tagline" value="{{ $settings['tagline'] ?? '' }}"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3"
                                        placeholder="Just another Laravel site">
                                    <p class="text-xs text-gray-500 mt-1">In a few words, explain what this site is about.
                                    </p>
                                </div>

                                <div>
                                    <label for="site_url" class="block text-sm font-medium text-gray-700 mb-2">Site Address
                                        (URL)</label>
                                    <input type="url" name="site_url" id="site_url"
                                        value="{{ $settings['site_url'] ?? config('app.url') }}"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3"
                                        placeholder="https://example.com">
                                </div>

                                <div>
                                    <label for="admin_path" class="block text-sm font-medium text-gray-700 mb-2">Admin
                                        Path</label>
                                    <div class="flex items-center">
                                        <span
                                            class="text-gray-500 bg-gray-50 border border-r-0 border-gray-200 rounded-l-md px-3 py-3 text-sm">{{ url('/') }}/</span>
                                        <input type="text" name="admin_path" id="admin_path"
                                            value="{{ get_setting('admin_path', 'bx-admin') }}"
                                            class="flex-1 min-w-0 block w-full px-3 py-3 rounded-none rounded-r-md border border-gray-200 bg-gray-50 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            placeholder="bx-admin">
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Changing this will immediately change your admin
                                        URL login path.</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Icon</label>
                                    <p class="text-xs text-gray-500 mb-3">The site icon is used as a browser and app icon
                                        for your site. Icons must be square and at least 512 × 512 pixels.</p>

                                    @php
                                        $siteIconId = $settings['site_icon'] ?? null;
                                        $siteIcon = $siteIconId ? \App\Models\Media::find($siteIconId) : null;
                                    @endphp

                                    <div class="flex items-start gap-4">
                                        <!-- Icon Preview -->
                                        <div
                                            class="w-24 h-24 border-2 border-gray-300 rounded-lg flex items-center justify-center bg-gray-50 overflow-hidden">
                                            <img id="siteIconPreview" src="{{ $siteIcon ? asset($siteIcon->path) : '' }}"
                                                alt="Site Icon" class="w-full h-full object-cover"
                                                style="{{ $siteIcon ? 'display: block;' : 'display: none;' }}">

                                            <svg id="siteIconPlaceholder" class="w-12 h-12 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24"
                                                style="{{ $siteIcon ? 'display: none;' : 'display: block;' }}">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                                </path>
                                            </svg>
                                        </div>

                                        <!-- Buttons -->
                                        <div class="flex flex-col gap-2">
                                            <button type="button" onclick="openMediaPicker('site_icon', 'siteIconPreview')"
                                                class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded text-sm shadow-sm transition-colors">
                                                {{ $siteIcon ? 'Change Site Icon' : 'Select Site Icon' }}
                                            </button>
                                            @if($siteIcon)
                                                <button type="button" onclick="removeSiteIcon()"
                                                    class="bg-red-50 border border-red-200 text-red-600 hover:bg-red-100 font-medium py-2 px-4 rounded text-sm transition-colors">
                                                    Remove Site Icon
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <input type="hidden" name="site_icon" id="site_icon" value="{{ $siteIconId }}">
                                </div>

                                <!-- Site Language -->
                                <div x-data="{ 
                                                search: '', 
                                                open: false, 
                                                selected: '{{ $settings['site_language'] ?? 'en' }}',
                                                languages: {
                                                    'en': 'English',
                                                    'es': 'Spanish',
                                                    'fr': 'French',
                                                    'de': 'German',
                                                    'it': 'Italian',
                                                    'pt': 'Portuguese',
                                                    'ru': 'Russian',
                                                    'zh': 'Chinese',
                                                    'ja': 'Japanese',
                                                    'ko': 'Korean',
                                                    'hi': 'Hindi',
                                                    'ar': 'Arabic',
                                                    'nl': 'Dutch',
                                                    'pl': 'Polish',
                                                    'tr': 'Turkish',
                                                    'id': 'Indonesian',
                                                    'th': 'Thai',
                                                    'vi': 'Vietnamese'
                                                },
                                                get filteredLanguages() {
                                                    if (this.search === '') {
                                                        return this.languages;
                                                    }
                                                    return Object.fromEntries(
                                                        Object.entries(this.languages).filter(([key, value]) => 
                                                            value.toLowerCase().includes(this.search.toLowerCase())
                                                        )
                                                    );
                                                },
                                                get selectedLabel() {
                                                    return this.languages[this.selected] || this.selected;
                                                }
                                            }" class="relative">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Site Language</label>
                                    <input type="hidden" name="site_language" :value="selected">

                                    <div class="relative">
                                        <button type="button"
                                            @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                            @click.away="open = false"
                                            class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-3 px-3 rounded-md text-left focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 flex justify-between items-center text-sm">
                                            <span x-text="selectedLabel"></span>
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div x-show="open"
                                            class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                            x-cloak>
                                            <div class="sticky top-0 z-10 bg-white p-2 border-b">
                                                <input type="text" x-model="search" x-ref="searchInput"
                                                    class="w-full border border-gray-200 rounded-md p-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                                                    placeholder="Search language...">
                                            </div>
                                            <ul class="py-1">
                                                <template x-for="(label, key) in filteredLanguages" :key="key">
                                                    <li @click="selected = key; open = false; search = ''"
                                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-600 hover:text-white"
                                                        :class="selected === key ? 'text-white bg-indigo-600' : 'text-gray-900'">
                                                        <span class="block truncate" x-text="label"></span>
                                                        <span x-show="selected === key"
                                                            class="absolute inset-y-0 right-0 flex items-center pr-4"
                                                            :class="selected === key ? 'text-white' : 'text-indigo-600'">
                                                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg"
                                                                viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd"
                                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                        </span>
                                                    </li>
                                                </template>
                                                <li x-show="Object.keys(filteredLanguages).length === 0"
                                                    class="py-2 px-3 text-gray-500 italic">No results found</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">Select the default language for the site.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Scripts -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Header & Footer Scripts</h3>

                            <div class="space-y-6">
                                <div>
                                    <label for="header_scripts" class="block text-sm font-medium text-gray-700 mb-2">Header
                                        Scripts (in <code>&lt;head&gt;</code>)</label>
                                    <textarea name="header_scripts" id="header_scripts" rows="5"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 font-mono text-sm"
                                        placeholder="<script>...</script>">{{ $settings['header_scripts'] ?? '' }}</textarea>
                                    <p class="text-xs text-gray-500 mt-1">Useful for Google Analytics, Meta tags,
                                        verification codes.</p>
                                </div>

                                <div>
                                    <label for="footer_scripts" class="block text-sm font-medium text-gray-700 mb-2">Footer
                                        Scripts (before <code>&lt;/body&gt;</code>)</label>
                                    <textarea name="footer_scripts" id="footer_scripts" rows="5"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 font-mono text-sm"
                                        placeholder="<script>...</script>">{{ $settings['footer_scripts'] ?? '' }}</textarea>
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
                                Settings
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mb-4">
                            Save your changes to update the global site configuration.
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

    <!-- Scripts -->
    <script>
        // Global media picker callback function
        window.mediaPickerCallback = function (inputId, previewId) {
            if (inputId === 'site_icon') {
                const preview = document.getElementById('siteIconPreview');
                const placeholder = document.getElementById('siteIconPlaceholder');

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