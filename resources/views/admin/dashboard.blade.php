@extends('admin.components.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    <!-- Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Posts Stat -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Posts</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $posts_count }}</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-full">
                    <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pages Stat -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Pages</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $pages_count }}</p>
                </div>
                <div class="p-3 bg-green-50 rounded-full">
                    <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 0 01.707.293l5.414 5.414a1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Users Stat -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Users</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $users_count }}</p>
                </div>
                <div class="p-3 bg-indigo-50 rounded-full">
                    <svg class="w-8 h-8 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Media Stat -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Media</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $media_count }}</p>
                </div>
                <div class="p-3 bg-purple-50 rounded-full">
                    <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Widgets -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
            class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-lg shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-md transition-shadow">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <svg class="w-24 h-24 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                    </path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2 relative z-10">Welcome Back!</h3>
            <p class="text-gray-600 relative z-10">You are logged in to your custom CMS. Use the sidebar to navigate to your
                content.</p>
            <div class="mt-4 relative z-10">
                <a href="{{ route('admin.posts.create') }}"
                    class="text-sm font-medium text-blue-600 hover:text-blue-800">Write a new post &rarr;</a>
            </div>
        </div>

        {{-- Hook for Plugins to add Widgets --}}
        <?php do_action('admin_dashboard_widgets'); ?>
    </div>
@endsection