@props([
    'post' => new \App\Models\Post(['type' => request('type', 'post')]),
    'action'
])

<form action="{{ $action }}" method="POST" class="min-h-screen bg-gray-100 pb-20">
    @csrf
    @if($post->exists)
        @method('PUT')
    @endif
    <input type="hidden" name="type" value="{{ $post->type }}">

    @php
        $isEdit = $post->exists;
        $typeInfo = request('type', $post->type ?? 'post');
        
        $title = $isEdit ? 'Edit Post' : 'Add New Post';
        
        if ($typeInfo === 'page') {
            $title = $isEdit ? 'Edit Page' : 'Add New Page';
        } elseif(isset($postTypeObj) && $postTypeObj) {
            $labels = json_decode($postTypeObj->labels, true) ?? [];
            if ($isEdit) {
                $title = $labels['edit_item'] ?? ('Edit ' . $postTypeObj->singular_label);
            } else {
                $title = $labels['add_new_item'] ?? ('Add New ' . $postTypeObj->singular_label);
            }
        }
        
        // Determine Supports
        $defaultSupports = ['title', 'editor', 'featured_image', 'excerpt', 'author', 'categories', 'tags'];
        if (isset($postTypeObj) && $postTypeObj) {
            $enabledSupports = json_decode($postTypeObj->supports, true) ?? [];
        } else {
            // Standard Post/Page defaults
            $enabledSupports = $defaultSupports;
            if ($typeInfo === 'page') {
                $enabledSupports = ['title', 'editor', 'featured_image', 'author', 'page-attributes']; // Pages usually don't have excerpt/tags/categories by default in WP, but strict parity optional
            }
        }
    @endphp

    <!-- Top Header: Title & Actions -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('admin.posts.index', ['type' => $post->type]) }}"
                        class="mr-4 text-gray-500 hover:text-gray-900 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </a>
                    <h1 class="text-xl font-bold text-gray-900">
                        @if($post->exists)
                            Edit {{ ucfirst($post->type) }}
                        @else
                            Add New {{ ucfirst($post->type) }}
                        @endif
                    </h1>
                    @if(session('success'))
                        <span
                            class="ml-3 text-sm text-green-600 font-medium animate-pulse bg-green-50 px-2 py-1 rounded">Saved</span>
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    @if($post->exists)
                        @if($post->status == 'publish' || $post->status == 'private')
                            <a href="{{ $post->url }}" target="_blank"
                                class="text-gray-600 hover:text-gray-900 font-medium text-sm flex items-center gap-1 mr-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View
                            </a>
                        @elseif($post->id)
                            <a href="{{ url('/?p=' . $post->id . '&preview=true') }}" target="_blank"
                                class="text-indigo-600 hover:text-indigo-900 font-medium text-sm flex items-center gap-1 mr-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                                Preview
                            </a>
                        @endif
                    @endif
                    
                    <!-- Mobile Sidebar Toggle -->
                    <button type="button"
                        onclick="document.getElementById('sidebar-container').classList.toggle('hidden')"
                        class="lg:hidden text-gray-500 p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16m-7 6h7"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="lg:grid lg:grid-cols-12 lg:gap-8">

            <!-- Left Column: Main Content (8 cols) -->
            <main class="lg:col-span-9 space-y-6">

                <!-- Title Input -->
                @if(supports_meta_box($post->type, 'title'))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <input type="text" name="title" value="{{ old('title', $post->title) }}" placeholder="Enter title here"
                        class="w-full bg-gray-50 p-3 text-3xl font-bold border-none focus:ring-0 placeholder-gray-300 text-gray-900"
                        required @if(!$post->exists) onkeyup="generateSlug(this.value)" @endif>
                </div>
                @else
                    <input type="hidden" name="title" value="{{ $post->title ?? 'Untitled ' . now()->toDateTimeString() }}">
                @endif

                <!-- Permalink (Editable Slug) -->
                <div class="" x-data="{
                            slug: '{{ old('slug', $post->slug) }}',
                            editing: false,
                            parentSlug: '{{ $post->parent ? $post->parent->slug : '' }}',
                            postType: '{{ $post->type }}',
                            baseUrl: '{{ url('/') }}',
                            get urlPrefix() {
                                // For pages with parent
                                if (this.postType === 'page' && this.parentSlug) {
                                    return this.baseUrl + '/' + this.parentSlug + '/';
                                }
                                
                                // For regular posts and pages (no prefix)
                                if (this.postType === 'post' || this.postType === 'page') {
                                    return this.baseUrl + '/';
                                }
                                
                                // For custom post types
                                @if(isset($postTypeObj) && $postTypeObj && property_exists($postTypeObj, 'slug'))
                                    return this.baseUrl + '/{{ $postTypeObj->slug }}/';
                                @else
                                    return this.baseUrl + '/';
                                @endif
                            },
                            startEdit() {
                                this.editing = true;
                                this.$nextTick(() => this.$refs.slugEdit.focus());
                            },
                            finishEdit() {
                                this.editing = false;
                            },
                            init() {
                                this.$nextTick(() => {
                                        const seoInput = document.querySelector('.seo-slug-input');
                                        if(seoInput) {
                                            seoInput.addEventListener('input', (e) => {
                                                this.slug = e.target.value;
                                            });
                                            if(seoInput.value !== this.slug) {
                                                seoInput.value = this.slug;
                                            }
                                        }
                                });

                                this.$watch('slug', (val) => {
                                        const seoInput = document.querySelector('.seo-slug-input');
                                        if(seoInput) {
                                            seoInput.value = val;
                                            seoInput.dispatchEvent(new Event('input'));
                                        }
                                });
                                
                                window.updateUrlPreviewParent = (parentSlug) => {
                                    this.parentSlug = parentSlug;
                                };
                            }
                        }">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Permalink</label>
                        
                        <!-- Hidden input for form submission -->
                        <input type="hidden" name="slug" id="slugInput" x-model="slug">
                        
                        <!-- Editable Permalink Display -->
                        <div class="flex items-center gap-1 p-3 bg-gray-50 rounded-md border border-gray-200 font-mono text-sm">
                            <!-- URL Prefix (non-editable) -->
                            <span class="text-gray-500" x-text="urlPrefix"></span>
                            
                            <!-- Editable Slug Portion -->
                            <div class="flex-1 min-w-0" @click.away="finishEdit()">
                                <input 
                                    x-show="editing"
                                    x-ref="slugEdit"
                                    type="text" 
                                    x-model="slug"
                                    @keydown.enter="finishEdit()"
                                    @keydown.escape="finishEdit()"
                                    class="w-full px-2 py-1 text-indigo-600 font-medium bg-white border border-indigo-300 rounded focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    placeholder="post-slug">
                                
                                <button 
                                    x-show="!editing"
                                    type="button"
                                    @click="startEdit()"
                                    class="text-indigo-600 font-medium hover:text-indigo-800 underline decoration-dotted underline-offset-2 break-all text-left">
                                    <span x-text="slug || 'edit-slug'"></span>
                                </button>
                            </div>
                            
                            <!-- Edit Button -->
                            <button 
                                x-show="!editing"
                                type="button"
                                @click="startEdit()"
                                class="flex-shrink-0 text-gray-400 hover:text-indigo-600 transition-colors"
                                title="Edit slug">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <p class="mt-2 text-xs text-gray-500">Click the slug to edit it</p>
                    </div>





                <!-- Editor -->
                @if(supports_meta_box($post->type, 'editor'))
                <div class="h-[600px] mb-8 content-editor" id="content-editor">
                    @include('admin.partials.editor', ['name' => 'content', 'value' => old('content', $post->content), 'height' => '100%'])
                </div>
                @endif

                <!-- Excerpt (Optional) -->
                @if(supports_meta_box($post->type, 'excerpt'))
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 mb-2">Excerpt</h3>
                    <textarea name="excerpt" rows="3"
                        class="w-full bg-gray-50 p-3 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Write a short summary...">{{ old('excerpt', $post->excerpt) }}</textarea>
                </div>
                @endif


                

                <!-- Plugin Hook -->
                @php 
                    do_action('admin_post_add', $post->exists ? $post : null); 
                @endphp

                <!-- Plugin Meta Boxes (Main Column) -->
                @php
                    render_meta_boxes($post->type, 'main', $post);
                @endphp

            </main>

            <!-- Right Column: Sidebar (4 cols) -->
            <aside id="sidebar-container" class="lg:col-span-3 space-y-6 hidden lg:block">

                <!-- Publish Card -->
                <div class="bg-white rounded-lg shadow-sm" x-data="{ open: true }">
                    <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center cursor-pointer" @click="open = !open">
                        <h3 class="font-semibold text-gray-700">Publish</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform" :class="{ 'rotate-180': !open }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="p-4 space-y-4" x-data="{
                        publishDate: '{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d H:i') : '') }}',
                        status: '{{ old('status', $post->status) }}',
                        get isScheduled() {
                            if (!this.publishDate) return false;
                            const pubDate = new Date(this.publishDate);
                            const now = new Date();
                            return pubDate > now && (this.status === 'publish' || this.status === 'scheduled');
                        },
                        get displayStatus() {
                            return this.isScheduled ? 'scheduled' : this.status;
                        },
                        init() {
                            // Watch for date changes
                            this.$watch('publishDate', () => {
                                // Update status display when date changes
                            });
                        }
                    }">
                        <div class="flex items-center justify-between text-sm py-1">
                            <label class="text-gray-600 font-medium">Status:</label>
                            
                            <!-- Show badge for scheduled posts -->
                            <div class="flex items-center gap-2">
                                <span x-show="isScheduled" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Scheduled
                                </span>
                                
                                <select name="status" x-model="status" x-show="!isScheduled"
                                    class="text-sm border-none bg-gray-50 rounded focus:ring-0 font-medium text-gray-700 cursor-pointer">
                                    <option value="publish">Public</option>
                                    <option value="private">Private</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>





                        <div class="flex items-center justify-between text-sm py-1">
                            <label class="text-gray-600 font-medium">Author:</label>
                            <div class="relative min-w-[150px]" id="authorSelectContainer">
                                @php
                                    $defaultAuthor = $post->author ?? (Auth::user() ?? \App\Models\User::where('role', 'admin')->first());
                                    $authorName = $defaultAuthor ? $defaultAuthor->name : 'Administrator';
                                    $authorId = $defaultAuthor ? $defaultAuthor->id : '';
                                    if(old('author_id')) {
                                        $authorId = old('author_id');
                                        // Ideally fetch name if failed validation, but simple fallback OK
                                    }
                                @endphp
                                <input type="text" id="authorSearch" placeholder="Search author..."
                                    value="{{ $authorName }}"
                                    class="w-full text-right text-sm border-none bg-gray-50 rounded focus:ring-0 font-medium text-gray-700 cursor-pointer"
                                    onclick="document.getElementById('authorDropdown').classList.remove('hidden')"
                                    onkeyup="filterAuthors()" autocomplete="off">
                                <input type="hidden" name="author_id" id="authorId" value="{{ $authorId }}">

                                <div id="authorDropdown"
                                    class="absolute right-0 z-10 w-48 bg-white border border-gray-200 rounded-md mt-1 max-h-40 overflow-y-auto hidden shadow-lg">
                                    @foreach($users as $user)
                                        <div class="author-option px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700"
                                            onclick="selectAuthor('{{ $user->id }}', '{{ addslashes($user->name) }}')">
                                            {{ $user->name }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-sm" x-data="{
                            date: '{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d H:i') : '') }}',
                            init() {
                                flatpickr(this.$refs.picker, {
                                    enableTime: true,
                                    dateFormat: 'Y-m-d H:i',
                                    altInput: true,
                                    altFormat: 'F j, Y at h:i K',
                                    defaultDate: this.date,
                                    time_24hr: false,
                                    onReady: (selectedDates, dateStr, instance) => {
                                        // Robust check to avoid duplicates or errors (e.g. re-renders)
                                        if (instance.calendarContainer.querySelector('.flatpickr-now-button-container')) {
                                            return;
                                        }

                                        const container = instance.calendarContainer;
                                        const wrapper = document.createElement('div');
                                        wrapper.className = 'flatpickr-now-button-container p-2 border-t border-gray-200 flex justify-center';
                                        
                                        const nowBtn = document.createElement('button');
                                        nowBtn.type = 'button';
                                        nowBtn.className = 'text-xs bg-indigo-50 text-indigo-600 px-3 py-1 rounded hover:bg-indigo-100 font-medium w-full transition-colors';
                                        nowBtn.textContent = 'Set to Current Time';
                                        nowBtn.addEventListener('click', () => {
                                            const now = new Date();
                                            instance.setDate(now, true);
                                            this.date = instance.input.value;
                                            // instance.close(); // Optional, user might want to adjust time further
                                        });

                                        wrapper.appendChild(nowBtn);
                                        container.appendChild(wrapper);
                                    },
                                    onChange: (selectedDates, dateStr) => {
                                        this.date = dateStr;
                                        // Notify parent Alpine component of date change
                                        this.$dispatch('date-changed', { date: dateStr });
                                    }
                                });
                            }
                        }">
                        <div class="flex items-center justify-between text-sm py-1" @date-changed.window="publishDate = $event.detail.date">
                            <label class="text-gray-600 font-medium">Publish Date:</label>
                            
                            {{-- Input --}}
                            <div class="relative max-w-[180px]">
                                <input x-ref="picker" type="text" name="published_at" value="{{ old('published_at', $post->published_at ? $post->published_at->format('Y-m-d H:i') : '') }}"
                                    class="w-full text-right text-sm border-none bg-gray-50 hover:bg-gray-100 transition-colors rounded focus:ring-0 font-medium text-gray-700 cursor-pointer placeholder-gray-500"
                                    placeholder="Immediately">
                                <button type="button" class="absolute right-0 top-1/2 -translate-y-1/2 -mr-5 text-gray-400 hover:text-red-500 transition-colors"
                                    x-show="date"
                                    @click.stop="$refs.picker._flatpickr.clear(); date = ''"
                                    title="Clear date (Publish Immediately)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </div>
                        </div>
                        @if($post->exists && $post->status !== 'trash')
                            <div class="text-right pt-2">
                                <button type="submit" form="trashForm"
                                    class="text-red-500 hover:text-red-700 text-xs font-medium">Move to Trash</button>
                            </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 px-4 py-3 border-t border-gray-100 flex justify-between items-center">
                        @if($post->exists)
                            <span class="text-xs text-gray-400">
                                {{ $post->updated_at->diffForHumans() }}
                            </span>
                        @else
                             <button type="button" class="text-gray-500 hover:text-gray-700 text-sm font-medium">Save Draft</button>
                        @endif
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded shadow-sm text-sm transition-colors">
                            {{ $post->status == 'publish' || $post->exists ? 'Update' : 'Publish' }}
                        </button>
                    </div>
                </div>

                <!-- Categories Card (Checkboxes with AJAX Add) -->
                @if(supports_meta_box($post->type, 'categories'))
                    <div class="bg-white rounded-lg shadow-sm mb-6" x-data="{
                                    categories: {{ $categories }},
                                    selected: {{ $post->exists ? $post->categories->pluck('id') : '[]' }},
                                    showAdd: false,
                                    newCatName: '',
                                    parent: null,
                                    open: true,
                                    async addCategory() {
                                        if (!this.newCatName.trim()) return;

                                        try {
                                            const response = await fetch('{{ route('admin.tags.store') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'Accept': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                        <div class="px-4 py-3 border-b border-gray-100 flex justify-between items-center cursor-pointer" @click="open = !open">
                            <h3 class="font-semibold text-gray-700">Categories</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform" :class="{ 'rotate-180': !open }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="p-4">
                            <!-- List -->
                            <div
                                class="max-h-60 overflow-y-auto border border-gray-100 rounded p-2 bg-gray-50 mb-3 space-y-2">
                                <template x-for="cat in categories" :key="cat.id">
                                    <label class="flex items-start space-x-2 cursor-pointer">
                                        <input type="checkbox" name="categories[]" :value="cat.id" x-model="selected"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">
                                        <span class="text-sm text-gray-700 select-none" x-text="cat.name"></span>
                                    </label>
                                </template>
                                <div x-show="categories.length === 0" class="text-xs text-gray-400 text-center py-2">
                                    No categories found.
                                </div>
                            </div>

                            <!-- Add New Toggle -->
                            <button type="button" @click="showAdd = !showAdd"
                                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                                <span x-text="showAdd ? '-' : '+'"></span> Add New Category
                            </button>

                            <!-- Add New Form -->
                            <div x-show="showAdd" x-transition class="mt-3">
                                <input type="text" x-model="newCatName" @keydown.enter.prevent="addCategory()"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm mb-2"
                                    placeholder="New Category Name">
                                <div class="flex justify-between items-center">
                                    <button type="button" @click="addCategory()"
                                        class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium hover:bg-gray-50">
                                        Add New Category
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tags Card -->
                @if(supports_meta_box($post->type, 'tags'))
                    <div class="bg-white rounded-lg shadow-sm" x-data="{
                                                tags: {{ $post->exists ? $post->tags->pluck('name') : '[]' }},
                                                newTag: '',
                                                availableTags: {{ $tags->pluck('name') }},
                                                filteredTags: [],
                                                open: true,
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
                                                    // Split by comma if user pasted multiple
                                                    if (this.newTag.includes(',')) {
                                                        this.newTag.split(',').forEach(t => this.addTag(t));
                                                    } else {
                                                        this.addTag(this.newTag);
                                                    }
                                                }
                                            }">
                        <div class="px-4 py-3 border-b border-gray-100 cursor-pointer flex justify-between items-center" @click="open = !open">
                            <h3 class="font-semibold text-gray-700">Tags</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform" :class="{ 'rotate-180': !open }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="p-4">
                            <!-- Selected Tags -->
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

                            <!-- Input -->
                            <div class="relative">
                                <input type="text" x-model="newTag" @keydown.enter.prevent="addFromInput()"
                                    @keydown.comma.prevent="addFromInput()"
                                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    placeholder="Add new tag (press Enter)">

                                <!-- Suggestions -->
                                <div x-show="filteredTags.length > 0" @click.away="filteredTags = []"
                                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-md mt-1 max-h-40 overflow-y-auto shadow-lg"
                                    style="display: none;">
                                    <template x-for="tag in filteredTags">
                                        <div @click="addTag(tag)"
                                            class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700">
                                            <span x-text="tag"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-2">Separate tags with commas or the Enter key.</p>
                        </div>
                    </div>
                @endif

                <!-- Page Attributes (If Page) -->
                @if(supports_meta_box($post->type, 'page_attributes'))
                    <div class="bg-white rounded-lg shadow-sm" x-data="{ open: true }">
                        <div class="px-4 py-3 border-b border-gray-100 cursor-pointer flex justify-between items-center" @click="open = !open">
                            <h3 class="font-semibold text-gray-700">Page Attributes</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform" :class="{ 'rotate-180': !open }">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                        </div>
                        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="p-4 space-y-4">
                                <label class="block text-sm text-gray-600 mb-1">Parent Page</label>
                                <div class="relative" id="parentSelectContainer">
                                    <input type="text" id="parentSearch" placeholder="Search parent..."
                                        value="{{ $post->parent ? $post->parent->title : 'None' }}"
                                        class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onclick="document.getElementById('parentDropdown').classList.remove('hidden')"
                                        onkeyup="filterPages()">
                                    <input type="hidden" name="parent_id" id="parentId" value="{{ $post->parent_id }}">

                                    <div id="parentDropdown"
                                        class="absolute z-10 w-full bg-white border border-gray-200 rounded-md mt-1 max-h-40 overflow-y-auto hidden shadow-lg">
                                        <div class="px-3 py-2 hover:bg-gray-50 cursor-pointer text-gray-400 italic text-sm"
                                            onclick="selectParent('', 'None')">None</div>
                                        @foreach($parents as $parent)
                                            <div class="parent-option px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700 {{ $parent->id == $post->parent_id ? 'bg-indigo-50 font-semibold' : '' }}"
                                                onclick="selectParent('{{ $parent->id }}', '{{ addslashes($parent->title) }}', '{{ $parent->slug }}')">
                                                {{ $parent->title }}
                                            </div>
                                        @endforeach
                                        </div>
                                </div>

                                <div class="mt-4">
                                    <label class="block text-sm text-gray-600 mb-1">Template</label>
                                    <select name="template"
                                        class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        {{-- Default --}}
                                        <option value="default" {{ ($post->template == 'default' || !$post->template) ? 'selected' : '' }}>Default Template</option>
                                        {{-- Custom Templates --}}
                                        @if(isset($templates) && count($templates) > 0)
                                            @foreach($templates as $file => $name)
                                                @if($file !== 'default')
                                                    <option value="{{ $file }}" {{ $post->template == $file ? 'selected' : '' }}>{{ $name }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>

                @endif

                <!-- Featured Image Card -->
                @if(supports_meta_box($post->type, 'featured_image'))
                <div class="bg-white rounded-lg shadow-sm" x-data="{ open: true }">
                    <div class="px-4 py-3 border-b border-gray-100 cursor-pointer flex justify-between items-center" @click="open = !open">
                        <h3 class="font-semibold text-gray-700">Featured Image</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600 transition-transform" :class="{ 'rotate-180': !open }">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                    </div>
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 transform scale-100" x-transition:leave-end="opacity-0 transform scale-95" class="p-4">
                        <div
                            class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-lg p-2 text-center hover:border-indigo-300 transition-colors cursor-pointer relative group min-h-[150px] flex items-center justify-center overflow-hidden">

                            @php $featuredUrl = $post->featured_image_url; @endphp

                            <img id="featuredPreview" src="{{ $featuredUrl }}"
                                class="w-full h-auto object-cover rounded {{ $featuredUrl ? '' : 'hidden' }}">

                            <div id="featuredPlaceholder"
                                class="flex flex-col items-center justify-center {{ $featuredUrl ? 'hidden' : '' }}">
                                <svg class="mx-auto h-10 w-10 text-gray-400" stroke="currentColor" fill="none"
                                    viewBox="0 0 48 48">
                                    <path
                                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <button type="button" onclick="openMediaPicker('featured_image', 'featuredPreview')"
                                    class="mt-2 text-sm text-indigo-600 font-medium hover:text-indigo-500">Set featured
                                    image</button>
                            </div>

                            <div id="featuredActions"
                                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity {{ $featuredUrl ? '' : 'hidden' }}">
                                <button type="button" onclick="openMediaPicker('featured_image', 'featuredPreview')"
                                    class="bg-white text-gray-700 px-3 py-1 rounded text-xs font-medium hover:bg-gray-50">Replace</button>
                                <button type="button" onclick="removeFeaturedImage()"
                                    class="bg-red-600 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-700">Remove</button>
                            </div>
                        </div>
                        <input type="hidden" name="featured_image" id="featured_image"
                            value="{{ $post->featured_image }}">
                    </div>
                </div>
                @endif

                <!-- Plugin Meta Boxes (Sidebar) -->
                @php
                    render_meta_boxes($post->type, 'side', $post);
                @endphp

            </aside>
        </div>
    </div>
</form>

<script>
    // Slug Generator
    function generateSlug(value) {
        const slug = value.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
        const input = document.getElementById('slugInput');
        if (input) {
            input.value = slug;
            input.dispatchEvent(new Event('input'));
        }
    }

    // Parent Search
    function filterPages() {
        const input = document.getElementById('parentSearch');
        const filter = input.value.toLowerCase();
        const options = document.getElementsByClassName('parent-option');
        for (let i = 0; i < options.length; i++) {
            const txt = options[i].textContent || options[i].innerText;
            options[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
    function selectParent(id, title, slug = '') {
        document.getElementById('parentId').value = id;
        document.getElementById('parentSearch').value = title;
        document.getElementById('parentDropdown').classList.add('hidden');

        // Update URL Preview with parent slug
        if (window.updateUrlPreviewParent) {
            window.updateUrlPreviewParent(slug);
        }

        // SEO Plugin Hook (Update URL Prefix)
        if (document.getElementById('urlParentPrefix')) {
            document.getElementById('urlParentPrefix').innerText = slug ? slug + '/' : '';
        }
    }
    document.addEventListener('click', function (e) {
        if (!document.getElementById('parentSelectContainer')?.contains(e.target)) {
            document.getElementById('parentDropdown')?.classList.add('hidden');
        }
        if (!document.getElementById('authorSelectContainer')?.contains(e.target)) {
            document.getElementById('authorDropdown')?.classList.add('hidden');
        }
    });

    // Author Search Logic
    function filterAuthors() {
        const input = document.getElementById('authorSearch');
        const filter = input.value.toLowerCase();
        const options = document.getElementsByClassName('author-option');
        for (let i = 0; i < options.length; i++) {
            const txt = options[i].textContent || options[i].innerText;
            options[i].style.display = txt.toLowerCase().indexOf(filter) > -1 ? "" : "none";
        }
    }
    function selectAuthor(id, name) {
        document.getElementById('authorId').value = id;
        document.getElementById('authorSearch').value = name;
        document.getElementById('authorDropdown').classList.add('hidden');
    }

    // Featured Image
    function removeFeaturedImage() {
        document.getElementById('featured_image').value = '';
        document.getElementById('featuredPreview').src = '';
        document.getElementById('featuredPreview').classList.add('hidden');
        document.getElementById('featuredPlaceholder').classList.remove('hidden');
        document.getElementById('featuredActions').classList.add('hidden');
    }

    const featuredPreview = document.getElementById('featuredPreview');
    if (featuredPreview) {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.type === "attributes" && mutation.attributeName === "src") {
                    if (featuredPreview.src && featuredPreview.src !== window.location.href) {
                        featuredPreview.classList.remove('hidden');
                        document.getElementById('featuredPlaceholder').classList.add('hidden');
                        document.getElementById('featuredActions').classList.remove('hidden');
                    }
                }
            });
        });
        observer.observe(featuredPreview, { attributes: true });
    }
</script>
@include('admin.partials.media-picker-modal')
