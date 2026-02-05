@foreach($media as $item)
    <div class="relative group media-item" data-id="{{ $item->id }}">
        <!-- Checkbox -->
        <div
            class="absolute top-2 left-2 z-10 opacity-0 group-hover:opacity-100 transition-opacity bg-white rounded shadow">
            <input type="checkbox" value="{{ $item->id }}"
                class="media-checkbox w-5 h-5 rounded text-indigo-600 focus:ring-indigo-500 m-1 cursor-pointer"
                onchange="updateBulkUI()">
        </div>

        <!-- Media Item -->
        <div class="cursor-pointer" onclick="openMediaModal({{ json_encode($item) }})">
            <div
                class="aspect-square bg-gray-100 rounded-lg overflow-hidden border hover:border-indigo-500 transition-colors relative flex items-center justify-center">

                @if(Str::startsWith($item->mime_type, 'image/'))
                    <img src="{{ asset(ltrim($item->path, '/')) }}" alt="{{ $item->alt_text }}"
                        class="w-full h-full object-cover">
                @elseif(Str::startsWith($item->mime_type, 'video/'))
                    <div class="text-center p-4">
                        <span class="text-4xl">🎥</span>
                        <div class="text-xs text-gray-500 mt-2 truncate max-w-[100px]">{{ $item->filename }}</div>
                    </div>
                @elseif(Str::startsWith($item->mime_type, 'audio/'))
                    <div class="text-center p-4">
                        <span class="text-4xl">🎵</span>
                        <div class="text-xs text-gray-500 mt-2 truncate max-w-[100px]">{{ $item->filename }}</div>
                    </div>
                @elseif($item->mime_type === 'application/pdf')
                    <div class="text-center p-4">
                        <span class="text-4xl">📄</span> <!-- PDF Icon -->
                        <div class="text-xs text-gray-500 mt-2 truncate max-w-[100px]">{{ $item->filename }}</div>
                    </div>
                @else
                    <div class="text-center p-4">
                        <span class="text-4xl">📁</span> <!-- Generic File -->
                        <div class="text-xs text-gray-500 mt-2 truncate max-w-[100px]">{{ $item->filename }}</div>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endforeach