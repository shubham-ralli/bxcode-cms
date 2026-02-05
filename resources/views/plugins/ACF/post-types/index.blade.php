@extends('admin.components.admin')

@section('title', 'Post Types')
@section('header', 'Post Types')

@section('header_actions')
    <a href="{{ route('admin.acf.post-types.create') }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm text-sm font-medium">
        Add New
    </a>
@endsection

@section('content')

    <x-admin::admin-table :pagination="$postTypes" :counts="$counts" :status="$status" :search="$search"
        route="admin.acf.post-types.index" bulk-route="admin.acf.post-types.bulk" bulk-action-name="action">
        <x-slot:header>
            <th
                class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                <input type="checkbox" id="selectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onclick="toggleAll(this)">
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Key /
                Features
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Active
            </th>
            <th class="px-6 py-3 text-right bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
            </th>
        </x-slot:header>

        @forelse($postTypes as $type)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" name="ids[]" value="{{ $type->id }}"
                        class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.acf.post-types.edit', $type->id) }}" class="hover:text-indigo-600">
                            {{ $type->plural_label }}
                        </a>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ $type->singular_label }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="flex flex-col gap-1">
                        <div class="flex flex-wrap gap-1 items-center">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                Key: <span class="font-semibold ml-1">{{ $type->key }}</span>
                            </span>
                            @if($type->supports && count($type->supports) > 0)
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-800 border border-blue-100">
                                    {{ count($type->supports) }} features
                                </span>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <form action="{{ route('admin.acf.post-types.toggle', $type->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer {{ $type->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                            {{ $type->active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <form action="{{ route('admin.acf.post-types.toggle', $type->id) }}" method="POST"
                        class="inline-block mr-3">
                        @csrf
                        <button type="submit"
                            class="{{ $type->active ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }}">
                            {{ $type->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.acf.post-types.edit', $type->id) }}"
                        class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                    {{-- Delete via Form (Single) --}}
                    <form action="{{ route('admin.acf.post-types.destroy', $type->id) }}" method="POST" class="inline-block"
                        onsubmit="return confirm('Are you sure?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                    No post types found. <a href="{{ route('admin.acf.post-types.create') }}"
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