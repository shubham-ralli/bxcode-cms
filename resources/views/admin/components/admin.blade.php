<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') - BxCode CMS</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteIconId = \App\Models\Setting::get('site_icon');
        $siteIcon = $siteIconId ? \App\Models\Media::find($siteIconId) : null;
    @endphp

    @if($siteIcon)
        <link rel="icon" type="{{ $siteIcon->mime_type }}" href="{{ asset($siteIcon->path) }}">
        <link rel="apple-touch-icon" href="{{ asset($siteIcon->path) }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>

    @php
        // Fetch Custom Taxonomies globally for sidebar if ACF is active
        $sidebarTaxonomies = collect();
        $sidebarCPTs = collect();
        if (function_exists('plugin_is_active') && plugin_is_active('ACF')) {
            if (\Illuminate\Support\Facades\Schema::hasTable('custom_taxonomies')) {
                $sidebarTaxonomies = \Plugins\ACF\src\Models\CustomTaxonomy::where('active', 1)->get();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('custom_post_types')) {
                $sidebarCPTs = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('active', 1)->get();
            }
        }

        // Resolve Active Post Type (for sidebar highlighting)
        $activePostType = request('post_type', request('type'));
        if (empty($activePostType) && request('post')) {
            $editingPost = \App\Models\Post::find(request('post'));
            if ($editingPost) {
                $activePostType = $editingPost->type;
            }
        }
        // Fallback for Posts menu default
        if (empty($activePostType)) {
            $activePostType = 'post';
        }
    @endphp

    <!-- Custom Admin Styles -->
    <!-- Custom Admin Styles -->
    <link rel="stylesheet" href="{{ url('public/css/admin-custom.css') }}">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            margin-top: 32px;
            /* Space for Admin Bar */
        }

        /* Smooth transitions for width and margin */
        #adminSidebar,
        #adminMain,
        .sidebar-label,
        #sidebarLogo {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-collapsed .sidebar-label {
            display: none;
            opacity: 0;
        }

        .sidebar-collapsed #sidebarLogo {
            font-size: 1.25rem;
            /* smaller font */
            text-align: center;
        }

        .sidebar-collapsed #sidebarLogo .logo-full {
            display: none;
        }

        .sidebar-collapsed #sidebarLogo .logo-short {
            display: inline-block !important;
        }

        /* Submenu Styling */
        .submenu-inline {
            background-color: #1e293b;
            /* slate-800 */
        }

        .submenu-item {
            position: relative;
            padding-left: 2rem !important;
        }

        .submenu-item::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 50%;
            width: 4px;
            height: 4px;
            background-color: #94a3b8;
            /* slate-400 */
            border-radius: 50%;
            transform: translateY(-50%);
        }

        /* Flyout Logic */
        .flyout-menu {
            display: none;
            /* Default hidden */
        }

        .sidebar-collapsed .submenu-inline {
            display: none !important;
        }

        /* When collapsed, flyout becomes fixed to escape overflow clipping */
        .sidebar-collapsed .flyout-menu {
            position: fixed;
            left: 5rem;
            /* w-20 = 5rem */
            width: 200px;
            background-color: #1e293b;
            /* slate-800 */
            border-radius: 0 0.5rem 0.5rem 0;
            box-shadow: 4px 0 6px -1px rgba(0, 0, 0, 0.1);
            z-index: 100;
            pointer-events: none;
            /* Initially none to prevent flickering, JS handles visibility */
            opacity: 0;
            transition: opacity 0.1s;
        }

        .sidebar-collapsed .flyout-menu.active {
            display: block;
            pointer-events: auto;
            opacity: 1;
        }

        .flyout-header {
            display: block;
            padding: 0.75rem 1rem;
            background-color: #0f172a;
            /* slate-900 */
            font-weight: 600;
            font-size: 0.875rem;
            color: #fff;
            border-bottom: 1px solid #334155;
        }

        /* Hide chevron when collapsed */
        .sidebar-collapsed .dropdown-chevron {
            display: none;
        }

        /* Custom Scrollbar to prevent ugly horizontal bars */
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: #0f172a;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
</head>

