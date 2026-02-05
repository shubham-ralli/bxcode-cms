@extends('admin.components.admin')

@section('title', ucfirst(request('type', 'Posts')))
@section('header', 'All ' . ucfirst(request('type', 'Posts')))

@section('header_actions')
    <a href="{{ route('admin.posts.create', ['type' => $type]) }}"
        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
        Add New
    </a>
@endsection

@section('content')
    <x-admin::admin-table :pagination="$posts" :counts="$counts" :status="$status" :search="$search"
        route="admin.posts.index" bulk-route="admin.posts.bulk" bulk-action-name="action">


            <x-slot name="header">
                <th
                    class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                    <input type="checkbox" id="selectAll"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        onclick="toggleAll(this)">
                </th>
                <th
                    class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Title</th>
                @if($type == 'page')
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Path</th>
                @endif
                <th
                    class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Author</th>
                <th
                    class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Date</th>
                <th
                    class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Actions</th>
            </x-slot>

            @forelse($posts as $post)
                <tr class="hover:bg-gray-50 transition-colors group">
                    <td class="px-5 py-4">
                        <input type="checkbox" name="ids[]" value="{{ $post->id }}"
                            class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $post->title }}
                            @if($post->status === 'draft') <span class="text-xs text-orange-600 font-bold ml-1">—
                            Draft</span> @endif
                            @if($post->status === 'private') <span class="text-xs text-gray-500 font-bold ml-1">—
                            Private</span> @endif
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
                            <a href="{{ route('admin.posts.edit', ['post' => $post->id, 'action' => 'edit']) }}"
                                class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Edit</a>

                            @if($post->status == 'publish' || $post->status == 'private')
                                <a href="{{ $post->url }}" target="_blank"
                                    class="text-green-600 hover:text-green-900 font-medium mr-3">View</a>
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
        </x-admin::admin-table>
        <!-- End Posts Table Component -->

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
                if (action === 'trash') {
                    return confirm('Move selected items to Trash?');
                }
                return true;
            }
        </script>
@endsection