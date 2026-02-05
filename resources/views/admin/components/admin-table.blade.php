@props([
    'columns' => [],
    'pagination' => null,
    'counts' => [],
    'status' => 'all',
    'route' => null,
    'search' => null,
    'bulkRoute' => null,
])

<div>
    {{-- 1. Top Toolbar: Status Tabs --}}
    @if(!empty($counts))
        <div class="mb-4">
            <div class="flex flex-wrap gap-2 text-sm text-gray-600">
                @foreach($counts as $key => $count)
                    @php
                        if ($key !== 'all' && $count === 0) continue;

                        $label = match($key) {
                            'all' => 'All',
                            'publish' => 'Published',
                            'draft' => 'Draft',
                            'trash' => 'Trash',
                            'private' => 'Private',
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                            default => ucfirst($key)
                        };
                        
                        $isActive = $status === $key;
                        $filterParam = $attributes->get('filter-param', 'status');
                        $url = $route ? route($route, array_merge(request()->query(), [$filterParam => $key, 'page' => null])) : '#';
                    @endphp
                    
                    <a href="{{ $url }}" 
                       class="{{ $isActive ? 'text-indigo-600 font-bold' : 'hover:text-indigo-600' }}">
                       {{ $label }} <span class="text-gray-400">({{ $count }})</span>
                    </a>
                    @if(!$loop->last) <span class="text-gray-300">|</span> @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- 2. Bulk Actions Wrapper & Table --}}
    @php
        $hasBulk = !empty($bulkRoute);
    @endphp

    <div>
        {{-- Toolbar Row: Bulk Actions (Left) + Search (Right) --}}
        <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            {{-- Left: Bulk Actions --}}
            <div>
                @if($hasBulk)
                    <div class="flex items-center gap-2">
                        <select name="action" form="bulkActionForm" class="shadow-sm border border-gray-300 rounded-lg py-2 pl-3 pr-10 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white" required>
                            <option value="" disabled selected>Bulk Actions</option>
                            @if($status === 'trash')
                                <option value="restore">Restore</option>
                                <option value="delete">Delete Permanently</option>
                            @else
                                {{-- Smart Bulk Options based on Capabilities (Counts) --}}
                                @if(isset($counts['inactive']))
                                    <option value="activate">Activate</option>
                                    <option value="deactivate">Deactivate</option>
                                @endif
                                
                                @if(isset($counts['trash']))
                                    <option value="trash">Move to Trash</option>
                                @else
                                    {{-- If no trash support, allow direct delete --}}
                                    <option value="delete">Delete Permanently</option>
                                @endif
                                
                                {{-- Status Options --}}
                                @if(isset($counts['publish']) && (isset($counts['draft']) || isset($counts['private'])))
                                    <option value="publish">Edit to Public</option>
                                @endif
                                @if(isset($counts['draft']))
                                    <option value="draft">Edit to Draft</option>
                                @endif
                                @if(isset($counts['private']))
                                    <option value="private">Edit to Private</option>
                                @endif
                            @endif
                        </select>
                        <button type="submit" form="bulkActionForm" class="bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium py-2 px-4 rounded-lg shadow-sm text-sm transition-colors" 
                                onclick="return confirm('Are you sure you want to apply this action to selected items?');">
                            Apply
                        </button>
                    </div>
                @endif
            </div>

            {{-- Right: Search Box --}}
            <div>
                @if($route)
                    <form action="{{ route($route) }}" method="GET" class="flex items-center">
                        @foreach(request()->query() as $key => $value)
                            @if(!in_array($key, ['s', 'page']))
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        
                        <input type="text" name="s" value="{{ $search ?? '' }}" placeholder="Search..." 
                            class="shadow-sm border border-gray-300 rounded-l-lg py-2 px-4 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48 transition-all focus:w-64">
                        <button type="submit" class="bg-gray-100 border border-l-0 border-gray-300 py-2 px-4 rounded-r-lg hover:bg-gray-200 text-sm font-medium">Search</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- The Table Card --}}
        @php
            $TableTag = $hasBulk ? 'form' : 'div';
            // Use quoted attributes for safety
            $TableAttrs = $hasBulk ? 'action="'.route($bulkRoute).'" method="POST" id="bulkActionForm"' : '';
        @endphp

        <{{ $TableTag }} {!! $TableAttrs !!} class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            @if($hasBulk) @csrf @endif
            
            @if($pagination)
                <div class="px-5 py-3 border-b border-gray-200">
                    {{ $pagination->links() }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="min-w-full leading-normal">
                    <thead>
                        <tr>
                            {{ $header ?? '' }}
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        {{ $slot }}
                    </tbody>
                </table>
            </div>

            @if($pagination)
                <div class="px-5 py-3 border-t border-gray-200">
                    {{ $pagination->links() }}
                </div>
            @endif
        </{{ $TableTag }}>
    </div>
</div>
