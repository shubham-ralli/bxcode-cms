@php
    $value = $post ? get_field($field->name, $post->id) : $field->default_value;
    $fieldName = "acf[{$field->name}]";
    // Handle nested names if parent is present? 
    // Ideally we would pass a prefix, but for now flat 'acf[name]' works if names are unique. 
    // For proper nesting, we might need a recursive prefix stack.
    // However, existing ACF usages might expect flat. Let's stick to simple first unless collision is high.
    // Actually, distinct names are usually required in ACF.
@endphp

<div class="acf-field mb-4" data-name="{{ $field->name }}" data-type="{{ $field->type }}">
    @if(!in_array($field->type, ['message', 'tab']))
    <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ $field->label }}
        @if($field->required) <span class="text-red-500">*</span> @endif
    </label>
    @endif
    
    @if($field->instructions)
        <p class="text-xs text-gray-500 mb-2">{{ $field->instructions }}</p>
    @endif

    <div class="acf-input">
        @switch($field->type)
            @case('textarea')
                <textarea name="{{ $fieldName }}" rows="3" class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $value ?? $field->default_value }}</textarea>
                @break
            
            @case('image')
                <div class="relative group">
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-2 text-center hover:bg-gray-50 transition cursor-pointer min-h-[150px] flex items-center justify-center relative overflow-hidden"
                            onclick="if(!event.target.closest('button')) openMediaPicker('{{ $field->name }}_input', '{{ $field->name }}_preview')">
                            
                        <input type="hidden" name="{{ $fieldName }}" id="{{ $field->name }}_input" value="{{ $value ?? $field->default_value }}">
                        
                        {{-- Preview Image --}}
                        <img src="{{ ($value ?? $field->default_value) ? asset(\App\Models\Media::find($value ?? $field->default_value)?->path) : '' }}" 
                                id="{{ $field->name }}_preview" 
                                class="max-h-48 rounded {{ ($value ?? $field->default_value) ? '' : 'hidden' }}">
                        
                        {{-- Placeholder --}}
                        <div id="{{ $field->name }}_placeholder" class="{{ ($value ?? $field->default_value) ? 'hidden' : '' }}">
                            <svg class="w-10 h-10 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-xs text-gray-500">Select Image</span>
                        </div>

                        {{-- Actions Overlay --}}
                        <div id="{{ $field->name }}_actions" class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity {{ ($value ?? $field->default_value) ? '' : 'hidden' }}">
                            <button type="button" 
                                onclick="openMediaPicker('{{ $field->name }}_input', '{{ $field->name }}_preview')"
                                class="bg-white text-gray-800 px-3 py-1 rounded text-xs font-medium hover:bg-gray-100">
                                Replace
                            </button>
                            <button type="button" 
                                onclick="removeAcfImage('{{ $field->name }}')"
                                class="bg-red-600 text-white px-3 py-1 rounded text-xs font-medium hover:bg-red-700">
                                Remove
                            </button>
                        </div>
                    </div>
                </div>
                @break

            @case('wysiwyg')
                @php 
                    $editorId = 'acf_editor_' . preg_replace('/[^a-zA-Z0-9_]/', '', $field->name) . '_' . uniqid(); 
                @endphp
                @include('admin.partials.editor', [
                    'name' => $fieldName, 
                    'value' => $value ?? $field->default_value, 
                    'height' => '200px',
                    'id' => $editorId
                ])
                @break

            @case('select')
                @php
                    $choices = [];
                    if (!empty($field->options['choices'])) {
                        $lines = explode("\n", $field->options['choices']);
                        foreach ($lines as $line) {
                            $parts = explode(':', $line);
                            $val = trim($parts[0]);
                            $label = isset($parts[1]) ? trim($parts[1]) : $val;
                            $choices[$val] = $label;
                        }
                    }
                @endphp
                <select name="{{ $fieldName }}" class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select...</option>
                    @foreach($choices as $key => $label)
                        <option value="{{ $key }}" {{ ($value ?? $field->default_value) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @break

            @case('checkbox')
                @php
                    $choices = [];
                    if (!empty($field->options['choices'])) {
                        $lines = explode("\n", $field->options['choices']);
                        foreach ($lines as $line) {
                            $parts = explode(':', $line);
                            $val = trim($parts[0]);
                            $label = isset($parts[1]) ? trim($parts[1]) : $val;
                            $choices[$val] = $label;
                        }
                    }
                    $savedValues = is_array($value) ? $value : json_decode($value, true) ?? [];
                    if (empty($value) && $field->default_value && empty($savedValues)) {
                            $savedValues = explode(',', $field->default_value); 
                    }
                @endphp
                <div class="space-y-2">
                    @foreach($choices as $key => $label)
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="{{ $fieldName }}[]" value="{{ $key }}" {{ in_array($key, (array)$savedValues) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('radio')
                @php
                    $choices = [];
                    if (!empty($field->options['choices'])) {
                        $lines = explode("\n", $field->options['choices']);
                        foreach ($lines as $line) {
                            $parts = explode(':', $line);
                            $val = trim($parts[0]);
                            $label = isset($parts[1]) ? trim($parts[1]) : $val;
                            $choices[$val] = $label;
                        }
                    }
                @endphp
                <div class="space-y-2">
                    @foreach($choices as $key => $label)
                        <label class="inline-flex items-center mr-4">
                            <input type="radio" name="{{ $fieldName }}" value="{{ $key }}" {{ ($value ?? $field->default_value) == $key ? 'checked' : '' }} class="border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @break

            @case('message')
                <div class="prose prose-sm max-w-none text-gray-600">
                    {!! $field->options['message'] ?? '' !!}
                </div>
                @break

            @case('group')
            @case('block')
            @case('row')
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                    @if($field->children->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($field->children as $childField)
                                @include('plugins.ACF.field-render', ['field' => $childField, 'post' => $post ?? null])
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 italic">No fields in this group.</p>
                    @endif
                </div>
                @break

            @case('table')
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50/50">
                    <p class="text-xs text-gray-400 italic">Table layout coming soon.</p>
                </div>
                @break

            @case('true_false')
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" name="{{ $fieldName }}" value="1" {{ ($value ?? $field->default_value) ? 'checked' : '' }} class="border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 rounded">
                    <span class="ml-2 text-sm text-gray-700">{{ $field->options['message'] ?? 'Yes' }}</span>
                </label>
                @break

            @case('button_group')
                @php
                    $choices = [];
                    if (!empty($field->options['choices'])) {
                        $lines = explode("\n", $field->options['choices']);
                        foreach ($lines as $line) {
                            $parts = explode(':', $line);
                            $val = trim($parts[0]);
                            $label = isset($parts[1]) ? trim($parts[1]) : $val;
                            $choices[$val] = $label;
                        }
                    }
                @endphp
                <div class="inline-flex rounded-md shadow-sm" role="group">
                    @foreach($choices as $key => $label)
                        <button type="button" 
                                onclick="this.parentElement.querySelectorAll('button').forEach(b => b.classList.remove('bg-indigo-600', 'text-white')); this.classList.add('bg-indigo-600', 'text-white'); document.querySelector('input[name=&quot;{{ $fieldName }}&quot;]').value = '{{ $key }}';"
                                class="px-4 py-2 text-sm font-medium border border-gray-300 {{ $loop->first ? 'rounded-l-lg' : '' }} {{ $loop->last ? 'rounded-r-lg' : '' }} {{ ($value ?? $field->default_value) == $key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                    <input type="hidden" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value }}">
                </div>
                @break

            @case('date_picker')
                <input type="date" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value }}" 
                       placeholder="{{ $field->placeholder ?? '' }}"
                       class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @break

            @case('date_time_picker')
                <input type="datetime-local" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value }}" 
                       placeholder="{{ $field->placeholder ?? '' }}"
                       class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @break

            @case('time_picker')
                <input type="time" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value }}" 
                       placeholder="{{ $field->placeholder ?? '' }}"
                       class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @break

            @case('color_picker')
                <div class="flex gap-2 items-center">
                    <input type="color" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value ?? '#000000' }}" 
                           class="h-10 w-20 border border-gray-200 rounded-md shadow-sm cursor-pointer">
                    <input type="text" value="{{ $value ?? $field->default_value ?? '#000000' }}" 
                           onchange="this.previousElementSibling.value = this.value"
                           oninput="this.previousElementSibling.value = this.value"
                           placeholder="#000000"
                           class="flex-1 bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                @break

            @case('icon_picker')
                @php
                    $iconClasses = ['fas fa-heart', 'fas fa-star', 'fas fa-user', 'fas fa-home', 'fas fa-envelope', 'fas fa-phone', 'fas fa-check', 'fas fa-times', 'fas fa-search', 'fas fa-cog'];
                @endphp
                <div x-data="{ open: false, selected: '{{ $value ?? $field->default_value ?? 'fas fa-heart' }}' }" class="relative">
                    <button type="button" @click="open = !open" 
                            class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm flex items-center justify-between hover:bg-gray-100">
                        <span class="flex items-center gap-2">
                            <i :class="selected" class="text-lg"></i>
                            <span x-text="selected" class="text-sm text-gray-700"></span>
                        </span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" 
                         class="absolute z-10 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-auto">
                        @foreach($iconClasses as $icon)
                            <div @click="selected = '{{ $icon }}'; open = false" 
                                 class="p-2 hover:bg-indigo-50 cursor-pointer flex items-center gap-2">
                                <i class="{{ $icon }} text-lg"></i>
                                <span class="text-sm text-gray-700">{{ $icon }}</span>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="{{ $fieldName }}" x-model="selected">
                </div>
                @break

            @default
                <input type="text" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value }}" class="w-full bg-gray-50 p-2 border border-gray-200 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        @endswitch
    </div>
</div>
