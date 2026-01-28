{{-- SEO Metabox --}}
<div class="bg-white rounded-lg shadow-sm border border-gray-200 mt-6 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <h3 class="font-semibold text-gray-800">SEO Settings</h3>
        </div>
        <span class="text-xs text-gray-400">Configure search engine appearance</span>
    </div>

    <div class="p-6 space-y-6">

        <!-- Snippet Preview -->
        <div class="bg-gray-50 p-4 rounded-md border border-gray-200 mb-6">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-3">Google Preview</h4>
            <div class="bg-white p-4 rounded border border-gray-100 shadow-sm max-w-2xl">
                <div class="text-sm text-gray-800 flex items-center gap-1 mb-1">
                    <span class="bg-gray-200 rounded-full w-4 h-4 block"></span>
                    <span class="text-xs text-gray-500">{{ url('/') }} > <span
                            class="text-gray-700 slug-preview">{{ $post->slug ?? 'post-url' }}</span></span>
                </div>
                <h3 class="text-xl text-[#1a0dab] font-medium hover:underline cursor-pointer mb-1 title-preview">
                    {{ $post->seo->meta_title ?? $post->title ?? 'Post Title' }}
                </h3>
                <p class="text-sm text-[#4d5156] leading-snug description-preview">
                    {{ $post->seo->meta_description ?? Str::limit(strip_tags($post->content ?? 'Post description will appear here...'), 160) }}
                </p>
            </div>
        </div>

        <!-- Form Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Focus Keyphrase</label>
                    <input type="text" name="seo[focus_keyphrase]" value="{{ $post->seo->focus_keyphrase ?? '' }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="e.g. Best Pizza Recipe">
                    <p class="mt-1 text-xs text-gray-500">The main keyword you want this post to rank for.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SEO Title</label>
                    <input type="text" name="seo[meta_title]" value="{{ $post->seo->meta_title ?? '' }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm title-input"
                        placeholder="Title to display in search results">
                    <div class="mt-1 flex justify-between text-xs text-gray-500">
                        <span>Leave empty to use post title.</span>
                        <span class="title-count">0/60</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                    <input type="text" name="slug" value="{{ $post->slug ?? '' }}"
                        class="seo-slug-input w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Meta Description</label>
                    <textarea name="seo[meta_description]" rows="4"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm description-input"
                        placeholder="A short summary of the page content.">{{ $post->seo->meta_description ?? '' }}</textarea>
                    <div class="mt-1 flex justify-between text-xs text-gray-500">
                        <span>Recommended length: 150-160 characters.</span>
                        <span class="desc-count">0/160</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Advanced Toggle -->
        <div x-data="{ advanced: false }" class="border-t border-gray-100 pt-6">
            <button type="button" @click="advanced = !advanced"
                class="flex items-center text-sm font-medium text-gray-600 hover:text-indigo-600 focus:outline-none">
                <svg class="w-4 h-4 mr-1 transition-transform" :class="{ 'rotate-90': advanced }" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                Advanced
            </button>

            <div x-show="advanced" x-cloak class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                    <input type="url" name="seo[canonical_url]" value="{{ $post->seo->canonical_url ?? '' }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="https://example.com/original-post">
                    <p class="mt-1 text-xs text-gray-500">The authoritative URL for this content.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Breadcrumbs Title</label>
                    <input type="text" name="seo[breadcrumbs_title]" value="{{ $post->seo->breadcrumbs_title ?? '' }}"
                        class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Short title for breadcrumbs">
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Simple Live Preview Logic
    const titleInput = document.querySelector('.title-input');
    const descInput = document.querySelector('.description-input');
    const titlePreview = document.querySelector('.title-preview');
    const descPreview = document.querySelector('.description-preview');
    const slugInput = document.querySelector('input[name="slug"]'); // Defined in main form
    const slugPreview = document.querySelector('.slug-preview');

    // Title
    if (titleInput) {
        titleInput.addEventListener('input', function () {
            titlePreview.textContent = this.value || "{{ $post->title ?? 'Post Title' }}";
        });
    }

    // Description
    if (descInput) {
        descInput.addEventListener('input', function () {
            descPreview.textContent = this.value || "{{ Str::limit(strip_tags($post->content ?? 'Post description...'), 160) }}";
        });
    }

    // Slug (from main form if exists)
    if (slugInput) {
        slugInput.addEventListener('input', function () {
            slugPreview.textContent = this.value;
        });
    }
</script>