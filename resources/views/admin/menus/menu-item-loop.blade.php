@foreach($items as $item)
    <li class="menu-item bg-white border border-gray-200 rounded-lg mb-3 shadow-sm group hover:border-gray-300 transition-all"
        data-id="{{ $item->id }}">

        <!-- Header / Handle -->
        <div
            class="flex items-center justify-between px-4 py-3 bg-white cursor-move hover:bg-gray-50 transition-colors rounded-t-lg border-b border-gray-100 handle select-none">
            <span class="text-sm font-semibold text-gray-700">{{ $item->title }}</span>
            <div class="flex items-center gap-3">
                <span
                    class="text-[10px] font-bold uppercase tracking-wider text-gray-400 bg-gray-100 px-2 py-0.5 rounded">{{ $item->type }}</span>
                <button type="button" @click="toggleItem($el)"
                    class="text-gray-400 hover:text-indigo-600 focus:outline-none transition-transform duration-200 p-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Settings Panel -->
        <div class="p-4 bg-gray-50 rounded-b-lg settings-panel hidden border-t border-gray-100">
            <form action="{{ route('admin.menus.updateItem', $item->id) }}" method="POST" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Navigation
                            Label</label>
                        <input type="text" name="title" value="{{ $item->title }}"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">URL</label>
                        <input type="text" name="url" value="{{ $item->url }}"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">CSS Class
                            (Optional)</label>
                        <input type="text" name="css_class" value="{{ $item->css_class }}"
                            placeholder="e.g. mb-2 custom-btn"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors placeholder-gray-300">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Target</label>
                        <select name="target"
                            class="w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
                            <option value="_self" {{ $item->target == '_self' ? 'selected' : '' }}>Same Tab</option>
                            <option value="_blank" {{ $item->target == '_blank' ? 'selected' : '' }}>New Tab</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-3 mt-2 border-t border-gray-200">
                    <button type="button"
                        onclick="if(confirm('Are you sure you want to remove this item?')) { document.getElementById('delete-item-{{ $item->id }}').submit(); }"
                        class="text-xs text-red-600 hover:text-red-800 font-medium hover:underline transition-colors">
                        Remove Item
                    </button>
                    <button type="submit"
                        class="text-xs bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded font-medium shadow-sm transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
            <form id="delete-item-{{ $item->id }}" action="{{ route('admin.menus.deleteItem', $item->id) }}" method="POST"
                class="hidden">
                @csrf @method('DELETE')
            </form>
        </div>

        <!-- Recursive Children -->
        @if(!empty($item->children))
            <ul class="pl-8 border-l border-gray-200 ml-4 mt-2">
                @include('admin.menus.menu-item-loop', ['items' => $item->children])
            </ul>
        @endif
    </li>
@endforeach