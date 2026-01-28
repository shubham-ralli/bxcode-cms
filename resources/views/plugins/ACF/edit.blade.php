@extends('layouts.admin')

@section('title', $group->id ? 'Edit Field Group' : 'Add New Field Group')

@section('content')
    <form action="{{ $group->id ? route('admin.acf.update', $group->id) : route('admin.acf.store') }}" method="POST">
        @csrf
        @if($group->id) @method('PUT') @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">{{ $group->id ? 'Edit Field Group' : 'New Field Group' }}</h1>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Save Changes
            </button>
        </div>

        <!-- Title and Config -->
        <div class="">


            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input type="text" name="title" value="{{ old('title', $group->title) }}"
                    class="w-full border-gray-300 rounded-md bg-gray-50 p-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="e.g. Homepage Hero" required>
            </div>


            <!-- Fields Builder (Alpine) -->
            <div class="bg-white rounded-lg shadow overflow-hidden mb-6"
                x-data="fieldBuilder({{ $group->fields->toJson() }})">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-medium text-gray-800">Fields</h2>
                    <button type="button" @click="addField()"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        + Add Field
                    </button>
                </div>

                <div class="divide-y divide-gray-200" x-data="{ expanded: 0, dragging: null, dropping: null }">
                    <template x-for="(field, index) in fields" :key="field.key || field.id || index">
                        <div class="bg-white transition-colors drag-box"
                            :id="field.id ? `field-card-${field.id}` : `field-card-new-${index}`"
                            x-data="{ handleActive: false }"
                            :class="{ 'border-t-2 border-indigo-500': dropping === index && dragging !== index }"
                            :draggable="handleActive" @dragstart="dragging = index; $el.classList.add('opacity-50')"
                            @dragend="dragging = null; $el.classList.remove('opacity-50'); handleActive = false"
                            @dragover.prevent="dropping = index"
                            @drop="reorder(dragging, index); dragging = null; dropping = null">

                            <!-- Header (Click to Toggle) -->
                            <div class="px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 bg-gray-50 border-b border-gray-100"
                                @click="expanded === index ? expanded = null : expanded = index">
                                <div class="flex items-center gap-3">
                                    <!-- Drag Handle -->
                                    <div class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-200"
                                        title="Drag to reorder" @mousedown="handleActive = true"
                                        @mouseup="handleActive = false">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M4 8h16M4 16h16"></path>
                                        </svg>
                                    </div>

                                    <span class="bg-gray-200 text-gray-600 text-xs font-bold px-2 py-1 rounded"
                                        x-text="index + 1"></span>

                                    <div class="flex flex-col"
                                        @click="expanded === index ? expanded = null : expanded = index">
                                        <span class="font-medium text-gray-900 text-sm"
                                            x-text="field.label || '(No Label)'"></span>
                                        <span class="text-xs text-mono text-gray-400" x-text="field.name"></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3"
                                    @click="expanded === index ? expanded = null : expanded = index">
                                    <span
                                        class="text-xs text-gray-500 uppercase tracking-wider bg-white border border-gray-200 px-2 py-0.5 rounded"
                                        x-text="field.type"></span>
                                    <div class="transform transition-transform duration-200"
                                        :class="{'rotate-180': expanded === index}">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Body (Form) -->
                            <div x-show="expanded === index" x-collapse>
                                <div class="p-4 bg-white border-t border-gray-100">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <input type="hidden" :name="`fields[${index}][id]`" :value="field.id">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                Label</label>
                                            <input type="text" x-model="field.label" :name="`fields[${index}][label]`"
                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                                required @input="generateName(field)">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Field Name</label>
                                            <input type="text" x-model="field.name" :name="`fields[${index}][name]`"
                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                                required>
                                        </div>
                                        <div x-show="field.type !== 'image'">
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Default
                                                Value</label>
                                            <input type="text" x-model="field.default_value"
                                                :name="`fields[${index}][default_value]`"
                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3">
                                        </div>
                                        <div x-show="field.type !== 'image'">
                                            <label
                                                class="block text-xs font-semibold text-gray-500 mb-1">Placeholder</label>
                                            <input type="text" x-model="field.placeholder"
                                                :name="`fields[${index}][placeholder]`"
                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Field Type</label>
                                            <select x-model="field.type" :name="`fields[${index}][type]`"
                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3">
                                                <option value="text">Text</option>
                                                <option value="textarea">Text Area</option>
                                                <option value="number">Number</option>
                                                <option value="email">Email</option>
                                                <option value="url">Url</option>
                                                <option value="image">Image</option>
                                                <option value="wysiwyg">WYSIWYG Editor</option>
                                                <option value="select">Select</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="radio">Radio Button</option>
                                            </select>
                                        </div>
                                        <div x-show="['select', 'checkbox', 'radio'].includes(field.type)">
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Choices</label>
                                            <p class="text-xs text-gray-400 mb-2">Enter each choice on a new line.<br>For more control, you may specify both a value and label like this:<br><code>red : Red</code><br><code>blue : Blue</code></p>
                                            <textarea x-model="field.options.choices" :name="`fields[${index}][options][choices]`"
                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                                rows="5" placeholder="red : Red&#10;blue : Blue"></textarea>
                                        </div>
                                        <div class="flex items-end justify-end">
                                            <button type="button" @click="removeField(index)"
                                                class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center gap-1 px-3 py-2 rounded hover:bg-red-50 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                                Delete Field
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="fields.length === 0" class="p-8 text-center text-gray-500 bg-gray-50">
                        <p class="mb-2">No fields added yet.</p>
                        <button type="button" @click="addField()"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            Add First Field
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 bg-white border-t border-gray-200" x-show="fields.length > 0">
                    <button type="button" @click="addField()"
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                        + Add Another Field
                    </button>
                </div>
            </div>

            <!-- Location Rules (Dynamic) - MOVED TO BOTTOM -->
            @php
                // Transform rules relationship to array structure [[rule, rule], [rule]] for frontend
                $rulesArray = [];
                if ($group->locationRules->isNotEmpty()) {
                    foreach ($group->locationRules as $rule) {
                        $rulesArray[$rule->group_index][] = [
                            'param' => $rule->param,
                            'operator' => $rule->operator,
                            'value' => $rule->value
                        ];
                    }
                    $rulesArray = array_values($rulesArray); // Ensure indexed array
                }
            @endphp

            <script>
                window.acfEditorData = {
                    rules: @json($rulesArray),
                    postTypes: @json($postTypes),
                    templates: @json($templates),
                    roles: @json($roles),
                    pages: @json($pages)
                };
            </script>

            <div class="bg-white rounded-lg shadow p-6 mb-6"
                x-data="ruleBuilder(window.acfEditorData.rules, window.acfEditorData.postTypes, window.acfEditorData.templates, window.acfEditorData.roles, window.acfEditorData.pages)">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Location Rules</h3>
                <p class="text-xs text-gray-500 mb-4">Show this field group if...</p>

                <div class="space-y-4">
                    <template x-for="(group, gIndex) in groups" :key="gIndex">
                        <div class="p-4 bg-gray-50 border border-gray-200 rounded relative">
                            <div class="absolute -top-3 left-4 bg-gray-50 px-2 text-xs font-bold text-gray-400 uppercase">
                                <span x-show="gIndex === 0">Rule Group</span>
                                <span x-show="gIndex > 0">OR</span>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(rule, rIndex) in group" :key="rIndex">
                                    <div class="flex items-center gap-2">
                                        <span x-show="rIndex > 0"
                                            class="text-xs font-bold text-gray-400 w-8 text-center">AND</span>
                                        <span x-show="rIndex === 0" class="w-8"></span>

                                        <!-- Param -->
                                        <select x-model="rule.param" @change="rule.value = ''"
                                            class="text-sm border-gray-300 rounded-md w-1/4 p-3">
                                            <option value="post_type">Post Type</option>
                                            <option value="page_template">Page Template</option>
                                            <option value="post_status">Post Status</option>
                                            <option value="page">Page</option>
                                            <option value="current_user_role">User Role</option>
                                        </select>

                                        <!-- Operator -->
                                        <select x-model="rule.operator"
                                            class="text-sm border-gray-300 rounded-md w-auto p-3">
                                            <option value="==">is equal to</option>
                                            <option value="!=">is not equal to</option>
                                        </select>

                                        <!-- Value (Dynamic based on Param) -->
                                        <div class="flex-1">
                                            <!-- Post Type -->
                                            <template x-if="rule.param === 'post_type'">
                                                <select x-model="rule.value"
                                                    class="w-full text-sm border-gray-300 rounded-md p-3">
                                                    <template x-for="(label, value) in options.postTypes" :key="value">
                                                        <option :value="value" x-text="label"
                                                            :selected="value == rule.value"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <!-- Page Template -->
                                            <template x-if="rule.param === 'page_template'">
                                                <select x-model="rule.value"
                                                    class="w-full text-sm border-gray-300 rounded-md p-3">
                                                    <template x-for="(label, value) in options.templates" :key="value">
                                                        <option :value="value" x-text="label"
                                                            :selected="value == rule.value"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <!-- Post Status -->
                                            <template x-if="rule.param === 'post_status'">
                                                <select x-model="rule.value"
                                                    class="w-full text-sm border-gray-300 rounded-md p-3">
                                                    <option value="publish" :selected="'publish' == rule.value">Published
                                                    </option>
                                                    <option value="draft" :selected="'draft' == rule.value">Draft</option>
                                                </select>
                                            </template>

                                            <!-- User Role -->
                                            <template x-if="rule.param === 'current_user_role'">
                                                <select x-model="rule.value"
                                                    class="w-full text-sm border-gray-300 rounded-md p-3">
                                                    <template x-for="(label, value) in options.roles" :key="value">
                                                        <option :value="value" x-text="label"
                                                            :selected="value == rule.value"></option>
                                                    </template>
                                                </select>
                                            </template>

                                            <!-- Page ID -->
                                            <template x-if="rule.param === 'page'">
                                                <select x-model="rule.value"
                                                    class="w-full text-sm border-gray-300 rounded-md p-3">
                                                    <template x-for="page in options.pages" :key="page.id">
                                                        <option :value="String(page.id)" x-text="page.title"
                                                            :selected="String(page.id) == String(rule.value)"></option>
                                                    </template>
                                                </select>
                                            </template>
                                        </div>

                                        <button type="button" @click="removeRule(gIndex, rIndex)"
                                            class="text-gray-400 hover:text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </template>
                            </div>

                            <button type="button" @click="addRule(gIndex)"
                                class="mt-2 ml-10 text-xs text-indigo-600 font-medium hover:underline">
                                + AND
                            </button>

                            <button type="button" @click="removeGroup(gIndex)"
                                class="absolute top-2 right-2 text-gray-300 hover:text-red-500" title="Remove Group">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>

                <div class="mt-4">
                    <button type="button" @click="addGroup()"
                        class="px-3 py-1 bg-white border border-gray-300 text-gray-700 text-sm rounded shadow-sm hover:bg-gray-50">
                        + Add Rule Group
                    </button>
                </div>

                <!-- Hidden Input for Form Submission -->
                <input type="hidden" name="rules" :value="JSON.stringify(groups)">
            </div>

    </form>

    <script>
        function ruleBuilder(initialRules, postTypes, templates, roles, pages) {
            // Migration for legacy rules {post_type: 'page'} -> [[{param: 'post_type', operator: '==', value: 'page'}]]
            let initGroups = [];

            if (!initialRules || (Array.isArray(initialRules) && initialRules.length === 0)) {
                // Default: One group, one rule: Post Type == Page
                initGroups = [[{ param: 'post_type', operator: '==', value: 'page' }]];
            } else if (!Array.isArray(initialRules) && typeof initialRules === 'object') {
                // Check if it's an object-casted array (keys are numbers)
                const values = Object.values(initialRules);
                if (values.length > 0 && Array.isArray(values[0])) {
                    initGroups = values;
                } else {
                    // Legacy Object found, migrate.
                    // Assuming simple key-value pairs meant 'AND', but single group.
                    let group = [];
                    for (const [key, val] of Object.entries(initialRules)) {
                        group.push({ param: key, operator: '==', value: val });
                    }
                    initGroups = [group];
                }
            } else {
                // Already new structure (Array of Arrays) or generic Array
                // Basic validation: Check if first element is array.
                if (initialRules.length > 0 && !Array.isArray(initialRules[0])) {
                    // It's a flat array of rules (single group)
                    initGroups = [initialRules];
                } else {
                    initGroups = initialRules;
                }
            }

            return {
                groups: initGroups,
                options: { postTypes, templates, roles, pages },

                addGroup() {
                    this.groups.push([{ param: 'post_type', operator: '==', value: 'page' }]);
                },
                removeGroup(index) {
                    if (this.groups.length > 1 && confirm('Remove this rule group?')) {
                        this.groups.splice(index, 1);
                    }
                },
                addRule(groupIndex) {
                    this.groups[groupIndex].push({ param: 'post_type', operator: '==', value: 'page' });
                },
                removeRule(groupIndex, ruleIndex) {
                    if (this.groups[groupIndex].length > 1) {
                        this.groups[groupIndex].splice(ruleIndex, 1);
                    } else {
                        // If removing last rule, remove group? Or just prevent?
                        // Let's remove group if user wants to delete last rule.
                        this.removeGroup(groupIndex);
                    }
                }
            }
        }

        function fieldBuilder(initialFields) {
            return {
                fields: (initialFields || []).map(f => ({
                    ...f,
                    key: Math.random().toString(36).substr(2, 9),
                    default_value: f.default_value || '',
                    placeholder: f.placeholder || ''
                })),
                addField() {
                    this.fields.push({
                        id: null, // New field
                        key: Math.random().toString(36).substr(2, 9),
                        label: '',
                        name: '',
                        type: 'text',
                        default_value: '',
                        placeholder: '',
                        options: {}
                    });
                },
                removeField(index) {
                    if (confirm('Remove this field?')) {
                        this.fields.splice(index, 1);
                    }
                },
                reorder(fromIndex, toIndex) {
                    if (fromIndex === null || toIndex === null) return;
                    if (fromIndex === toIndex) return;

                    const element = this.fields.splice(fromIndex, 1)[0];
                    this.fields.splice(toIndex, 0, element);
                },
                generateName(field) {
                    if (!field.name || field.name == this.slugify(field._oldLabel)) {
                        field.name = this.slugify(field.label);
                    }
                    field._oldLabel = field.label;
                },
                slugify(text) {
                    if (!text) return '';
                    return text.toString().toLowerCase()
                        .replace(/\s+/g, '_')
                        .replace(/[^\w\-]+/g, '')
                        .replace(/\-\-+/g, '_')
                        .replace(/^-+/, '')
                        .replace(/-+$/, '');
                }
            }
        }
    </script>
@endsection