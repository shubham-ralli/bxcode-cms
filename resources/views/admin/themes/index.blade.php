@extends('layouts.admin')

@section('title', 'Themes')
@section('header', 'Appearance')

@section('content')
    <div class="space-y-6">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Manage Themes</h2>
                <p class="text-gray-500 text-sm mt-1">Select and activate the visual theme for your website.</p>
            </div>
            <!-- Upload Theme Button (Placeholder action for now) -->
            <button
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-2 px-4 rounded-lg shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
                Upload Theme
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
            @foreach($themes as $theme)
                <div
                    class="group bg-white rounded-xl shadow-sm border {{ $theme['active'] ? 'border-2 border-indigo-500 ring-4 ring-indigo-50' : 'border-gray-200 hover:shadow-md' }} overflow-hidden transition-all duration-300 relative">

                    <!-- Screenshot Placeholder -->
                    <div
                        class="aspect-video w-full bg-gradient-to-br from-gray-100 to-gray-200 relative flex items-center justify-center overflow-hidden">
                        <!-- Try to find a screenshot if it existed, otherwise show Pattern -->
                        <div class="absolute inset-0 opacity-10"
                            style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23000000\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                        </div>

                        <span class="text-3xl font-bold text-gray-300 uppercase tracking-widest select-none">
                            {{ substr($theme['name'], 0, 2) }}
                        </span>

                        @if($theme['active'])
                            <div
                                class="absolute inset-0 bg-indigo-900 bg-opacity-10 backdrop-blur-[1px] flex items-center justify-center">
                                <div
                                    class="bg-white text-indigo-700 px-4 py-1.5 rounded-full shadow-lg font-bold text-xs uppercase tracking-wide flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                                    Active
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="text-lg font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">
                                {{ $theme['name'] }}
                            </h3>
                            <span class="bg-gray-100 text-gray-600 text-[10px] font-medium px-2 py-1 rounded">
                                v{{ $theme['version'] }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-500 line-clamp-2 h-10 mb-4">
                            {{ $theme['description'] ?: 'No description provided.' }}
                        </p>

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <div class="text-xs text-gray-400">
                                By <span class="font-medium text-gray-600">{{ $theme['author'] }}</span>
                            </div>

                            @if(!$theme['active'])
                                <form action="{{ route('admin.themes.activate') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="theme_id" value="{{ $theme['id'] }}">
                                    <button type="submit"
                                        class="text-sm bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium py-1.5 px-4 rounded transition-colors focus:ring-2 focus:ring-offset-1 focus:ring-indigo-500">
                                        Activate
                                    </button>
                                </form>
                            @else
                                <button disabled
                                    class="text-sm text-green-600 font-medium py-1.5 px-4 cursor-default flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                                        </path>
                                    </svg>
                                    Installed
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection