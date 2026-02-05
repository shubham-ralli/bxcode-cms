@extends('admin.components.admin')

@section('title', 'Custom Fields')
@section('header', 'Field Groups')

@section('header_actions')
    <a href="{{ route('admin.acf.create') }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm text-sm font-medium">
        Add New
    </a>
@endsection

@section('content')
    {{-- Top Actions handled by x-admin::admin-table props and header_actions --}}

    <x-admin::admin-table :pagination="$groups" :counts="$counts" :status="$status" :search="$search" route="admin.acf.index"
        bulk-route="admin.acf.bulk">
        <x-slot:header>
            <th
                class="px-6 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                <input type="checkbox" id="selectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    onclick="toggleAll(this)">
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Locations
            </th>
            <th class="px-6 py-3 text-left bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Active
            </th>
            <th class="px-6 py-3 text-right bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
            </th>
        </x-slot:header>

        @forelse($groups as $group)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" name="ids[]" value="{{ $group->id }}"
                        class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                        <a href="{{ route('admin.acf.edit', $group->id) }}" class="hover:text-indigo-600">
                            {{ $group->title }}
                        </a>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    @php
                        // Group rules by group_index for display
                        $groupedRules = $group->locationRules->groupBy('group_index');
                    @endphp

                    @if($groupedRules->isNotEmpty())
                        <div class="flex flex-col gap-1">
                            @foreach($groupedRules as $gIndex => $groupRules)
                                <div class="flex flex-wrap gap-1 items-center">
                                    @if($loop->index > 0) <span class="text-[10px] font-bold text-gray-400 uppercase">OR</span> @endif
                                    @foreach($groupRules as $rule)
                                        @if($loop->index > 0) <span class="text-[10px] font-bold text-gray-400 uppercase">AND</span> @endif
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                            {{ ucwords(str_replace('_', ' ', $rule->param)) }}
                                            <span class="text-gray-400 mx-1">{{ $rule->operator === '==' ? ':' : $rule->operator }}</span>
                                            <span class="font-semibold">{{ ucwords(str_replace(['_', '-'], ' ', $rule->value)) }}</span>
                                        </span>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="text-gray-400">No rules</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <form action="{{ route('admin.acf.toggle', $group->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer {{ $group->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                            {{ $group->active ? 'Active' : 'Inactive' }}
                        </button>
                    </form>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <form action="{{ route('admin.acf.toggle', $group->id) }}" method="POST" class="inline-block mr-3">
                        @csrf
                        <button type="submit"
                            class="{{ $group->active ? 'text-amber-600 hover:text-amber-900' : 'text-green-600 hover:text-green-900' }}">
                            {{ $group->active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </form>
                    <a href="{{ route('admin.acf.edit', $group->id) }}"
                        class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                    {{-- Delete via Form (Single) --}}
                    <form action="{{ route('admin.acf.destroy', $group->id) }}" method="POST" class="inline-block"
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
                    No field groups found. <a href="{{ route('admin.acf.create') }}"
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