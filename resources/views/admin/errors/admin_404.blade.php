@extends('admin.components.admin')

@section('title', 'Page Not Found')
@section('header', '404 - Not Found')

@section('content')
    <div class="flex flex-col items-center justify-center py-20">
        <div class="text-9xl font-bold text-gray-200">404</div>
        <div class="text-2xl font-bold text-gray-700 mt-4">Page Not Found</div>
        <p class="text-gray-500 mt-2 text-center max-w-md">
            The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.
        </p>
        <div class="mt-8 flex gap-4">
            <a href="{{ route('admin.dashboard') }}"
                class="px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Dashboard
            </a>
        </div>
    </div>
@endsection