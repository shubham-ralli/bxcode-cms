@extends('admin.components.admin')

@section('header', 'Edit User')

@section('content')
    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="md:col-span-2 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Display Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                            <input type="text" name="first_name" id="first_name"
                                value="{{ old('first_name', $user->first_name) }}"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" id="last_name"
                                value="{{ old('last_name', $user->last_name) }}"
                                class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-gray-400 font-normal">(Leave blank to keep current)</span>
                        </label>
                        <input type="password" name="password" id="password"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="bio" class="block text-sm font-medium text-gray-700 mb-2">Biographical Info</label>
                        <div class="h-[300px]">
                            @include('admin.partials.editor', ['name' => 'bio', 'value' => old('bio', $user->bio), 'height' => '100%'])
                        </div>
                    </div>
                </div>

                <!-- Sidebar / Role / Image -->
                <div class="space-y-6">
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <select name="role" id="role"
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-3 border">
                            <option value="subscriber" {{ old('role', $user->role) == 'subscriber' ? 'selected' : '' }}>
                                Subscriber</option>
                            <option value="contributor" {{ old('role', $user->role) == 'contributor' ? 'selected' : '' }}>
                                Contributor</option>
                            <option value="author" {{ old('role', $user->role) == 'author' ? 'selected' : '' }}>Author
                            </option>
                            <option value="editor" {{ old('role', $user->role) == 'editor' ? 'selected' : '' }}>Editor
                            </option>
                            <option value="administrator" {{ old('role', $user->role) == 'administrator' ? 'selected' : '' }}>
                                Administrator</option>
                        </select>
                        @error('role') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
                        <label class="block text-sm font-medium text-gray-700 mb-3">Profile Image</label>
                        <input type="hidden" name="profile_image_id" id="profile_image_id"
                            value="{{ old('profile_image_id', $user->profile_image_id) }}">

                        <div class="flex flex-col items-center">
                            @php
                                $profileUrl = $user->profileImage ? asset(ltrim($user->profileImage->path, '/')) : '';
                            @endphp
                            <img id="profilePreview" src="{{ $profileUrl }}" alt="Profile Preview"
                                class="w-32 h-32 rounded-full object-cover mb-3 bg-gray-200 {{ $profileUrl ? '' : 'hidden' }} border shadow-sm">
                            <button type="button" onclick="openMediaPicker('profile_image_id', 'profilePreview')"
                                class="text-indigo-600 hover:text-indigo-800 text-sm font-medium focus:outline-none">
                                Set Profile Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8 border-t pt-6">
                <a href="{{ route('admin.users.index') }}"
                    class="mr-4 px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit"
                    class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Update User
                </button>
            </div>
        </form>
    </div>

    @include('admin.partials.media-picker-modal')
@endsection