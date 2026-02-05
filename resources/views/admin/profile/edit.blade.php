@extends('admin.components.admin')

@section('title', 'My Profile')

@section('content')
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">My Profile</h1>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column (Main Content) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Card 1: Personal Information -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Personal Information</h3>

                            <div class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Display
                                        Name</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First
                                            Name</label>
                                        <input type="text" name="first_name" id="first_name"
                                            value="{{ old('first_name', $user->first_name) }}"
                                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    </div>
                                    <div>
                                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last
                                            Name</label>
                                        <input type="text" name="last_name" id="last_name"
                                            value="{{ old('last_name', $user->last_name) }}"
                                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email
                                        Address</label>
                                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}"
                                        required
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                                    <select name="role" id="role"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors"
                                        {{ auth()->user()->id == $user->id && auth()->user()->role != 'administrator' ? 'disabled' : '' }}>
                                        <option value="administrator" {{ old('role', $user->role) == 'administrator' ? 'selected' : '' }}>Administrator</option>
                                        <option value="editor" {{ old('role', $user->role) == 'editor' ? 'selected' : '' }}>
                                            Editor</option>
                                        <option value="author" {{ old('role', $user->role) == 'author' ? 'selected' : '' }}>
                                            Author</option>
                                        <option value="contributor" {{ old('role', $user->role) == 'contributor' ? 'selected' : '' }}>Contributor</option>
                                        <option value="subscriber" {{ old('role', $user->role) == 'subscriber' ? 'selected' : '' }}>Subscriber</option>
                                    </select>
                                    @if(auth()->user()->id == $user->id && auth()->user()->role != 'administrator')
                                        <p class="text-xs text-gray-400 mt-1">You cannot change your own role.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Bio -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Biographical Info</h3>
                            <div class="h-[300px]">
                                @include('admin.partials.editor', ['name' => 'bio', 'value' => old('bio', $user->bio), 'height' => '100%'])
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Security -->
                    <div class="bg-white rounded-lg shadow">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Security</h3>
                            <div class="space-y-6">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                                        New Password <span class="text-gray-400 font-normal">(Leave blank to keep
                                            current)</span>
                                    </label>
                                    <input type="password" name="password" id="password"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                                        Confirm New Password
                                    </label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="w-full border border-gray-200 rounded-md bg-gray-50 p-3 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Sidebar) -->
                <div class="space-y-6">
                    <!-- Update Action -->
                    <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-semibold text-gray-900">Update Profile</h3>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($user->role) }}
                            </span>
                        </div>
                        <div class="border-t pt-4">
                            <button type="submit"
                                class="w-full py-2 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-medium shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Save Changes
                            </button>
                        </div>
                    </div>

                    <!-- Profile Image -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 border-b pb-2">Profile Image</h3>

                        <input type="hidden" name="profile_image_id" id="profile_image_id"
                            value="{{ old('profile_image_id', $user->profile_image_id) }}">

                        <div class="flex flex-col items-center">
                            @php
                                $profileUrl = $user->profileImage ? asset(ltrim($user->profileImage->path, '/')) : '';
                            @endphp

                            <div
                                class="w-32 h-32 rounded-full bg-gray-100 border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden mb-4 relative group">
                                <img id="profilePreview" src="{{ $profileUrl }}" alt="Profile Preview"
                                    class="w-full h-full object-cover {{ $profileUrl ? '' : 'hidden' }}">
                                <span id="profilePlaceholder" class="text-gray-400"
                                    style="{{ $profileUrl ? 'display:none' : '' }}">
                                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </span>
                            </div>

                            <button type="button" onclick="openMediaPicker('profile_image_id', 'profilePreview')"
                                class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded text-sm shadow-sm transition-colors mb-2">
                                Set Profile Image
                            </button>
                            <button type="button" onclick="removeProfileImage()" id="removeImageBtn"
                                class="text-red-600 text-xs hover:text-red-800 {{ $profileUrl ? '' : 'hidden' }}">
                                Remove Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @include('admin.partials.media-picker-modal')

    <script>
        window.mediaPickerCallback = function (inputId, previewId) {
            const preview = document.getElementById(previewId);
            const placeholder = document.getElementById('profilePlaceholder');
            const removeBtn = document.getElementById('removeImageBtn');

            if (preview && preview.src) {
                preview.classList.remove('hidden');
                if (placeholder) placeholder.style.display = 'none';
                if (removeBtn) removeBtn.classList.remove('hidden');
            }
        };

        function removeProfileImage() {
            document.getElementById('profile_image_id').value = '';
            const preview = document.getElementById('profilePreview');
            const placeholder = document.getElementById('profilePlaceholder');
            const removeBtn = document.getElementById('removeImageBtn');

            preview.src = '';
            preview.classList.add('hidden');
            if (placeholder) placeholder.style.display = 'block';
            if (removeBtn) removeBtn.classList.add('hidden');
        }
    </script>
@endsection