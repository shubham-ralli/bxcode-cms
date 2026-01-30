@extends('admin.components.admin')

@section('title', 'Users')
@section('header', 'All Users')

@section('header_actions')
    <a href="{{ route('admin.users.create') }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
        Add New User
    </a>
@endsection

@section('content')
    <x-admin::admin-table :pagination="$users" :counts="$counts" :status="$status" 
        search="{{ request('s') }}" 
        route="admin.users.index" 
        bulk-route="admin.users.bulk" 
        bulk-action-name="action"
        filter-param="role"
    >
        <x-slot name="header">
            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onclick="toggleAll(this)">
            </th>
            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Role</th>
            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Joined</th>
            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
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
                                    {{ substr($user->name, 0, 2) }}
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
                <td class="px-5 py-4 text-sm whitespace-nowrap text-right">
                    <a href="{{ $user->id === auth()->id() ? route('admin.profile.edit') : route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Edit</a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-5 py-8 text-center text-gray-500">
                    No users found.
                </td>
            </tr>
        @endforelse
    </x-admin::admin-table>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('user-checkbox');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
@endsection