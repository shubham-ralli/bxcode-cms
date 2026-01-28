@extends('layouts.admin')

@section('title', ucfirst(request('type', 'Posts')))
@section('header', 'All ' . ucfirst(request('type', 'Posts')))

@section('content')
    <!-- Top Actions & Filters -->
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <!-- Status Tabs -->
        <div class="flex flex-wrap gap-2 text-sm text-gray-600">
            <a href="{{ route('admin.posts.index', ['type' => $type, 'status' => 'all']) }}" 
               class="{{ ($status == 'all' || !$status) ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
               All <span class="text-gray-400">({{ $counts['all'] }})</span>
            </a> |
            <a href="{{ route('admin.posts.index', ['type' => $type, 'status' => 'publish']) }}" 
               class="{{ $status == 'publish' ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
               Published <span class="text-gray-400">({{ $counts['publish'] }})</span>
            </a> |
            <a href="{{ route('admin.posts.index', ['type' => $type, 'status' => 'draft']) }}" 
               class="{{ $status == 'draft' ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
               Draft <span class="text-gray-400">({{ $counts['draft'] }})</span>
            </a> |
            <a href="{{ route('admin.posts.index', ['type' => $type, 'status' => 'private']) }}" 
               class="{{ $status == 'private' ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
               Private <span class="text-gray-400">({{ $counts['private'] }})</span>
            </a> |
            <a href="{{ route('admin.posts.index', ['type' => $type, 'status' => 'trash']) }}" 
               class="{{ $status == 'trash' ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
               Trash <span class="text-gray-400">({{ $counts['trash'] }})</span>
            </a>
        </div>

        <!-- Search & Create -->
        <div class="flex items-center gap-2">
            <form action="{{ route('admin.posts.index') }}" method="GET" class="flex items-center">
                <input type="hidden" name="type" value="{{ $type }}">
                @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
                
                <input type="text" name="s" value="{{ $search }}" placeholder="Search posts..." 
                    class="shadow-sm border border-gray-300 rounded-l-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="bg-gray-100 border border-l-0 border-gray-300 py-2 px-4 rounded-r-lg hover:bg-gray-200 text-sm font-medium">Search</button>
            </form>
            
            <a href="{{ route('admin.posts.create', ['type' => $type]) }}"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors shadow-sm text-sm whitespace-nowrap">
                Add New
            </a>
        </div>
    </div>

    <form id="bulkActionForm" action="{{ route('admin.posts.bulk') }}" method="POST">
        @csrf
        <!-- Bulk Actions Toolbar -->
        <div class="mb-4 flex items-center gap-2">
            <select name="action" class="shadow-sm border border-gray-300 rounded-lg py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white" required>
                <option value="" disabled selected>Bulk Actions</option>
                @if($status === 'trash')
                    <option value="restore">Restore</option>
                    <option value="delete">Delete Permanently</option>
                @else
                    <option value="trash">Move to Trash</option>
                @endif
            </select>
            <button type="submit" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg shadow-sm text-sm transition-colors" onclick="return confirmBulkAction()">
                Apply
            </button>
        </div>

        <!-- Posts Table -->
        <x-admin-table :pagination="$posts">
            <x-slot name="header">
                 <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                    <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" onclick="toggleAll(this)">
                </th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Title</th>
                @if($type == 'page')
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Path</th>
                @endif
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Author</th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                <th class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
            </x-slot>

            @forelse($posts as $post)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-5 py-4">
                        <input type="checkbox" name="ids[]" value="{{ $post->id }}" class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $post->title }}
                            @if($post->status === 'draft') <span class="text-xs text-orange-600 font-bold ml-1">— Draft</span> @endif
                            @if($post->status === 'private') <span class="text-xs text-gray-500 font-bold ml-1">— Private</span> @endif
                        </div>
                    </td>
                
                    @if($type == 'page')
                    <td class="px-5 py-4 text-xs text-gray-500 font-mono">
                        /{{ $post->slug }}
                    </td>
                    @endif

                    <td class="px-5 py-4 text-sm text-gray-500">
                        {{ \App\Models\User::find($post->author_id)->name ?? 'Unknown' }}
                    </td>
                    
                    <td class="px-5 py-4 text-sm text-gray-500">
                        {{ $post->created_at->format('M j, Y') }}<br>
                        <span class="text-xs">{{ $post->status == 'publish' ? 'Published' : 'Last Modified' }}</span>
                    </td>

                    <td class="px-5 py-4 text-sm whitespace-nowrap text-right">
                        @if($post->status !== 'trash')
                            <a href="{{ route('admin.posts.edit', ['post' => $post->id, 'action' => 'edit']) }}" class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Edit</a>
                            
                            @if($post->status == 'publish' || $post->status == 'private')
                            <a href="{{ $post->url }}" target="_blank" class="text-green-600 hover:text-green-900 font-medium mr-3">View</a>
                            @endif
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $type == 'page' ? 6 : 5 }}" class="px-5 py-8 text-center text-gray-500">
                        No posts found.
                    </td>
                </tr>
            @endforelse
        </x-admin-table>
        <!-- End Posts Table Component -->
    </form>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('post-checkbox');
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
            if (action === 'delete') {
                return confirm('Are you sure you want to PERMANENTLY delete selected items? This cannot be undone.');
            }
            if (action === 'trash') {
                return confirm('Move selected items to Trash?');
            }
            return true;
        }
    </script>
@endsection