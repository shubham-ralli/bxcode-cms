@extends('admin.components.admin')

@section('title', 'Dashboard')
@section('header', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Welcome Back!</h3>
            <p class="text-gray-600">You are logged in to your custom CMS.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Quick Stats</h3>
            <p class="text-gray-600">Manage your posts, pages and media from the sidebar.</p>
        </div>
    </div>
@endsection