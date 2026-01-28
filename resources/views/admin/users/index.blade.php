@extends('layouts.admin')

@section('title', 'Users')
@section('header', 'All Users')

@section('content')
    <!-- Top Actions & Filters -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Role Tabs -->
        <div class="flex flex-wrap gap-2 text-sm text-gray-600">
            @php
                $currentRole = request('role', 'all');
                $roles = [
                    'all' => 'All',
                    'administrator' => 'Administrator',
                    'editor' => 'Editor',
                    'author' => 'Author',
                    'contributor' => 'Contributor',
                    'subscriber' => 'Subscriber'
                ];
            @endphp

            @foreach($roles as $key => $label)
                @if(($counts[$key] ?? 0) > 0 || $key === 'all')
                    <a href="{{ route('admin.users.index', ['role' => $key]) }}"
                        class="{{ $currentRole === $key ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
                        {{ $label }} <span class="text-gray-400">({{ $counts[$key] ?? 0 }})</span>
                    </a>
                    @if(!$loop->last) <span class="text-gray-300">|</span> @endif
                @endif
            @endforeach
        </div>

        <!-- Search & Add New -->
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.users.index') }}" method="GET" class="flex items-center">
                @if(request('role')) <input type="hidden" name="role" value="{{ request('role') }}"> @endif

                <input type="text" name="s" value="{{ request('s') }}" placeholder="Search users..."
                    class="shadow-sm border border-gray-300 rounded-l-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit"
                    class="bg-gray-100 border border-l-0 border-gray-300 py-2 px-4 rounded-r-lg hover:bg-gray-200 text-sm font-medium">Search</button>
            </form>

            <a href="{{ route('admin.users.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap">
                Add New User
            </a>
        </div>
    </div>

    <form id="bulkActionForm" method="POST">
        @csrf
        <!-- Bulk Actions Toolbar (Skeleton) -->
        <div class="mb-4 flex items-center gap-2">
            <select name="action"
                class="shadow-sm border border-gray-300 rounded-lg py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
                required>
                <option value="" disabled selected>Bulk Actions</option>
                <option value="delete">Delete</option>
            </select>
            <button type="button" onclick="alert('Bulk actions not fully implemented yet')"
                class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg shadow-sm text-sm transition-colors">
                Apply
            </button>
        </div>

        <!-- Users Table -->
        <x-admin-table :pagination="$users">
            <x-slot name="header">
                 <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onclick="toggleAll(this)">
                </th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
            </x-slot>

            @forelse($users as $user)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-5 py-4">
                        <input type="checkbox" name="ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 overflow-hidden rounded-full">
                                @if($user->avatar_url)
                                    <img class="h-10 w-10 object-cover" src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                                @else
                                    <span class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-500 font-bold border border-indigo-200">
                                        {{ $user->initials }}
                                    </span>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900 flex items-center gap-2">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">You</span>
                                    @endif
                                </div>
                                <!-- Row Actions -->
                                <div class="text-xs text-gray-500 mt-1 sm:invisible group-hover:visible flex gap-2">
                                    <a href="{{ $user->id === auth()->id() ? route('admin.profile.edit') : route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                    
                                    @if($user->id !== auth()->id())
                                    <span class="text-gray-300">|</span>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this user permanently?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-500">
                        <a href="mailto:{{ $user->email }}" class="hover:text-indigo-600">{{ $user->email }}</a>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-500 capitalize">
                        {{ $user->role }}
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-500">
                        {{ $user->created_at->format('M j, Y') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                        No users found.
                    </td>
                </tr>
            @endforelse
        </x-admin-table>
    </form>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('user-checkbox');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
@endsection