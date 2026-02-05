<?php
/**
 * Core Meta Boxes Registration
 * 
 * This file registers all core meta boxes (Featured Image, Tags, Categories, Page Attributes)
 * using the dynamic meta box system.
 * 
 * Load this file in: bootstrap/app.php or a service provider
 */

// Register core meta boxes on init
add_action('init', function () {

    // ===== FEATURED IMAGE META BOX =====
    add_meta_box(
        'featured_image',
        'Featured Image',
        function ($post) {
            $featuredUrl = $post->featured_image_url;
            ?>
        <div class="p-4">
            <div
                class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-2 text-center hover:border-indigo-300 transition-colors cursor-pointer relative group min-h-[150px] flex items-center justify-center overflow-hidden">

                <img id="featuredPreview" src="<?= $featuredUrl ?>"
                    class="w-full h-auto object-cover rounded <?= $featuredUrl ? '' : 'hidden' ?>">

                <div id="featuredPlaceholder"
                    class="flex flex-col items-center justify-center <?= $featuredUrl ? 'hidden' : '' ?>">
                    <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path
                            d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <button type="button" onclick="openMediaPicker('featured_image', 'featuredPreview')"
                        class="mt-2 text-sm text-indigo-600 font-medium hover:text-indigo-500">Set featured image</button>
                </div>

                <div id="featuredActions"
                    class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity <?= $featuredUrl ? '' : 'hidden' ?>">
                    <button type="button" onclick="openMediaPicker('featured_image', 'featuredPreview')"
                        class="bg-white text-gray-700 px-3 py-1 rounded text-xs font-medium hover:bg-gray-50">Replace</button>
                    <button type="button" onclick="removeFeaturedImage()"
                        class="bg-red-600 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-700">Remove</button>
                </div>
            </div>
            <input type="hidden" name="featured_image" id="featured_image" value="<?= $post->featured_image ?>">
        </div>
        <?php
        },
        ['post', 'page'],
        'side',
        30
    );

    // ===== CATEGORIES META BOX =====
    add_meta_box(
        'categories',
        'Categories',
        function ($post) {
            $categories = \App\Models\Tag::where('taxonomy', 'category')->orderBy('name')->get();
            ?>
        <div class="p-4" x-data="{
                categories: <?= json_encode($categories) ?>,
                selected: <?= $post->exists ? json_encode($post->categories->pluck('id')->toArray()) : '[]' ?>,
                showAdd: false,
                newCatName: '',
                async addCategory() {
                    if (!this.newCatName.trim()) return;
                    try {
                        const response = await fetch('<?= route('admin.tags.store') ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                            },
                            body: JSON.stringify({
                                name: this.newCatName,
                                taxonomy: 'category'
                            })
                        });
                        if (response.ok) {
                            const data = await response.json();
                            this.categories.unshift(data);
                            this.selected.push(data.id);
                            this.newCatName = '';
                            this.showAdd = false;
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Failed to add category');
                    }
                }
            }">
            <div class="max-h-60 overflow-y-auto border border-gray-100 rounded p-2 bg-gray-50 mb-3 space-y-2">
                <template x-for="cat in categories" :key="cat.id">
                    <label class="flex items-start space-x-2 cursor-pointer">
                        <input type="checkbox" name="categories[]" :value="cat.id" x-model="selected"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">
                        <span class="text-sm text-gray-700 select-none" x-text="cat.name"></span>
                    </label>
                </template>
                <div x-show="categories.length === 0" class="text-xs text-gray-400 text-center py-2">No categories found.</div>
            </div>

            <button type="button" @click="showAdd = !showAdd"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                <span x-text="showAdd ? '-' : '+'"></span> Add New Category
            </button>

            <div x-show="showAdd" x-transition class="mt-3">
                <input type="text" x-model="newCatName" @keydown.enter.prevent="addCategory()"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm mb-2"
                    placeholder="New Category Name">
                <div class="flex justify-between items-center">
                    <button type="button" @click="addCategory()"
                        class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium hover:bg-gray-50">Add
                        New Category</button>
                </div>
            </div>
        </div>
        <?php
        },
        'post',
        'side',
        20
    );

    // ===== TAGS META BOX =====
    add_meta_box(
        'tags',
        'Tags',
        function ($post) {
            $tags = \App\Models\Tag::where('taxonomy', 'post_tag')->orderBy('name')->get();
            ?>
        <div class="p-4" x-data="{
                tags: <?= $post->exists ? json_encode($post->tags->pluck('name')->toArray()) : '[]' ?>,
                newTag: '',
                availableTags: <?= json_encode($tags->pluck('name')->toArray()) ?>,
                filteredTags: [],
                init() {
                    this.$watch('newTag', (val) => {
                        if (val.length < 1) {
                            this.filteredTags = [];
                            return;
                        }
                        this.filteredTags = this.availableTags.filter(tag => 
                            tag.toLowerCase().includes(val.toLowerCase()) && !this.tags.includes(tag)
                        );
                    });
                },
                addTag(tag) {
                    tag = tag.trim();
                    if (tag !== '' && !this.tags.includes(tag)) {
                        this.tags.push(tag);
                    }
                    this.newTag = '';
                    this.filteredTags = [];
                },
                removeTag(index) {
                    this.tags.splice(index, 1);
                },
                addFromInput() {
                    if (this.newTag.includes(',')) {
                        this.newTag.split(',').forEach(t => this.addTag(t));
                    } else {
                        this.addTag(this.newTag);
                    }
                }
            }">
            <div class="flex flex-wrap gap-2 mb-3">
                <template x-for="(tag, index) in tags" :key="index">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        <span x-text="tag"></span>
                        <button type="button" @click="removeTag(index)"
                            class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white inline-flex items-center justify-center">
                            <span class="sr-only">Remove tag</span>
                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                            </svg>
                        </button>
                        <input type="hidden" name="tags[]" :value="tag">
                    </span>
                </template>
            </div>

            <div class="relative">
                <input type="text" x-model="newTag" @keydown.enter.prevent="addFromInput()"
                    @keydown.comma.prevent="addFromInput()"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Add new tag (press Enter)">

                <div x-show="filteredTags.length > 0" @click.away="filteredTags = []"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-md mt-1 max-h-40 overflow-y-auto shadow-lg"
                    style="display: none;">
                    <template x-for="tag in filteredTags">
                        <div @click="addTag(tag)" class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700">
                            <span x-text="tag"></span>
                        </div>
                    </template>
                </div>
            </div>

            <p class="text-xs text-gray-500 mt-2">Separate tags with commas or the Enter key.</p>
        </div>
        <?php
        },
        'post',
        'side',
        21
    );

    // ===== PAGE ATTRIBUTES META BOX =====
    add_meta_box(
        'page_attributes',
        'Page Attributes',
        function ($post) {
            $parents = \App\Models\Post::where('type', 'page')
                ->where('status', 'publish')
                ->where('id', '!=', $post->id ?? 0)
                ->get();

            $templates = app(\App\Http\Controllers\PostController::class)->getAvailableTemplates();
            ?>
        <div class="p-4 space-y-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Parent Page</label>
                <div class="relative" id="parentSelectContainer">
                    <input type="text" id="parentSearch" placeholder="Search parent..."
                        value="<?= $post->parent ? $post->parent->title : 'None' ?>"
                        class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                        onclick="document.getElementById('parentDropdown').classList.remove('hidden')" onkeyup="filterPages()">
                    <input type="hidden" name="parent_id" id="parentId" value="<?= $post->parent_id ?>">

                    <div id="parentDropdown"
                        class="absolute z-10 w-full bg-white border border-gray-200 rounded-md mt-1 max-h-40 overflow-y-auto hidden shadow-lg">
                        <div class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-gray-400 italic text-sm"
                            onclick="selectParent('', 'None')">None</div>
                        <?php foreach ($parents as $parent): ?>
                            <div class="parent-option px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700 <?= $parent->id == $post->parent_id ? 'bg-indigo-50 font-semibold' : '' ?>"
                                onclick="selectParent('<?= $parent->id ?>', '<?= addslashes($parent->title) ?>', '<?= $parent->slug ?>')">
                                <?= $parent->title ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Template</label>
                <select name="template"
                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="default" <?= ($post->template == 'default' || !$post->template) ? 'selected' : '' ?>>Default
                        Template</option>
                    <?php if (isset($templates) && count($templates) > 0): ?>
                        <?php foreach ($templates as $file => $name): ?>
                            <?php if ($file !== 'default'): ?>
                                <option value="<?= $file ?>" <?= $post->template == $file ? 'selected' : '' ?>>
                                    <?= $name ?>
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>
        <?php
        },
        'page',
        'side',
        25
    );

}, 5); // Priority 5 - Load early
