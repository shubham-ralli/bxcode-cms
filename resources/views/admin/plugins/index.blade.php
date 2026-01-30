@extends('admin.components.admin')

@section('title', 'Plugins')
@section('header', 'Plugins')

@section('header_actions')
    <a href="{{ route('admin.plugins.create') }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm text-sm font-medium">
        Add Plugin
    </a>
@endsection

@section('content')

    <x-admin::admin-table :pagination="$plugins" :counts="$counts" :status="$status" :search="$search"
        route="admin.plugins.index" bulk-route="admin.plugins.bulk" bulk-action-name="action">
        
        <x-slot:header>
            <th class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                <input type="checkbox" id="selectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onclick="toggleAll(this)">
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Plugin</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th class="px-6 py-3 text-right bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </x-slot>

        @forelse($plugins as $plugin)
            <tr class="hover:bg-gray-50 transition-colors group">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" name="ids[]" value="{{ $plugin['slug'] }}"
                        class="plugin-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        {{ $plugin['name'] }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        v{{ $plugin['version'] }} @if($plugin['author']) by {{ $plugin['author'] }} @endif
                    </div>
                </td>
                <td class="px-6 py-4">
                    <div class="text-sm text-gray-600">{{ $plugin['description'] }}</div>
                    <div class="text-xs text-gray-400 font-mono mt-1">{{ $plugin['path'] }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($plugin['is_active'])
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Inactive
                        </span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    @if($plugin['is_active'])
                        <button type="button" 
                            onclick="submitSingleAction('{{ route('admin.plugins.deactivate', $plugin['slug']) }}')" 
                            class="text-red-600 hover:text-red-900 transition-colors cursor-pointer">
                            Deactivate
                        </button>
                    @else
                        <button type="button" 
                            onclick="submitSingleAction('{{ route('admin.plugins.activate', $plugin['slug']) }}')" 
                            class="text-indigo-600 hover:text-indigo-900 transition-colors font-semibold cursor-pointer mr-3">
                            Activate
                        </button>
                        <button type="button" 
                            onclick="if(confirm('Delete {{ $plugin['name'] }}?')) { submitSingleAction('{{ route('admin.plugins.destroy', $plugin['slug']) }}', 'DELETE') }" 
                            class="text-red-600 hover:text-red-900 cursor-pointer">
                            Delete
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    No plugins found. @if(request('s')) Try a different search term. @else Add plugins to <code>resources/views/plugins</code>. @endif
                </td>
            </tr>
        @endforelse
    </x-admin::admin-table>

    <!-- Hidden Generic Form for Single Actions -->
    <form id="singleActionForm" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="_method" id="singleActionMethod" value="POST">
    </form>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('plugin-checkbox');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function confirmBulkAction() {
            const action = document.querySelector('select[name="action"]').value;
            if (!action) {
                alert('Please select an action.');
                return false;
            }
            if (action === 'delete') {
                return confirm('Are you sure you want to PERMANENTLY delete selected plugins? This cannot be undone.');
            }
            return true;
        }

        function submitSingleAction(url, method = 'POST') {
            const form = document.getElementById('singleActionForm');
            form.action = url;
            document.getElementById('singleActionMethod').value = method;
            form.submit();
        }
    </script>
@endsection