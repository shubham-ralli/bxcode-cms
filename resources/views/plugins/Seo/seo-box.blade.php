<div class="bg-white rounded-lg shadow-sm mt-6" x-data="{ 
        tab: 'general',
        title: '{{ addslashes(optional(optional($post)->seo)->meta_title ?? '') }}',
        desc: '{{ addslashes(optional(optional($post)->seo)->meta_description ?? '') }}',
        getTitleColor() {
            let len = this.title.length;
            if (len === 0) return 'bg-gray-200';
            if (len < 40) return 'bg-yellow-400';
            if (len <= 60) return 'bg-green-500';
            return 'bg-red-500';
        },
        getTitleWidth() {
            let len = this.title.length;
            let pct = Math.min((len / 65) * 100, 100);
            return pct + '%';
        },
        getDescColor() {
            let len = this.desc.length;
            if (len === 0) return 'bg-gray-200';
            if (len < 120) return 'bg-yellow-400';
            if (len <= 160) return 'bg-green-500';
            return 'bg-red-500';
        },
        getDescWidth() {
            let len = this.desc.length;
            let pct = Math.min((len / 160) * 100, 100);
            return pct + '%';
        },
        insertVariable(code) {
             if (this.title.length > 0 && !this.title.endsWith(' ')) {
                 this.title += ' ';
             }
             this.title += code;
        },
        // Preview Logic
        previewType: 'mobile', 
        postTitle: '{{ addslashes(optional($post)->title ?? '') }}',
        postType: '{{ optional($post)->type ?? 'post' }}',
        siteTitle: '{{ addslashes(\App\Models\Setting::get("site_title", "BxCode")) }}',
        sep: '-',
        defaultTitleTemplate: '{{ addslashes(\App\Models\Setting::get(optional($post)->type === 'page' ? "seo_page_title_template" : "seo_post_title_template", "%%title%% %%sep%% %%sitename%%")) }}',
        defaultDescTemplate: '{{ addslashes(\App\Models\Setting::get(optional($post)->type === 'page' ? "seo_page_description_template" : "seo_post_description_template", "%%excerpt%%")) }}',
        getUrl() {
             return '{{ url('/') }}/' + (document.getElementById('seoSlugInput')?.value || '{{ optional($post)->slug ?? '' }}');
        },
        getPreviewTitle() {
            let t = this.title || this.defaultTitleTemplate;
            t = t.replace(/%%title%%/g, this.postTitle)
                 .replace(/%%sitename%%/g, this.siteTitle)
                 .replace(/%%site_title%%/g, this.siteTitle)
                 .replace(/%%sep%%/g, this.sep)
                 .replace(/%%year%%/g, new Date().getFullYear())
                 .replace(/%%date%%/g, '{{ optional($post)->created_at ? optional($post)->created_at->format("F d, Y") : date("F d, Y") }}');
            
            if (t.length > 60) return t.substring(0, 57) + ' ...';
            return t;
        },
        getPreviewDesc() {
            let d = this.desc || 'Please provide a meta description by editing the snippet below. If you don’t, Google will try to find a relevant part of your post to show in the search results.';
            d = d.replace(/%%title%%/g, this.postTitle)
                 .replace(/%%site_title%%/g, this.siteTitle)
                 .replace(/%%sep%%/g, this.sep)
                 .replace(/%%year%%/g, new Date().getFullYear());
            
            if (d.length > 160) return d.substring(0, 157) + ' ...';
            return d;
        }
     }">
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-gray-700 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            SEO Settings
        </h3>
        <div class="flex space-x-1 bg-gray-100 p-1 rounded-lg">
            <button type="button" @click="tab = 'general'"
                :class="{ 'bg-white shadow-sm text-indigo-600': tab === 'general', 'text-gray-500 hover:text-gray-700': tab !== 'general' }"
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                General
            </button>
            <button type="button" @click="tab = 'advanced'"
                :class="{ 'bg-white shadow-sm text-indigo-600': tab === 'advanced', 'text-gray-500 hover:text-gray-700': tab !== 'advanced' }"
                class="px-3 py-1.5 text-xs font-medium rounded-md transition-all">
                Advanced
            </button>
        </div>
    </div>

    <div class="p-6">
        <!-- GENERAL TAB -->
        <div x-show="tab === 'general'" class="space-y-6">

            <!-- Focus Keyphrase -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Focus Keyphrase</label>
                <input type="text" name="seo[focus_keyphrase]"
                    value="{{ optional(optional($post)->seo)->focus_keyphrase ?? '' }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Enter the main keyword for this page">
                <p class="mt-1 text-xs text-gray-500">The main keyword you want this page to rank for.</p>
            </div>

            <!-- Google Preview -->
            <div class="pt-6 border-t border-gray-100 pb-6 border-b">
                <h3 class="text-sm font-medium text-gray-700 mb-4">Snippet Preview</h3>

                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <!-- Toggle -->
                    <div class="flex items-center space-x-4 mb-4">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" x-model="previewType" value="mobile"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Mobile result</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" x-model="previewType" value="desktop"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Desktop result</span>
                        </label>
                    </div>

                    <!-- Preview Card -->
                    <div class="bg-white p-4 rounded shadow-sm border border-gray-100 transition-all duration-300 mx-auto sm:mx-0"
                        :class="previewType === 'mobile' ? 'max-w-[375px]' : 'max-w-[600px]'">

                        <!-- Site Info -->
                        <div class="flex items-center gap-2 mb-1">
                            <div
                                class="bg-gray-100 rounded-full w-7 h-7 flex items-center justify-center text-xs font-bold text-gray-500">
                                {{ substr(addslashes(\App\Models\Setting::get("site_title", "B")), 0, 1) }}
                            </div>
                            <div class="flex flex-col leading-tight">
                                <span class="font-normal text-[#202124]"
                                    :class="previewType === 'mobile' ? 'text-sm' : 'text-sm'">
                                    <span x-text="siteTitle"></span>
                                </span>
                                <span class="text-xs text-[#5f6368]" x-text="getUrl()"></span>
                            </div>
                            <template x-if="previewType === 'mobile'">
                                <div class="ml-auto text-gray-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </template>
                            <template x-if="previewType === 'desktop'">
                                <div class="ml-auto text-gray-400">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z">
                                        </path>
                                    </svg>
                                </div>
                            </template>
                        </div>

                        <!-- Title -->
                        <h3 class="text-[#1a0dab] hover:underline cursor-pointer font-normal leading-snug break-words"
                            :class="previewType === 'mobile' ? 'text-lg' : 'text-xl'" x-text="getPreviewTitle()"></h3>

                        <!-- Description & Image -->
                        <!-- Mobile: Image on right. Desktop: Image usually not shown like this but we keep for now -->
                        <div class="text-[#4d5156] mt-1 leading-snug flex gap-2">
                            <div class="flex-1" :class="previewType === 'mobile' ? 'text-sm' : 'text-sm'">
                                <span class="text-gray-500 text-xs">{{ date('M d, Y') }} — </span>
                                <span x-text="getPreviewDesc()"></span>
                            </div>
                            <!-- Optional Image Thumbnail -->
                            @if(optional($post)->featured_image)
                                <img src="{{ optional($post)->featured_image_url }}"
                                    class="object-cover rounded ml-2 hidden sm:block"
                                    :class="previewType === 'mobile' ? 'w-24 h-24' : 'w-24 h-24'">
                                <!-- Adjust size if needed -->
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Title (Tag Editor) -->
            <div class="relative" x-data="{ openVars: false }">
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-medium text-gray-700">SEO Title</label>
                    <div class="relative">
                        <button type="button" @click="openVars = !openVars"
                            class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded hover:bg-indigo-100 flex items-center transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Insert variable
                        </button>
                        <!-- Dropdown -->
                        <div x-show="openVars" @click.away="openVars = false"
                            class="absolute right-0 top-full mt-1 w-48 bg-white rounded-md shadow-lg border border-gray-100 z-10 py-1"
                            style="display: none;">
                            <button type="button" @click="insertVariable('%%title%%'); openVars = false"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Page
                                Title</button>
                            <button type="button" @click="insertVariable('%%site_title%%'); openVars = false"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Site
                                Title</button>
                            <button type="button" @click="insertVariable('%%sep%%'); openVars = false"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Separator</button>
                            <button type="button" @click="insertVariable('%%year%%'); openVars = false"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Current
                                Year</button>
                        </div>
                    </div>
                </div>

                <!-- SEO Title Input -->
                <input type="text" name="seo[meta_title]" x-model="title"
                    class="w-full border bg-gray-100 p-4 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 text-gray-900"
                    :placeholder="'Default: ' + defaultTitleTemplate">

                <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full transition-all duration-300" :class="getTitleColor()"
                        :style="'width: ' + getTitleWidth()"></div>
                </div>
                <p class="text-xs text-right mt-1 text-gray-400">
                    <span x-text="title.length"></span> / 60
                </p>
            </div>

            <!-- Slug (Canonical Name) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                <div class="flex rounded-md shadow-sm">
                    <span
                        class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm">
                        {{ url('/') }}/<span id="urlParentPrefix"></span>
                    </span>
                    <!-- NAME MUST BE 'slug' for PostController -->
                    <input type="text" name="slug" id="seoSlugInput" value="{{ optional($post)->slug ?? '' }}"
                        class="seo-slug-input flex-1 min-w-0 block w-full px-3 py-2 rounded-none rounded-r-md border-gray-300 bg-white text-gray-700 sm:text-sm font-semibold"
                        placeholder="my-page-slug">
                </div>
                <p class="mt-1 text-xs text-gray-500">The URL-friendly name of the page.</p>
            </div>

            <!-- Meta Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                <textarea name="seo[meta_description]" rows="3" x-model="desc"
                    class="w-full bg-gray-100 p-4 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    :placeholder="'Default: ' + defaultDescTemplate"></textarea>
                <div class="mt-1 w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full transition-all duration-300" :class="getDescColor()"
                        :style="'width: ' + getDescWidth()"></div>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <p class="text-xs text-gray-500">Provide a short summary of the page for search results.</p>
                    <p class="text-xs text-gray-400">
                        <span x-text="desc.length"></span> / 160
                    </p>
                </div>
            </div>
        </div>

        <!-- End Google Preview Moved -->

        <!-- ADVANCED TAB -->
        <div x-show="tab === 'advanced'" class="space-y-6" style="display: none;">

            <!-- Robots Index -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Allow search engines to show this content in
                    search results?</label>
                <select name="seo[robots_index]"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="index" {{ (optional(optional($post)->seo)->robots_index ?? 'index') == 'index' ? 'selected' : '' }}>Yes
                        (current default)</option>
                    <option value="noindex" {{ (optional(optional($post)->seo)->robots_index ?? 'index') == 'noindex' ? 'selected' : '' }}>No
                    </option>
                </select>
            </div>

            <!-- Robots Follow -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Should search engines follow links on this
                    content?</label>
                <select name="seo[robots_follow]"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="follow" {{ (optional(optional($post)->seo)->robots_follow ?? 'follow') == 'follow' ? 'selected' : '' }}>Yes
                    </option>
                    <option value="nofollow" {{ (optional(optional($post)->seo)->robots_follow ?? 'follow') == 'nofollow' ? 'selected' : '' }}>No</option>
                </select>
            </div>

            <!-- Breadcrumbs Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Breadcrumbs Title</label>
                <input type="text" name="seo[breadcrumbs_title]"
                    value="{{ optional(optional($post)->seo)->breadcrumbs_title ?? '' }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">Label used in breadcrumb paths.</p>
            </div>

            <!-- Canonical URL -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                <input type="url" name="seo[canonical_url]"
                    value="{{ optional(optional($post)->seo)->canonical_url ?? '' }}"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="https://example.com/original-page">
                <p class="mt-1 text-xs text-gray-500">Leave empty to default to current URL.</p>
            </div>
        </div>
    </div>
</div>