@extends('admin.components.admin')

@section('title', 'Taxonomies')
@section('header', 'Taxonomies')

@section('header_actions')
    <a href="{{ route('admin.acf.taxonomies.create') }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm text-sm font-medium">
        Add New
    </a>
@endsection

@section('content')

    <x-admin::admin-table :pagination="$taxonomies" :counts="$counts" :status="$status" :search="$search"
        route="admin.acf.taxonomies.index" bulk-route="admin.acf.taxonomies.bulk" bulk-action-name="action">
        <x-slot:header>
            <th
                class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                <input type="checkbox" id="selectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onclick="toggleAll(this)">
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Key / Type
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Post Types
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Active
            </th>
            <th class="px-6 py-3 text-right bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
            </th>
        </x-slot:header>

        @forelse($taxonomies as $taxonomy)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" name="ids[]" value="{{ $taxonomy->id }}"
                        class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.acf.taxonomies.edit', $taxonomy->id) }}" class="hover:text-indigo-600">
                            {{ $taxonomy->plural_label }}
                        </a>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ $taxonomy->singular_label }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex flex-col gap-1">
                        <span class="text-xs font-medium">Key: <span class="font-semibold">{{ $taxonomy->key }}</span></span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $taxonomy->hierarchical ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $taxonomy->hierarchical ? 'Category (Hierarchical)' : 'Tag (Flat)' }}
                        </span>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @if($taxonomy->post_types && count($taxonomy->post_types) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($taxonomy->post_types as $pt)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $pt }}
                                </span>
                            @endforeach
                        </div>
                    @else
                        <span class="text-xs text-gray-400">None</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <form action="{{ route('admin.acf.taxonomies.toggle', $taxonomy->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer {{ $taxonomy->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                            {{ $taxonomy->active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <form action="{{ route('admin.acf.taxonomies.toggle', $taxonomy->id) }}" method="POST"
                        class="inline-block mr-3">
                        @csrf
                        <button type="submit"
                            class="{{ $taxonomy->active ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }}">
                            {{ $taxonomy->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.acf.taxonomies.edit', $taxonomy->id) }}"
                        class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>

                    <form action="{{ route('admin.acf.taxonomies.destroy', $taxonomy->id) }}" method="POST" class="inline-block"
                        onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                    No taxonomies found. <a href="{{ route('admin.acf.taxonomies.create') }}"
                        class="text-indigo-600 hover:underline">Create one</a>.
                </td>
            </tr>
        @endforelse
    </x-admin::admin-table>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('post-checkbox');
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
                return confirm('Are you sure you want to PERMANENTLY delete selected items? This cannot be undone.');
            }
            return true;
        }
    </script>
@endsection