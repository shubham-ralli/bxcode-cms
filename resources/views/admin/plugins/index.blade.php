@extends('admin.components.admin')

@section('title', 'Plugins')
@section('header', 'Plugins')

@section('content')
    <!-- Top Actions & Filters -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Status Filter Links -->
        <div class="flex flex-wrap gap-2 text-sm text-gray-600">
            @php
                $activeCount = collect($plugins)->where('is_active', true)->count();
                $inactiveCount = collect($plugins)->where('is_active', false)->count();
                $allCount = count($plugins);
            @endphp
            <a href="{{ route('admin.plugins.index') }}" 
               class="{{ !request('status') ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
               All <span class="text-gray-400">({{ $allCount }})</span>
            </a> |
            <a href="#" class="hover:text-indigo-600 cursor-not-allowed opacity-50" title="Coming soon">
               Active <span class="text-gray-400">({{ $activeCount }})</span>
            </a> |
            <a href="#" class="hover:text-indigo-600 cursor-not-allowed opacity-50" title="Coming soon">
               Inactive <span class="text-gray-400">({{ $inactiveCount }})</span>
            </a>
        </div>

        <!-- Search & Create -->
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.plugins.index') }}" method="GET" class="flex items-center">
                <input type="text" name="s" value="{{ $search ?? '' }}" placeholder="Search plugins..." 
                    class="shadow-sm border border-gray-300 rounded-l-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48 sm:w-64">
                <button type="submit" class="bg-gray-100 border border-l-0 border-gray-300 py-2 px-4 rounded-r-lg hover:bg-gray-200 text-sm font-medium">Search</button>
            </form>
            
            <a href="{{ route('admin.plugins.create') }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap">
                Add New
            </a>
        </div>
    </div>

    <!-- Bulk Form -->
    <form id="bulkActionForm" action="{{ route('admin.plugins.bulk') }}" method="POST">
        @csrf
        <!-- Bulk Actions Toolbar -->
        <div class="mb-4 flex items-center gap-2">
            <select name="action" class="shadow-sm border border-gray-300 rounded-lg py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white" required>
                <option value="" disabled selected>Bulk Actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete Permanently</option>
            </select>
            <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg shadow-sm text-sm transition-colors" onclick="return confirmBulkAction()">
                Apply
            </button>
        </div>

        <!-- Plugins Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onclick="toggleAll(this)">
                            </th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Plugin</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($plugins as $plugin)
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-5 py-4">
                                    <input type="checkbox" name="ids[]" value="{{ $plugin['slug'] }}" class="plugin-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $plugin['name'] }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        v{{ $plugin['version'] }} @if($plugin['author']) by {{ $plugin['author'] }} @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-600">{{ $plugin['description'] }}</div>
                                    <div class="text-xs text-gray-400 font-mono mt-1">{{ $plugin['path'] }}</div>
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap">
                                    @if($plugin['is_active'])
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="mr-1.5 h-2 w-2 text-green-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="mr-1.5 h-2 w-2 text-gray-400" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3" /></svg>
                                            Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    @if($plugin['is_active'])
                                        <button type="button" onclick="submitPluginAction('{{ route('admin.plugins.deactivate', $plugin['slug']) }}')" class="text-red-600 hover:text-red-900 transition-colors">Deactivate</button>
                                    @else
                                        <button type="button" onclick="submitPluginAction('{{ route('admin.plugins.activate', $plugin['slug']) }}')" class="text-indigo-600 hover:text-indigo-900 transition-colors font-semibold">Activate</button>
                                        <span class="text-gray-300 mx-2">|</span>
                                        <button type="button" 
                                            onclick="if(confirm('Delete {{ $plugin['name'] }}?')) { document.getElementById('delete-form-{{ $plugin['slug'] }}').submit(); }"
                                            class="text-red-600 hover:text-red-900 transition-colors">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                    No plugins found. @if(request('s')) Try a different search term. @else Add plugins to <code>resources/views/plugins</code>. @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <!-- Hidden Generic Form for Actions -->
    <form id="pluginActionForm" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Hidden Individual Delete Forms (to handle method=DELETE correctly vs POST bulk) -->
    @foreach($plugins as $plugin)
        @if(!$plugin['is_active'])
            <form id="delete-form-{{ $plugin['slug'] }}" action="{{ route('admin.plugins.destroy', $plugin['slug']) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        @endif
    @endforeach

    <script>
        function submitPluginAction(url) {
            const form = document.getElementById('pluginActionForm');
            form.action = url;
            form.submit();
        }

        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('plugin-checkbox');
            for(var i=0, n=checkboxes.length;i<n;i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function confirmBulkAction() {
            const action = document.querySelector('select[name="action"]').value;
            if (!action) {
                alert('Please select an action.');
                return false;
            }
            // Check if any checked
            const checked = document.querySelectorAll('.plugin-checkbox:checked').length;
            if (checked === 0) {
                 alert('Please select at least one plugin.');
                 return false;
            }

            if (action === 'delete') {
                return confirm('Are you sure you want to PERMANENTLY delete selected plugins? This cannot be undone.');
            }
            return true;
        }
    </script>
@endsection