<body class="bg-gray-50 flex font-sans text-gray-900">

    @include('partials.admin-bar')

    <div class="flex w-full"> <!-- Flex wrapper for layout -->




        <!-- Sidebar -->
        <aside id="adminSidebar"
            class="w-64 bg-slate-900 text-slate-300 flex flex-col fixed top-8 h-[calc(100%-2rem)] z-10 shadow-xl transition-all duration-300">

            <!-- Sidebar Header & Toggle -->
            <div class="p-2 border-b border-slate-800 flex items-center justify-between">
                <h1 id="sidebarLogo"
                    class="text-2xl font-bold text-white tracking-tight whitespace-nowrap overflow-hidden">
                    <span class="logo-full">BxCode</span>
                    <span class="logo-short hidden">BX</span>
                </h1>
                <button id="sidebarToggle" class="text-slate-500 hover:text-white focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16">
                        </path>
                    </svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-4 overflow-x-visible custom-scrollbar">
                <ul class="space-y-1 px-3">
                    <li class="group relative">
                        <a href="{{ route('admin.dashboard') }}"
                            class="flex items-center px-2 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-500 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}"
                            title="Dashboard">
                            <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            <span class="sidebar-label whitespace-nowrap">Dashboard</span>
                        </a>
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Dashboard</div>
                        </div>
                    </li>
                    {{-- Posts Menu (Dropdown Style to match Users) --}}
                    <li class="group relative"
                        x-data="{ open: {{ $activePostType === 'post' && (request()->routeIs('admin.posts.*') || request()->routeIs('admin.tags.*')) ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ $activePostType === 'post' && (request()->routeIs('admin.posts.*') || request()->routeIs('admin.tags.*')) ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-300' }}"
                            title="Posts">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ $activePostType === 'post' && (request()->routeIs('admin.posts.*') || request()->routeIs('admin.tags.*')) ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z">
                                    </path>
                                </svg>
                                <span class="sidebar-label whitespace-nowrap">Posts</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Inline Submenu -->
                        <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                            style="display: {{ $activePostType === 'post' && (request()->routeIs('admin.posts.*') || request()->routeIs('admin.tags.*')) ? 'block' : 'none' }}">
                            <li>
                                <a href="{{ route('admin.posts.index', ['type' => 'post']) }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ $activePostType == 'post' && request()->routeIs('admin.posts.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    All Posts
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.posts.create', ['type' => 'post']) }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.posts.create') && $activePostType == 'post' ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Add New
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.tags.index', ['taxonomy' => 'category']) }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.tags.index') && request('taxonomy') === 'category' ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Categories
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.tags.index', ['taxonomy' => 'post_tag']) }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.tags.index') && request('taxonomy') === 'post_tag' ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Tags
                                </a>
                            </li>
                            {{-- Custom Taxonomies for Posts --}}
                            @foreach($sidebarTaxonomies as $tax)
                                @if(in_array('post', $tax->post_types ?? []))
                                    <li>
                                        <a href="{{ route('admin.tags.index', ['taxonomy' => $tax->key]) }}"
                                            class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.tags.index') && request('taxonomy') === $tax->key ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                            {{ $tax->plural_label }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        <!-- Flyout Menu -->
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Posts</div>
                            <ul class="py-2">
                                <li>
                                    <a href="{{ route('admin.posts.index', ['type' => 'post']) }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">All
                                        Posts</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.posts.create', ['type' => 'post']) }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">Add
                                        New</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.tags.index', ['taxonomy' => 'post_tag']) }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">Tags</a>
                                </li>
                                {{-- Custom Taxonomies Flyout --}}
                                @foreach($sidebarTaxonomies as $tax)
                                    @if(in_array('post', $tax->post_types ?? []))
                                        <li>
                                            <a href="{{ route('admin.tags.index', ['taxonomy' => $tax->key]) }}"
                                                class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">{{ $tax->plural_label }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    </li>
                    <li class="group relative" x-data="{ open: {{ $activePostType === 'page' ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ $activePostType === 'page' ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-300' }}"
                            title="Pages">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ $activePostType === 'page' ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <span class="sidebar-label whitespace-nowrap">Pages</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Inline Submenu -->
                        <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                            style="display: {{ $activePostType === 'page' ? 'block' : 'none' }}">
                            <li>
                                <a href="{{ route('admin.posts.index', ['type' => 'page']) }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ $activePostType == 'page' && request()->routeIs('admin.posts.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    All Pages
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.posts.create', ['type' => 'page']) }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.posts.create') && $activePostType == 'page' ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Add New
                                </a>
                            </li>
                            {{-- Custom Taxonomies for Pages --}}
                            @foreach($sidebarTaxonomies as $tax)
                                @if(in_array('page', $tax->post_types ?? []))
                                    <li>
                                        <a href="{{ route('admin.tags.index', ['taxonomy' => $tax->key]) }}"
                                            class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.tags.index') && request('taxonomy') === $tax->key ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                            {{ $tax->plural_label }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>

                        <!-- Flyout Menu -->
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Pages</div>
                            <ul class="py-2">
                                <li>
                                    <a href="{{ route('admin.posts.index', ['type' => 'page']) }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">All
                                        Pages</a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.posts.create', ['type' => 'page']) }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">Add
                                        New</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- Dynamic Custom Post Types --}}
                    @foreach($sidebarCPTs as $cpt)
                        @php
                            $settings = json_decode($cpt->settings, true) ?? [];
                            if (empty($settings['show_in_menu']))
                                continue;

                            $icon = $settings['menu_icon'] ?? '<svg class="w-5 h-5 min-w-[1.25rem] mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>';
                            $labels = json_decode($cpt->labels ?? '{}', true) ?? [];
                            $menuName = $labels['menu_name'] ?? $cpt->plural_label;
                            $allItems = $labels['all_items'] ?? ("All " . $cpt->plural_label);
                            $addNew = $labels['add_new'] ?? "Add New";

                            // Active State Logic: Matches ?type=slug AND (posts.* or tags.*)
                            $isActive = ($activePostType == $cpt->key) && (request()->routeIs('admin.posts.*') || request()->routeIs('admin.tags.*'));
                        @endphp

                        <li class="group relative" x-data="{ open: @json($isActive) }">
                            <button @click="open = !open"
                                class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ $isActive ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-300' }}"
                                title="{{ $menuName }}">
                                <div class="flex items-center">
                                    <div
                                        class="w-5 h-5 min-w-[1.25rem] mr-3 {{ $isActive ? 'text-white' : 'text-slate-500 group-hover:text-white' }}">
                                        {!! str_replace('class="w-5 h-5"', 'class="w-full h-full"', $icon) !!}
                                    </div>
                                    <span class="sidebar-label whitespace-nowrap">{{ $menuName }}</span>
                                </div>
                                <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                    :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Inline Submenu -->
                            <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                                style="display: {{ $isActive ? 'block' : 'none' }}">
                                <li>
                                    <a href="{{ route('admin.posts.index', ['type' => $cpt->key]) }}"
                                        class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ $activePostType == $cpt->key && request()->routeIs('admin.posts.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                        {{ $allItems }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.posts.create', ['type' => $cpt->key]) }}"
                                        class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.posts.create') && $activePostType == $cpt->key ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                        {{ $addNew }}
                                    </a>
                                </li>
                                {{-- Custom Taxonomies for CPT --}}
                                @foreach($sidebarTaxonomies as $tax)
                                    @if(is_array($tax->post_types) && in_array($cpt->key, $tax->post_types))
                                        <li>
                                            <a href="{{ route('admin.tags.index', ['taxonomy' => $tax->key, 'type' => $cpt->key]) }}"
                                                class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.tags.index') && request('taxonomy') === $tax->key && $activePostType == $cpt->key ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                                {{ $tax->plural_label }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>

                            {{-- Flyout Menu --}}
                            <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                                <div class="flyout-header">{{ $menuName }}</div>
                                <ul class="py-2">
                                    <li><a href="{{ route('admin.posts.index', ['type' => $cpt->key]) }}"
                                            class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">{{ $allItems }}</a>
                                    </li>
                                    <li><a href="{{ route('admin.posts.create', ['type' => $cpt->key]) }}"
                                            class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">{{ $addNew }}</a>
                                    </li>
                                    @foreach($sidebarTaxonomies as $tax)
                                        @if(is_array($tax->post_types) && in_array($cpt->key, $tax->post_types))
                                            <li>
                                                <a href="{{ route('admin.tags.index', ['taxonomy' => $tax->key, 'type' => $cpt->key]) }}"
                                                    class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                                    {{ $tax->plural_label }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endforeach

                    <li class="group relative">
                        <a href="{{ route('admin.media.index') }}"
                            class="flex items-center px-2 py-2 rounded-lg transition-colors duration-200 {{ request()->routeIs('admin.media.*') ? 'bg-indigo-500 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}"
                            title="Media Library">
                            <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ request()->routeIs('admin.media.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span class="sidebar-label whitespace-nowrap">Media Library</span>
                        </a>
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Media Library</div>
                        </div>
                    </li>

                    <li class="group relative"
                        x-data="{ open: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.profile.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.profile.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300' }}"
                            title="Users">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.profile.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                    </path>
                                </svg>
                                <span class="sidebar-label whitespace-nowrap">Users</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Inline Menu (Expanded) -->
                        <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                            style="display: {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.profile.*') ? 'block' : 'none' }}">
                            <li>
                                <a href="{{ route('admin.users.index') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.users.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    All Users
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.users.create') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.users.create') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Add New
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.profile.edit') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.profile.edit') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Profile
                                </a>
                            </li>
                        </ul>

                        <!-- Flyout Menu (Collapsed) -->
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Users</div>
                            <ul class="py-2">
                                <li>
                                    <a href="{{ route('admin.users.index') }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                        All Users
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.users.create') }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                        Add New
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.profile.edit') }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                        Profile
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li>
                        <div
                            class="px-4 py-2 mt-4 text-xs font-semibold text-slate-500 uppercase tracking-wider sidebar-label">
                            Appearance
                        </div>
                        <div class="px-4 py-2 mt-4 text-slate-500 hidden">
                            <hr class="border-slate-700">
                        </div>
                    </li>

                    <!-- Appearance -->
                    <li class="group relative"
                        x-data="{ open: {{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.menus.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.menus.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300' }}"
                            title="Appearance">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.menus.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01">
                                    </path>
                                </svg>
                                <span class="sidebar-label whitespace-nowrap">Appearance</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Inline Submenu -->
                        <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                            style="display: {{ request()->routeIs('admin.themes.*') || request()->routeIs('admin.menus.*') ? 'block' : 'none' }}">
                            <li>
                                <a href="{{ route('admin.themes.index') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.themes.*') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Themes
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.menus.index') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.menus.*') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Menus
                                </a>
                            </li>
                        </ul>

                        <!-- Flyout Menu -->
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Appearance</div>
                            <div class="py-2">
                                <a href="{{ route('admin.themes.index') }}"
                                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                    Themes
                                </a>
                                <a href="{{ route('admin.menus.index') }}"
                                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                    Menus
                                </a>
                            </div>
                        </div>
                    </li>

                    <!-- Plugins -->
                    <li class="group relative"
                        x-data="{ open: {{ request()->routeIs('admin.plugins.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.plugins.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300' }}"
                            title="Plugins">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ request()->routeIs('admin.plugins.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z">
                                    </path>
                                </svg>
                                <span class="sidebar-label whitespace-nowrap">Plugins</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Inline Submenu -->
                        <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                            style="display: {{ request()->routeIs('admin.plugins.*') ? 'block' : 'none' }}">
                            <li>
                                <a href="{{ route('admin.plugins.index') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.plugins.index') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Installed Plugins
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.plugins.create') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.plugins.create') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Add New
                                </a>
                            </li>
                        </ul>

                        <!-- Flyout Menu -->
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Plugins</div>
                            <div class="py-2">
                                <a href="{{ route('admin.plugins.index') }}"
                                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                    Installed Plugins
                                </a>
                                <a href="{{ route('admin.plugins.create') }}"
                                    class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                    Add New
                                </a>
                            </div>
                        </div>
                    </li>

                    <li>
                        <div
                            class="px-4 py-2 mt-4 text-xs font-semibold text-slate-500 uppercase tracking-wider sidebar-label">
                            Settings
                        </div>
                        <!-- Separator line for collapsed mode -->
                        <div class="px-4 py-2 mt-4 text-slate-500 hidden" id="collapsedSettingsLine">
                            <hr class="border-slate-700">
                        </div>
                    </li>

                    <li class="group relative"
                        x-data="{ open: {{ request()->routeIs('admin.settings.*') ? 'true' : 'false' }} }">
                        <button @click="open = !open"
                            class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ request()->routeIs('admin.settings.*') ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-300' }}"
                            title="Settings">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 min-w-[1.25rem] mr-3 {{ request()->routeIs('admin.settings.*') ? 'text-white' : 'text-slate-500 group-hover:text-white' }}"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span class="sidebar-label whitespace-nowrap">Settings</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7">
                                </path>
                            </svg>
                        </button>

                        <!-- Inline Menu (Expanded) -->
                        <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                            style="display: {{ request()->routeIs('admin.settings.*') ? 'block' : 'none' }}">
                            <li>
                                <a href="{{ route('admin.settings.general') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.settings.general') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    General
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings.reading') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.settings.reading') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Reading
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.settings.permalink') }}"
                                    class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->routeIs('admin.settings.permalink') ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                    Permalinks
                                </a>
                            </li>
                        </ul>

                        <!-- Flyout Menu (Collapsed) -->
                        <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                            <div class="flyout-header">Settings</div>
                            <ul class="py-2">
                                <li>
                                    <a href="{{ route('admin.settings.general') }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                        General
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.settings.reading') }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                        Reading
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('admin.settings.permalink') }}"
                                        class="block px-4 py-2 text-sm text-slate-300 hover:text-white hover:bg-slate-700">
                                        Permalinks
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Dynamic Plugin Menus -->
                    <!-- DEBUG MENUS: {{ json_encode(\App\Services\AdminMenuService::get()) }} -->
                    @foreach(\App\Services\AdminMenuService::get() as $key => $item)
                        @if(!empty($item['children']))
                            {{-- Menu with Children (like Users menu) --}}
                            <li class="group relative"
                                x-data="{ open: {{ (request()->routeIs($item['active_pattern']) && (!isset($item['active_queries']) || empty(array_diff_assoc($item['active_queries'], request()->query())))) ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="w-full flex items-center justify-between px-2 py-2 rounded-lg transition-colors duration-200 group-hover:bg-slate-800 hover:text-white {{ (request()->routeIs($item['active_pattern']) && (!isset($item['active_queries']) || empty(array_diff_assoc($item['active_queries'], request()->query())))) ? 'bg-indigo-500 text-white shadow-md' : 'text-slate-300' }}"
                                    title="{{ $item['label'] }}">
                                    <div class="flex items-center">
                                        <div
                                            class="w-5 h-5 min-w-[1.25rem] mr-3 {{ (request()->routeIs($item['active_pattern']) && (!isset($item['active_queries']) || empty(array_diff_assoc($item['active_queries'], request()->query())))) ? 'text-white' : 'text-slate-500 group-hover:text-white' }}">
                                            {!! $item['icon'] !!}
                                        </div>
                                        <span class="sidebar-label whitespace-nowrap">{{ $item['label'] }}</span>
                                    </div>
                                    <svg class="w-4 h-4 transition-transform duration-200 dropdown-chevron"
                                        :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7">
                                        </path>
                                    </svg>
                                </button>

                                <!-- Inline Submenu -->
                                <ul x-show="open" x-cloak class="mt-2 space-y-1 submenu-inline rounded-lg"
                                    style="display: {{ request()->routeIs($item['active_pattern']) ? 'block' : 'none' }}">
                                    @foreach($item['children'] as $child)
                                        <li>
                                            <a href="{{ $child['route'] }}"
                                                class="submenu-item block px-4 py-2 rounded-lg text-sm transition-colors duration-200 {{ request()->fullUrl() == $child['route'] ? 'text-white font-medium' : 'text-slate-400 hover:text-white' }}">
                                                {{ $child['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>

                                <!-- Flyout Menu -->
                                <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                                    <div class="flyout-header">{{ $item['label'] }}</div>
                                    <div class="py-2">
                                        @foreach($item['children'] as $child)
                                            <a href="{{ $child['route'] }}"
                                                class="block px-4 py-2 text-sm text-slate-300 hover:bg-slate-700 hover:text-white transition-colors">
                                                {{ $child['label'] }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </li>
                        @else
                            {{-- Menu without Children --}}
                            <li class="group relative">
                                <a href="{{ $item['route'] }}"
                                    class="flex items-center px-2 py-2 rounded-lg transition-colors duration-200 {{ (request()->routeIs($item['active_pattern']) && (!isset($item['active_queries']) || empty(array_diff_assoc($item['active_queries'], request()->query())))) ? 'bg-indigo-500 text-white shadow-md' : 'hover:bg-slate-800 hover:text-white' }}"
                                    title="{{ $item['label'] }}">
                                    <div
                                        class="w-5 h-5 min-w-[1.25rem] mr-3 {{ (request()->routeIs($item['active_pattern']) && (!isset($item['active_queries']) || empty(array_diff_assoc($item['active_queries'], request()->query())))) ? 'text-white' : 'text-slate-500 group-hover:text-white' }}">
                                        {!! $item['icon'] !!}
                                    </div>
                                    <span class="sidebar-label whitespace-nowrap">{{ $item['label'] }}</span>
                                </a>
                                <div class="flyout-menu hidden bg-slate-800 rounded-r-lg shadow-xl overflow-hidden">
                                    <div class="flyout-header">{{ $item['label'] }}</div>
                                </div>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>

            <!-- <div class="p-2 border-t border-slate-800">
                <div class="flex items-center mb-4 px-2 overflow-hidden">
                    <div
                        class="w-8 h-8 rounded-full bg-indigo-500 overflow-hidden flex-shrink-0 flex items-center justify-center text-white font-bold mr-3">
                        @if(Auth::user()->avatar_url)
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}"
                                class="w-full h-full object-cover">
                        @else
                            {{ Auth::user()->initials }}
                        @endif
                    </div>
                    <div class="sidebar-label whitespace-nowrap overflow-hidden">
                        <p class="text-sm font-medium text-white">{{ Auth::user()->name ?? 'Admin' }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst(Auth::user()->role) }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm font-medium"
                        title="Logout">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                            </path>
                        </svg>
                        <span class="sidebar-label">Logout</span>
                    </button>
                </form>
            </div> -->
        </aside>

        <!-- Main Content -->
        <main id="adminMain" class="flex-1 ml-64 transition-all duration-300 @yield('main_padding', 'p-8')">
            @hasSection('header')
                @if(trim($__env->yieldContent('header')))
                    <header class="flex justify-between items-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800">@yield('header')</h2>
                        <div>
                            @yield('header_actions')
                        </div>
                    </header>
                @endif
            @endif

            {{-- Toast Notifications Container --}}
            <div class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none">

                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                        class="bg-white border-l-4 border-green-500 shadow-lg rounded p-4 flex items-center pointer-events-auto transition-all duration-300 transform"
                        x-transition:enter="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                        x-transition:leave="translate-y-2 opacity-0" role="alert">
                        <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-800">Success</p>
                            <p class="text-sm text-gray-600">{{ session('success') }}</p>
                        </div>
                        <button @click="show = false" class="ml-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                        class="bg-white border-l-4 border-red-500 shadow-lg rounded p-4 flex items-center pointer-events-auto transition-all duration-300 transform"
                        x-transition:enter="translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                        role="alert">
                        <svg class="w-6 h-6 text-red-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-800">Error</p>
                            <p class="text-sm text-gray-600">{{ session('error') }}</p>
                        </div>
                        <button @click="show = false" class="ml-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12">
                                </path>
                            </svg>
                        </button>
                    </div>
                @endif

            </div>

            @yield('content')
        </main>

        <script>
            const sidebar = document.getElementById('adminSidebar');
            const main = document.getElementById('adminMain');
            const toggleBtn = document.getElementById('sidebarToggle');
            const logoFull = document.querySelector('.logo-full');
            const logoShort = document.querySelector('.logo-short');
            const collapsedSettingsLine = document.getElementById('collapsedSettingsLine');

            // Initial State Check
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                applyCollapsedState(true);
            }

            toggleBtn.addEventListener('click', () => {
                const currentlyCollapsed = sidebar.classList.contains('w-20');
                // Toggle
                applyCollapsedState(!currentlyCollapsed);
                // Save
                localStorage.setItem('sidebarCollapsed', !currentlyCollapsed);
            });

            function applyCollapsedState(collapsed) {
                if (collapsed) {
                    // Collapse
                    sidebar.classList.remove('w-64');
                    sidebar.classList.add('w-20', 'sidebar-collapsed');
                    main.classList.remove('ml-64');
                    main.classList.add('ml-20');

                    logoFull.classList.add('hidden');
                    logoShort.classList.remove('hidden');
                    if (collapsedSettingsLine) {
                        collapsedSettingsLine.classList.remove('hidden');
                        collapsedSettingsLine.previousElementSibling.classList.add('hidden'); // Hide the label "SETTINGS"
                    }

                } else {
                    // Expand
                    sidebar.classList.remove('w-20', 'sidebar-collapsed');
                    sidebar.classList.add('w-64');
                    main.classList.remove('ml-20');
                    main.classList.add('ml-64');

                    logoFull.classList.remove('hidden');
                    logoShort.classList.add('hidden');
                    if (collapsedSettingsLine) {
                        collapsedSettingsLine.classList.add('hidden');
                        collapsedSettingsLine.previousElementSibling.classList.remove('hidden');
                    }
                }
            }

            // Flyout Positioning & Hover Logic
            const menuItems = document.querySelectorAll('#adminSidebar li.group');

            menuItems.forEach(item => {
                const flyout = item.querySelector('.flyout-menu');
                if (!flyout) return;

                let hideTimeout;

                const showMenu = () => {
                    if (!sidebar.classList.contains('sidebar-collapsed')) return;
                    clearTimeout(hideTimeout);

                    // Position it
                    const rect = item.getBoundingClientRect();
                    flyout.style.top = `${rect.top}px`;
                    flyout.classList.add('active');
                };

                const hideMenu = () => {
                    // Delay hiding to allow mouse to move from icon to flyout
                    hideTimeout = setTimeout(() => {
                        flyout.classList.remove('active');
                    }, 200);
                };

                // Icon Hover
                item.addEventListener('mouseenter', showMenu);
                item.addEventListener('mouseleave', hideMenu);

                // Flyout Hover (Keep it open)
                flyout.addEventListener('mouseenter', showMenu);
                flyout.addEventListener('mouseleave', hideMenu);
            });
        </script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('editorHandler', (config) => ({
                    mode: 'visual',
                    content: '',
                    editor: null,
                    editorId: config.editorId,
                    fieldName: config.fieldName,

                    switchMode(newMode) {
                        if (newMode === this.mode) return;

                        if (newMode === 'code') {
                            if (this.editor) {
                                this.content = this.editor.getContent();
                            }
                        } else {
                            // Switching to Visual
                            if (this.editor) {
                                this.editor.setContent(this.content);
                            }
                        }
                        this.mode = newMode;
                    },

                    init() {
                        this.$nextTick(() => {
                            const textarea = document.getElementById(this.editorId);
                            if (textarea) {
                                this.content = textarea.value;
                            }
                            this.initTinyMCE();
                        });
                    },

                    updateVisualFromRaw() {
                        const rawContent = this.$refs.rawArea.value;
                        if (this.editor) {
                            this.editor.setContent(rawContent);
                        }
                    },

                    initTinyMCE() {
                        const self = this;
                        const tryInit = () => {
                            if (typeof tinymce === 'undefined') {
                                setTimeout(tryInit, 50);
                                return;
                            }
                            if (tinymce.get(self.editorId)) {
                                tinymce.get(self.editorId).remove();
                            }
                            tinymce.init({
                                selector: '#' + self.editorId,
                                height: '100%', // Fill the container
                                min_height: 400,
                                resize: false, // Let container handle it
                                menubar: false,
                                statusbar: false,
                                images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                                    const xhr = new XMLHttpRequest();
                                    xhr.withCredentials = false;
                                    xhr.open('POST', '{{ route("admin.media.upload") }}');

                                    // CSRF Token
                                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                    if (token) {
                                        xhr.setRequestHeader('X-CSRF-TOKEN', token);
                                    }

                                    xhr.upload.onprogress = (e) => {
                                        progress(e.loaded / e.total * 100);
                                    };

                                    xhr.onload = () => {
                                        if (xhr.status === 403) {
                                            reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                                            return;
                                        }

                                        if (xhr.status < 200 || xhr.status >= 300) {
                                            reject('HTTP Error: ' + xhr.status);
                                            return;
                                        }

                                        const json = JSON.parse(xhr.responseText);

                                        if (!json || typeof json.location != 'string') {
                                            reject('Invalid JSON: ' + xhr.responseText);
                                            return;
                                        }

                                        // Add class to the image in the editor
                                        // We find the image by its blob URI (which it currently has)
                                        if (self.editor) {
                                            const img = self.editor.dom.select('img[src="' + blobInfo.blobUri() + '"]')[0];
                                            if (img) {
                                                self.editor.dom.addClass(img, 'bx-image-' + json.id);
                                            }
                                        }

                                        resolve(json.location);
                                    };

                                    xhr.onerror = () => {
                                        reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status);
                                    };

                                    const formData = new FormData();
                                    formData.append('file', blobInfo.blob(), blobInfo.filename());

                                    xhr.send(formData);
                                }),
                                relative_urls: false,
                                remove_script_host: false,
                                convert_urls: true,
                                image_title: true,
                                promotion: false,
                                plugins: 'preview importcss searchreplace autolink autosave save directionality code visualblocks visualchars fullscreen image link media template codesample table charmap pagebreak nonbreaking anchor insertdatetime advlist lists wordcount help charmap emoticons',
                                toolbar: 'blocks | bold italic underline strikethrough | alignleft aligncenter alignright |  numlist bullist | insertfile image media link | removeformat',
                                contextmenu: 'link image table',
                                content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:16px; line-height:1.6; color: #374151; max-width: 800px; margin: 0 auto; padding: 20px; } img { max-width: 100%; height: auto; } .bx-note-info { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 1rem; margin-bottom: 1rem; color: #1e40af; } .bx-note-warning { background: #fefce8; border-left: 4px solid #eab308; padding: 1rem; margin-bottom: 1rem; color: #854d0e; } .bx-note-error { background: #fef2f2; border-left: 4px solid #ef4444; padding: 1rem; margin-bottom: 1rem; color: #991b1b; }',
                                setup: function (editor) {
                                    self.editor = editor;

                                    // Slash Commands Autocompleter
                                    editor.ui.registry.addAutocompleter('slashcommands', {
                                        ch: '/',
                                        minChars: 0,
                                        columns: 1,
                                        fetch: function (pattern) {
                                            const query = pattern ? pattern.toLowerCase() : '';
                                            const matchedItems = [
                                                { type: 'autocompleteitem', value: 'h1', text: 'H1 Heading 1', icon: 'header' },
                                                { type: 'autocompleteitem', value: 'h2', text: 'H2 Heading 2', icon: 'header' },
                                                { type: 'autocompleteitem', value: 'h3', text: 'H3 Heading 3', icon: 'header' },
                                                { type: 'autocompleteitem', value: 'image', text: 'Image', icon: 'image' },
                                                { type: 'autocompleteitem', value: 'blockquote', text: 'Quote', icon: 'quote' },
                                                { type: 'autocompleteitem', value: 'bullist', text: 'Bulleted List', icon: 'unordered-list' },
                                                { type: 'autocompleteitem', value: 'numlist', text: 'Numbered List', icon: 'ordered-list' },
                                                { type: 'autocompleteitem', value: 'code', text: 'Code Block', icon: 'code-sample' },
                                                { type: 'autocompleteitem', value: 'note_info', text: 'Info Note', icon: 'info' },
                                                { type: 'autocompleteitem', value: 'note_warning', text: 'Warning Note', icon: 'warning' },
                                                { type: 'autocompleteitem', value: 'note_error', text: 'Error Note', icon: 'remove' },
                                                { type: 'autocompleteitem', value: 'hr', text: 'Divider', icon: 'horizontal-rule' }
                                            ].filter(function (item) {
                                                return item.text.toLowerCase().indexOf(query) !== -1;
                                            }).slice(0, 3);

                                            return new Promise(function (resolve) {
                                                resolve(matchedItems);
                                            });
                                        },
                                        onAction: function (autocompleteApi, rng, value) {
                                            editor.selection.setRng(rng);
                                            editor.insertContent('');

                                            switch (value) {
                                                case 'h1': editor.execCommand('FormatBlock', false, 'h1'); break;
                                                case 'h2': editor.execCommand('FormatBlock', false, 'h2'); break;
                                                case 'h3': editor.execCommand('FormatBlock', false, 'h3'); break;
                                                case 'image': editor.execCommand('mceImage'); break;
                                                case 'blockquote': editor.execCommand('FormatBlock', false, 'blockquote'); break;
                                                case 'bullist': editor.execCommand('InsertUnorderedList'); break;
                                                case 'numlist': editor.execCommand('InsertOrderedList'); break;
                                                case 'code': editor.execCommand('codesample'); break;
                                                case 'hr': editor.execCommand('InsertHorizontalRule'); break;
                                                case 'note_info':
                                                    editor.insertContent('<div class="bx-note-info"><p><strong>Info:</strong> Write your note here...</p></div><p>&nbsp;</p>');
                                                    break;
                                                case 'note_warning':
                                                    editor.insertContent('<div class="bx-note-warning"><p><strong>Warning:</strong> Watch out for this...</p></div><p>&nbsp;</p>');
                                                    break;
                                                case 'note_error':
                                                    editor.insertContent('<div class="bx-note-error"><p><strong>Error:</strong> Something went wrong...</p></div><p>&nbsp;</p>');
                                                    break;
                                            }
                                        }
                                    });

                                    editor.on('Change KeyUp', function () {
                                        self.content = editor.getContent();
                                        editor.save();
                                    });
                                    // Sync back from raw when switching modes or blurring
                                    editor.on('focus', function () {
                                        if (self.mode === 'visual' && self.$refs.rawArea) {
                                            // self.editor.setContent(self.$refs.rawArea.value);
                                        }
                                    });
                                    editor.on('change', function () {
                                        editor.save();
                                    });

                                    // Listen for Image Update Dialog
                                    editor.on('ExecCommand', function (e) {
                                        if (e.command === 'mceUpdateImage') {
                                            const img = editor.selection.getNode();
                                            if (img.nodeName === 'IMG') {
                                                // Check for bx-image-{id} class
                                                const matches = img.className.match(/bx-image-(\d+)/);
                                                if (matches && matches[1]) {
                                                    const mediaId = matches[1];
                                                    const altText = img.getAttribute('alt');
                                                    const title = img.getAttribute('title'); // TinyMCE might not set title by default but good to have

                                                    // Send Update Request
                                                    const xhr = new XMLHttpRequest();
                                                    xhr.open('POST', '{{ url(get_admin_prefix() . "/media") }}/' + mediaId);
                                                    xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');

                                                    // CSRF Token
                                                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                                                    if (token) {
                                                        xhr.setRequestHeader('X-CSRF-TOKEN', token);
                                                    }

                                                    // Spoof PUT for Laravel
                                                    xhr.send(JSON.stringify({
                                                        _method: 'PUT',
                                                        alt_text: altText,
                                                        title: title
                                                    }));
                                                }
                                            }
                                        }
                                    });
                                }
                            });
                        };
                        tryInit();
                    },

                    switchMode(newMode) {
                        if (this.mode === newMode) return;
                        if (newMode === 'code') {
                            if (this.editor) this.content = this.editor.getContent();
                        } else {
                            if (this.editor) this.editor.setContent(this.content);
                        }
                        this.mode = newMode;
                    },

                    updateVisualFromRaw() { }
                }));
            });
        </script>
</body>

</html>