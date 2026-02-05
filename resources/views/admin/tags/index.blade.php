@extends('admin.components.admin')

@php
    $taxLabel = 'Tags';
    $taxSingular = 'Tag';

    if (($taxonomy ?? 'post_tag') === 'category') {
        $taxLabel = 'Categories';
        $taxSingular = 'Category';
    } elseif (($taxonomy ?? 'post_tag') === 'post_tag') {
        $taxLabel = 'Tags';
        $taxSingular = 'Tag';
    } else {
        // Try to find Custom Taxonomy Label
        if (class_exists('\Plugins\ACF\src\Models\CustomTaxonomy')) {
            $customTax = \Plugins\ACF\src\Models\CustomTaxonomy::where('key', $taxonomy)->first();
            if ($customTax) {
                $taxLabel = $customTax->plural_label;
                $taxSingular = $customTax->singular_label;
            } else {
                $taxLabel = ucfirst($taxonomy);
                $taxSingular = ucfirst($taxonomy);
            }
        } else {
            $taxLabel = ucfirst($taxonomy);
            $taxSingular = ucfirst($taxonomy);
        }
    }
@endphp

@section('title', $taxLabel)
@section('header', $taxLabel)

@section('content')
    <div class="flex flex-col md:flex-row gap-8" x-data="{ editing: null }">

        <!-- Left: Create Tag -->
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New
                    {{ $taxSingular }}
                </h3>
                <form action="{{ route('admin.tags.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="taxonomy" value="{{ $taxonomy ?? 'post_tag' }}">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input type="text" name="name" required
                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-2">
                        <p class="text-xs text-gray-500 mt-1">The name is how it appears on your site.</p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug (optional)</label>
                        <input type="text" name="slug" class="w-full border border-gray-200 rounded-md bg-gray-50 p-2">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="3"
                            class="w-full border border-gray-200 rounded-md bg-gray-50 p-2 "></textarea>
                        <p class="text-xs text-gray-500 mt-1">The description is not prominent by default; however, some
                            themes may show it.</p>
                    </div>

                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded shadow-sm text-sm transition-colors">
                        Add New {{ $taxSingular }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Right: List Tags -->
        <div class="w-full md:w-2/3">

            <x-admin::admin-table :pagination="$tags" :counts="$counts" :search="$search" route="admin.tags.index"
                bulk-route="admin.tags.bulk">

                <x-slot name="header">
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-10">
                        <input type="checkbox" id="selectAll"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            onclick="toggleAll(this)">
                        <input type="hidden" name="taxonomy" value="{{ $taxonomy ?? 'post_tag' }}">
                    </th>
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Name
                    </th>
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Description
                    </th>
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Slug
                    </th>
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Count
                    </th>
                    <th
                        class="px-5 py-3 border-b border-gray-200 bg-gray-50 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </x-slot>

                @forelse($tags as $tag)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-5 py-4">
                            <input type="checkbox" name="ids[]" value="{{ $tag->id }}"
                                class="post-checkbox rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        </td>
                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $tag->name }}</span>
                            <div class="flex items-center gap-2 mt-1 invisible group-hover:visible">
                                <a href="{{ route('admin.tags.edit_custom', ['taxonomy' => $taxonomy ?? 'post_tag', 'tag_ID' => $tag->id, 'post_type' => request('type', 'post')]) }}"
                                    class="text-xs text-indigo-600 hover:text-indigo-900">Edit</a>
                                <span class="text-gray-300">|</span>
                                <button type="button"
                                    onclick="deleteItem('{{ route('admin.tags.destroy', ['tag' => $tag->id, 'type' => request('type')]) }}')"
                                    class="text-xs text-red-600 hover:text-red-900 bg-transparent border-0 cursor-pointer p-0 inline-block font-medium">Delete</button>
                                <span class="text-gray-300">|</span>
                                @php
                                    // Determine Base Slug
                                    $taxonomyKey = $taxonomy ?? 'post_tag';

                                    if ($taxonomyKey === 'category') {
                                        $base = \App\Models\Setting::get('category_base') ?: 'category';
                                    } elseif ($taxonomyKey === 'post_tag') {
                                        $base = \App\Models\Setting::get('tag_base') ?: 'tag';
                                    } else {
                                        // Custom Taxonomy
                                        $base = $taxonomyKey;
                                        if (isset($customTax) && $customTax) {
                                            $settings = json_decode($customTax->settings ?? '{}', true);
                                            $base = $settings['slug'] ?? $customTax->key;
                                        }
                                    }

                                    // Ensure clean base
                                    $base = trim($base, '/');
                                    // Fallback if base becomes empty after trim (unlikely with defaults, but safe)
                                    if (empty($base) && $taxonomyKey === 'category')
                                        $base = 'category';
                                    if (empty($base) && $taxonomyKey === 'post_tag')
                                        $base = 'tag';
                                    // Determine visibility
                                    $isPublic = true;
                                    if (isset($customTax) && $customTax) {
                                        $isPublic = $customTax->publicly_queryable;
                                    }
                                @endphp
                                @if($isPublic)
                                    <a href="{{ url($base . '/' . $tag->slug) }}" target="_blank"
                                        class="text-xs text-green-600 hover:text-green-900">View</a>
                                @endif
                            </div>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500 max-w-xs truncate">{{ $tag->description ?? '—' }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tag->slug }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-sm text-gray-500">{{ $tag->posts_count }}</td>
                        <td class="px-5 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button type="button"
                                onclick="deleteItem('{{ route('admin.tags.destroy', ['tag' => $tag->id, 'type' => request('type')]) }}')"
                                class="text-red-600 hover:text-red-900 bg-transparent border-0 cursor-pointer p-1">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-gray-500">No items found.</td>
                    </tr>
                @endforelse

            </x-admin::admin-table>
        </div>
    </div>

    <form id="deleteItemForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <script>
        function toggleAll(source) {
            checkboxes = document.getElementsByClassName('post-checkbox');
            for (var i = 0, n = checkboxes.length; i < n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function deleteItem(url) {
            if (confirm('Delete this item?')) {
                var form = document.getElementById('deleteItemForm');
                form.action = url;
                form.submit();
            }
        }
    </script>
@endsection