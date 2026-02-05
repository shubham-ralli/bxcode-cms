@props(['name' => 'content', 'value' => '', 'height' => '500px', 'id' => null])
@php
    $editorId = $id ?? $name . '_tinymce';
@endphp

<div x-data="editorHandler({ editorId: '{{ $editorId }}', fieldName: '{{ $name }}' })"
    class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 flex flex-col h-full not-prose">

    <!-- Editor Toolbar Header -->
    <div class="bg-gray-50 border-b border-gray-200 px-4 py-2 flex items-center justify-between">
        <div class="flex space-x-1 bg-gray-200 p-0.5 rounded-lg">
            <button type="button" @click="switchMode('visual')"
                :class="{ 'bg-white shadow-sm text-indigo-600 font-medium': mode === 'visual', 'text-gray-600 hover:text-gray-900 hover:bg-gray-100': mode !== 'visual' }"
                class="px-3 py-1.5 text-xs rounded transition-all focus:outline-none">
                Visual
            </button>
            <button type="button" @click="switchMode('code')"
                :class="{ 'bg-white shadow-sm text-indigo-600 font-medium': mode === 'code', 'text-gray-600 hover:text-gray-900 hover:bg-gray-100': mode !== 'code' }"
                class="px-3 py-1.5 text-xs rounded transition-all focus:outline-none flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                Code
            </button>
        </div>
        <div class="text-xs text-gray-400 font-mono" x-show="mode === 'code'">
            HTML Source Mode
        </div>
    </div>

    <!-- Editor Body -->
    <div class="relative flex-1 min-h-[{{ $height }}]">
        <!-- Raw Textarea (Code Mode) -->
        <textarea x-ref="rawArea" x-model="content" x-show="mode === 'code'"
            class="w-full h-full p-4 font-mono text-sm leading-relaxed border-none resize-none focus:ring-0 bg-gray-50 text-gray-800"
            style="min-height: {{ $height }}" placeholder="Write HTML code here..."
            @input="updateVisualFromRaw()"></textarea>

        <!-- Visual Editor Container -->
        <div x-show="mode === 'visual'" class="h-full">
            <textarea id="{{ $editorId }}" name="{{ $name }}" class="hidden">{!! $value !!}</textarea>
        </div>
    </div>
</div>

<!-- Load TinyMCE from CDN -->