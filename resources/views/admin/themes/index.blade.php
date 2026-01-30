@extends('admin.components.admin')

@section('title', 'Themes')
@section('header', 'Themes')

@section('header_actions')
    <button type="button"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm text-sm font-medium cursor-not-allowed opacity-75"
        title="Coming soon">
        Upload Theme
    </button>
@endsection

@section('content')

    <x-admin::admin-table :pagination="$themes" :counts="$counts" :status="$status" :search="$search"
        route="admin.themes.index" bulk-route="" bulk-action-name="">

        <x-slot:header>
            <th
                class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                <input type="checkbox" id="selectAll" disabled
                    class="rounded border-gray-300 text-gray-400 shadow-sm cursor-not-allowed">
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Theme</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">
                Description</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Status
            </th>
            <th class="px-6 py-3 text-right bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
            </th>
            </x-slot>

            @forelse($themes as $theme)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" disabled
                            class="rounded border-gray-300 text-gray-400 shadow-sm cursor-not-allowed">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $theme['name'] }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            v{{ $theme['version'] }} @if($theme['author']) by {{ $theme['author'] }} @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-600 line-clamp-2" title="{{ $theme['description'] }}">
                            {{ $theme['description'] ?: 'No description provided.' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($theme['active'])
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 mr-1.5"></span>
                                Active
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        @if(!$theme['active'])
                            <form action="{{ route('admin.themes.activate') }}" method="POST" class="inline-block">
                                @csrf
                                <input type="hidden" name="theme_id" value="{{ $theme['id'] }}">
                                <button type="submit"
                                    class="text-indigo-600 hover:text-indigo-900 font-semibold cursor-pointer">Activate</button>
                            </form>
                        @else
                            <span class="text-green-600 font-medium cursor-default">Installed</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        No themes found.
                    </td>
                </tr>
            @endforelse
    </x-admin::admin-table>

@endsection