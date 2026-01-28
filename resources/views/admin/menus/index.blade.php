@extends('admin.components.admin')

@section('title', 'Menus')
@section('header', 'Menus')

@section('content')
    <div class="flex flex-col md:flex-row gap-6" x-data="menuManager()">

        <!-- LEFT COLUMN: ADD ITEMS -->
        <div class="w-full md:w-1/3 space-y-4">

            <!-- Accordions for Item Types -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <!-- Pages -->
                <div class="border-b border-gray-100">
                    <button @click="toggle('pages')"
                        class="w-full px-4 py-3 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                        <span class="font-medium text-gray-700">Pages</span>
                        <svg class="w-4 h-4 text-gray-400 transform transition-transform"
                            :class="activeSection === 'pages' ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="activeSection === 'pages'" class="p-4" style="display: none;">
                        <div class="space-y-2 max-h-48 overflow-y-auto mb-3">
                            @foreach($pages as $page)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" value="{{ $page->id }}" data-title="{{ $page->title }}"
                                        data-url="{{ url($page->slug) }}"
                                        class="page-checkbox rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm text-gray-700">{{ $page->title }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                            <button type="button" @click="selectAll('.page-checkbox')"
                                class="text-xs text-indigo-600 hover:underline">Select All</button>
                            <button type="button" @click="addPages()"
                                class="px-3 py-1.5 bg-white border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">Add
                                to Menu</button>
                        </div>
                    </div>
                </div>

                <!-- Posts -->
                <div class="border-b border-gray-100">
                    <button @click="toggle('posts')"
                        class="w-full px-4 py-3 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                        <span class="font-medium text-gray-700">Posts</span>
                        <svg class="w-4 h-4 text-gray-400 transform transition-transform"
                            :class="activeSection === 'posts' ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="activeSection === 'posts'" class="p-4" style="display: none;">
                        <div class="space-y-2 max-h-48 overflow-y-auto mb-3">
                            @foreach($posts as $post)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" value="{{ $post->id }}" data-title="{{ $post->title }}"
                                        data-url="{{ url('post/' . $post->slug) }}"
                                        class="post-checkbox rounded border-gray-300 text-indigo-600">
                                    <span class="text-sm text-gray-700">{{ $post->title }}</span>
                                </label>
                            @endforeach
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                            <button type="button" @click="selectAll('.post-checkbox')"
                                class="text-xs text-indigo-600 hover:underline">Select All</button>
                            <button type="button" @click="addPosts()"
                                class="px-3 py-1.5 bg-white border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">Add
                                to Menu</button>
                        </div>
                    </div>
                </div>

                <!-- Custom Links -->
                <div>
                    <button @click="toggle('custom')"
                        class="w-full px-4 py-3 flex justify-between items-center bg-gray-50 hover:bg-gray-100 transition-colors">
                        <span class="font-medium text-gray-700">Custom Links</span>
                        <svg class="w-4 h-4 text-gray-400 transform transition-transform"
                            :class="activeSection === 'custom' ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="activeSection === 'custom'" class="p-4" style="display: none;">
                        <div class="space-y-3 mb-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">URL</label>
                                <input type="text" x-model="customUrl" placeholder="https://"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500 mb-1">Link Text</label>
                                <input type="text" x-model="customText" placeholder="Menu Item"
                                    class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div class="flex justify-end pt-2 border-t border-gray-100">
                            <button type="button" @click="addCustomLink()"
                                class="px-3 py-1.5 bg-white border border-gray-300 rounded text-sm font-medium hover:bg-gray-50">Add
                                to Menu</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: MENU STRUCTURE -->
        <div class="w-full md:w-2/3">

            <!-- Menu Selector / Creator -->
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('admin.menus.index') }}" method="GET"
                    class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Select a menu to edit:</label>
                    <select name="menu" onchange="this.form.submit()"
                        class="text-sm border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($menus as $menu)
                            <option value="{{ $menu->id }}" {{ $selectedMenu && $selectedMenu->id == $menu->id ? 'selected' : '' }}>{{ $menu->name }}</option>
                        @endforeach
                    </select>
                    <button type="submit"
                        class="px-3 py-2 bg-indigo-50 text-indigo-700 rounded-md text-sm font-medium hover:bg-indigo-100">Select</button>
                </form>
                <div class="text-sm text-gray-500">or <a href="#" @click.prevent="showCreateModal = true"
                        class="text-indigo-600 hover:underline">create a new menu</a>.</div>
            </div>

            @if($selectedMenu)
                <div class="bg-white rounded-lg shadow-sm border border-gray-100">
                    <div class="border-b border-gray-100 px-6 py-4 flex justify-between items-center bg-gray-50">
                        <div>
                            <h3 class="font-medium text-gray-900">Menu Structure</h3>
                            <p class="text-xs text-gray-500">Drag each item into the order you prefer.</p>
                        </div>
                        <form action="{{ route('admin.menus.destroy', $selectedMenu->id) }}" method="POST"
                            onsubmit="return confirm('Delete this menu?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 text-xs hover:underline">Delete Menu</button>
                        </form>
                    </div>

                    <div class="p-6">
                        <!-- Drop Zone -->
                        <ul id="menu-sortable" class="menu-sortable min-h-[200px]">
                             @if(isset($menuItems) && count($menuItems) > 0)
                                @include('admin.menus.menu-item-loop', ['items' => $menuItems])
                             @endif
                        </ul>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                        <div class="mt-2 text-sm text-gray-500">
                            <!-- Locations -->
                            <h4 class="font-medium text-gray-700 mb-2">Display Location</h4>
                            <div class="space-y-2">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="location-primary" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ isset($locations['primary']) && $locations['primary'] == $selectedMenu->id ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-600">Primary Menu</span>
                                </label>
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="location-footer" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        {{ isset($locations['footer']) && $locations['footer'] == $selectedMenu->id ? 'checked' : '' }}>
                                    <span class="text-sm text-gray-600">Footer Menu</span>
                                </label>
                            </div>
                        </div>
                        <button type="button" @click="saveMenu()"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-lg shadow-sm transition-colors"
                            :class="{'opacity-50 cursor-wait': saving}">
                            <span x-show="!saving">Save Menu</span>
                            <span x-show="saving">Saving...</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
                    <h3 class="text-lg font-medium text-blue-900">Create your first menu!</h3>
                    <p class="text-blue-700 mt-2">Enter a menu name above to get started.</p>
                    <button @click="showCreateModal = true"
                        class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Create Menu</button>
                </div>
            @endif
        </div>

        <!-- Create Menu Modal -->
        <div x-show="showCreateModal" style="display: none;"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm" @click.away="showCreateModal = false">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Create New Menu</h3>
                <form action="{{ route('admin.menus.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Menu Name</label>
                        <input type="text" name="name" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showCreateModal = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-md">Create
                            Menu</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Hidden form for adding items -->
        <form id="addItemsForm" action="{{ route('admin.menus.addItem') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="menu_id" value="{{ $selectedMenu->id ?? '' }}">
            <!-- Inputs will be appended via JS -->
        </form>
    </div>

    <!-- Nested Sortable CSS & JS -->
    <style>
        .menu-sortable { min-height: 50px; }
        .menu-sortable ul { min-height: 50px; margin-left: 30px; border-left: 1px dashed #e5e7eb; padding-left: 10px; }
        .menu-item .handle { cursor: move; }
        .menu-item .settings-panel { display: none; }
        .menu-item.open > .settings-panel { display: block; }
        .sortable-ghost { opacity: 0.4; background: #e0e7ff; }
        .sortable-drag { cursor: grabbing; }
    </style>
    <!-- Use nested sortable compatible library -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        document.addEventListener('alpine:init', () => {
             Alpine.data('menuManager', () => ({
                activeSection: 'pages',
                customUrl: 'http://',
                customText: '',
                showCreateModal: false,
                saving: false,

                toggle(section) {
                    this.activeSection = this.activeSection === section ? null : section;
                },

                selectAll(selector) {
                    document.querySelectorAll(selector).forEach(el => el.checked = true);
                },

                // Toggle settings panel for an item
                toggleItem(btn) {
                     const item = btn.closest('.menu-item');
                     item.classList.toggle('open');
                     const svg = btn.querySelector('svg');
                     if(item.classList.contains('open')) {
                         svg.style.transform = 'rotate(180deg)';
                     } else {
                         svg.style.transform = 'rotate(0deg)';
                     }
                },

                init() {
                    this.initSortable(document.getElementById('menu-sortable'));
                },

                initSortable(el) {
                    if (!el) return;
                    new Sortable(el, {
                        group: 'nested',
                        animation: 150,
                        handle: '.handle',
                        fallbackOnBody: true,
                        swapThreshold: 0.65,
                        ghostClass: 'sortable-ghost',
                        onEnd: function (evt) {
                            // Check nesting logic if needed, but styling handles visual indentation
                        }
                    });
                     
                    // Init sub-lists if any existing
                     el.querySelectorAll('ul').forEach(ul => {
                         this.initSortable(ul);
                     });
                },

                addPages() { this.submitItems('.page-checkbox:checked', 'page'); },
                addPosts() { this.submitItems('.post-checkbox:checked', 'post'); },
                
                addCustomLink() {
                    if (!this.customUrl || !this.customText) {
                         alert('Please enter both URL and Link Text');
                         return;
                    }
                    this.addToFormAndSubmit([
                         { title: this.customText, url: this.customUrl, type: 'custom', id: null }
                    ]);
                },

                submitItems(selector, type) {
                    const checkboxes = document.querySelectorAll(selector);
                    if (checkboxes.length === 0) return;
                    
                    const items = [];
                    checkboxes.forEach(cb => {
                         items.push({
                              title: cb.dataset.title,
                              url: cb.dataset.url,
                              type: type,
                              id: cb.value
                         });
                    });
                    this.addToFormAndSubmit(items);
                },

                addToFormAndSubmit(items) {
                     const form = document.getElementById('addItemsForm');
                     form.innerHTML = '@csrf <input type="hidden" name="menu_id" value="{{ $selectedMenu->id ?? '' }}">';
                     
                     items.forEach((item, index) => {
                          this.createHiddenInput(form, `items[${index}][id]`, item.id);
                          this.createHiddenInput(form, `items[${index}][title]`, item.title);
                          this.createHiddenInput(form, `items[${index}][url]`, item.url);
                          this.createHiddenInput(form, `items[${index}][type]`, item.type);
                     });
                     form.submit();
                },

                createHiddenInput(form, name, value) {
                     if(value === null) return;
                     const input = document.createElement('input');
                     input.type = 'hidden';
                     input.name = name;
                     input.value = value;
                     form.appendChild(input);
                },

                async saveMenu() {
                    this.saving = true;
                    
                    // Recursive function to build tree
                    const getItems = (ul) => {
                        const items = [];
                        // Direct children LIs only
                        Array.from(ul.children).forEach((li, index) => {
                             if(li.tagName === 'LI') {
                                  const item = {
                                      id: li.dataset.id,
                                      // Note: "order" is implicit by array position
                                      children: []
                                  };
                                  
                                  // Check for sub-ul
                                  const subUl = li.querySelector('ul');
                                  if(subUl) {
                                       item.children = getItems(subUl);
                                  }
                                  items.push(item);
                             }
                        });
                        return items;
                    };

                    const tree = getItems(document.getElementById('menu-sortable'));

                    try {
                        const response = await fetch('{{ route('admin.menus.updateTree') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                menu_id: '{{ $selectedMenu->id ?? 0 }}',
                                tree: tree,
                                locations: {
                                    primary: document.getElementById('location-primary')?.checked || false,
                                    footer: document.getElementById('location-footer')?.checked || false
                                }
                            })
                        });

                        if (response.ok) {
                            showNotification('Menu saved successfully!', 'success');
                        } else {
                            showNotification('Error saving menu.', 'error');
                        }
                    } catch (e) {
                        console.error(e);
                        showNotification('Error saving menu.', 'error');
                    }
                    this.saving = false;
                }
            }));
        });

        function showNotification(message, type = 'success') {
            // Create container if not exists
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = 'fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none';
                document.body.appendChild(container);
            }

            // Create Toast
            const toast = document.createElement('div');
            toast.className = `bg-white border-l-4 ${type === 'success' ? 'border-green-500' : 'border-red-500'} shadow-lg rounded p-4 flex items-center pointer-events-auto transition-all duration-300 transform translate-y-2 opacity-0`;
            
            const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
            const title = type === 'success' ? 'Success' : 'Error';
            
            toast.innerHTML = `
                <svg class="w-6 h-6 ${iconColor} mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${type === 'success' ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' : 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'}"></path>
                </svg>
                <div>
                    <p class="font-medium text-gray-800">${title}</p>
                    <p class="text-sm text-gray-600">${message}</p>
                </div>
            `;

            container.appendChild(toast);

            // Animate In
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            // Animate Out & Remove
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
@endsection