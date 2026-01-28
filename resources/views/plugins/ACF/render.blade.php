@foreach($groups as $group)
@php
    $rulesArray = [];
    if ($group->locationRules->isNotEmpty()) {
        foreach ($group->locationRules as $rule) {
            $rulesArray[$rule->group_index][] = [
                'param' => $rule->param,
                'operator' => $rule->operator,
                'value' => $rule->value
            ];
        }
        $rulesArray = array_values($rulesArray);
    }
@endphp
<div id="acf-group-{{ $group->id }}" class="acf-group bg-white rounded-lg shadow-sm mb-6" data-rules='@json($rulesArray, JSON_HEX_APOS)' style="display: none;">
    <div class="px-4 py-3 border-b border-gray-100">
        <h3 class="font-semibold text-gray-700">{{ $group->title }}</h3>
    </div>
    <div class="p-4 space-y-4">
        @foreach($group->fields as $field)
        @php
            $value = $post ? get_field($field->name, $post->id) : $field->default_value;
            $fieldName = "acf[{$field->name}]";
        @endphp
        
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ $field->label }}
                @if($field->required) <span class="text-red-500">*</span> @endif
            </label>
            
            @if($field->instructions)
                <p class="text-xs text-gray-500 mb-2">{{ $field->instructions }}</p>
            @endif

            @switch($field->type)
                @case('textarea')
                    <textarea name="{{ $fieldName }}" rows="3" placeholder="{{ $field->placeholder }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ $value ?? $field->default_value }}</textarea>
                    @break
                
                @case('image')
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-2 text-center hover:bg-gray-50 transition cursor-pointer"
                         onclick="openMediaPicker('{{ $field->name }}_input', '{{ $field->name }}_preview')">
                         
                        <input type="hidden" name="{{ $fieldName }}" id="{{ $field->name }}_input" value="{{ $value ?? $field->default_value }}">
                        
                        @if($value ?? $field->default_value)
                            @php $img = \App\Models\Media::find($value ?? $field->default_value); @endphp
                            <img src="{{ $img ? asset($img->path) : '' }}" id="{{ $field->name }}_preview" class="max-h-32 mx-auto rounded">
                        @else
                            <img src="" id="{{ $field->name }}_preview" class="max-h-32 mx-auto rounded hidden">
                            <div class="text-gray-400 text-xs py-4" id="{{ $field->name }}_placeholder">Select Image</div>
                        @endif
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
                    <select name="{{ $fieldName }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                             $savedValues = explode(',', $field->default_value); // rudimentary default support
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
                            <label class="inline-flex items-center">
                                <input type="radio" name="{{ $fieldName }}" value="{{ $key }}" {{ ($value ?? $field->default_value) == $key ? 'checked' : '' }} class="border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @break

                @default
                    <input type="text" name="{{ $fieldName }}" value="{{ $value ?? $field->default_value }}" placeholder="{{ $field->placeholder }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @endswitch
        </div>
        @endforeach
    </div>
</div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const groups = document.querySelectorAll('.acf-group');
        const context = @json($context ?? []);
        
        // Input Selectors
        const typeInput = document.querySelector('input[name="type"]') || document.querySelector('select[name="type"]');
        const templateInput = document.querySelector('select[name="template"]');
        const statusInput = document.querySelector('select[name="status"]');

        function getContext() {
            // Start with server context (user role, page id, etc)
            let current = { ...context };

            // Update with live values
            if (typeInput) current.post_type = typeInput.value;
            if (templateInput) current.post_template = templateInput.value;
            if (statusInput) current.post_status = statusInput.value;

            // Normalize 'default' template if empty
            if (!current.post_template) current.post_template = 'default';

            return current;
        }

        function evaluateRules() {
            const currentContext = getContext();
            
            groups.forEach(group => {
                const rules = JSON.parse(group.dataset.rules || '[]');
                
                // If no rules, assume hidden or show? 
                // Usually custom fields are specific. If no rules, let's hide to be safe, 
                // OR show if it's a "Global" group. 
                // Logic: If rules array is empty, it's global? No, usually explicitly set.
                // Updated logic: if empty rules, don't show.
                if (!rules || rules.length === 0) {
                    group.style.display = 'none';
                    return;
                }

                let finalMatch = false;

                // OR Logic (Rule Groups)
                for (const groupRules of rules) {
                    let groupMatch = true;

                    // AND Logic (Rules inside Group)
                    for (const rule of groupRules) {
                        const param = rule.param;
                        const operator = rule.operator;
                        const value = rule.value;
                        const contextVal = currentContext[param]; // e.g. 'page'

                        let match = false;
                        
                        // Loose comparison to handle string/number differences
                        if (operator === '==') {
                            match = (String(contextVal).toLowerCase() == String(value).toLowerCase());
                        } else {
                            match = (String(contextVal).toLowerCase() != String(value).toLowerCase());
                        }

                        if (!match) {
                            groupMatch = false;
                            break;
                        }
                    }

                    if (groupMatch) {
                        finalMatch = true;
                        break;
                    }
                }

                group.style.display = finalMatch ? 'block' : 'none';
            });
        }

        // Init
        evaluateRules();

        // Listeners
        if (templateInput) templateInput.addEventListener('change', evaluateRules);
        if (statusInput) statusInput.addEventListener('change', evaluateRules);
        // Type usually doesn't change dynamically on same page (requires reload), but if it does:
        if (typeInput && typeInput.tagName === 'SELECT') typeInput.addEventListener('change', evaluateRules);
        
        // Expose for debugging
        window.ACF = { evaluateRules, getContext };
    });
</script>
