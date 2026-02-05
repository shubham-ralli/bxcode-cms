@extends('admin.components.admin')

@section('title', $group->id ? 'Edit Field Group' : 'Add New Field Group')

@section('content')
    <form action="{{ $group->id ? route('admin.acf.update', $group->id) : route('admin.acf.store') }}" method="POST">
        @csrf
        @if($group->id) @method('PUT') @endif

        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 tracking-tight">
                {{ $group->id ? 'Edit Field Group' : 'New Field Group' }}
            </h1>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Left Column: Fields (Main Content) -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Title Input -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Field Group Title</label>
                    <input type="text" name="title" value="{{ old('title', $group->title) }}"
                        class="w-full text-lg border-gray-300 rounded-lg bg-gray-50 p-3 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="Enter group title..." required>
                </div>

                <!-- Fields Builder (Alpine) -->
                {{-- DEBUG --}}
                @if(config('app.debug'))
                    <div class="hidden">
                        @dump($fieldsTree)
                    </div>
                @endif

                <script>
                    window.acf_fields = @json($fieldsTree);
                </script>

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
                    x-data="fieldBuilder(window.acf_fields)">
                    <div class="px-6 py-5 border-b border-gray-200 bg-gray-50/50 flex justify-between items-center">
                        <div>
                            <h2 class="text-lg font-bold text-gray-800">Fields</h2>
                            <p class="text-xs text-gray-500 mt-1">Manage the layout and fields for this group.</p>
                        </div>
                        <button type="button" @click="addField()"
                            class="inline-flex items-center gap-2 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-sm font-medium rounded-md hover:bg-indigo-100 border border-indigo-200 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                                </path>
                            </svg>
                            Add Field
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
                                <div class="group px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 bg-gray-50 border-b border-gray-100"
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

                                    <!-- Hover Actions -->
                                    <div
                                        class="flex items-center gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity duration-150">
                                        <button type="button"
                                            @click.stop="expanded === index ? expanded = null : expanded = index"
                                            class="text-xs text-indigo-600 hover:text-indigo-700 font-medium px-2.5 py-1 rounded hover:bg-indigo-50 transition-colors">
                                            Edit
                                        </button>
                                        <button type="button" @click.stop="duplicateField(index)"
                                            class="text-xs text-gray-600 hover:text-gray-700 font-medium px-2.5 py-1 rounded hover:bg-gray-100 transition-colors">
                                            Duplicate
                                        </button>
                                        <button type="button" @click.stop="removeField(index)"
                                            class="text-xs text-red-600 hover:text-red-700 font-medium px-2.5 py-1 rounded hover:bg-red-50 transition-colors">
                                            Delete
                                        </button>
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
                                <div x-show="expanded === index" x-transition>
                                    <div class="p-4 bg-white border-t border-gray-100">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <input type="hidden" :name="`fields[${index}][id]`" :value="field.id">

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                    Type</label>
                                                <select x-model="field.type" :name="`fields[${index}][type]`"
                                                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3">
                                                    <optgroup label="Basic">
                                                        <option value="text">Text</option>
                                                        <option value="textarea">Text Area</option>
                                                        <option value="number">Number</option>
                                                        <option value="email">Email</option>
                                                        <option value="url">Url</option>
                                                    </optgroup>
                                                    <optgroup label="Content">
                                                        <option value="image">Image</option>
                                                        <option value="wysiwyg">WYSIWYG Editor</option>
                                                    </optgroup>
                                                    <optgroup label="Choice">
                                                        <option value="select">Select</option>
                                                        <option value="checkbox">Checkbox</option>
                                                        <option value="radio">Radio Button</option>
                                                        <option value="true_false">True / False</option>
                                                        <option value="button_group">Button Group</option>
                                                    </optgroup>
                                                    <optgroup label="Advanced">
                                                        <option value="date_picker">Date Picker</option>
                                                        <option value="date_time_picker">Date Time Picker</option>
                                                        <option value="time_picker">Time Picker</option>
                                                        <option value="color_picker">Color Picker</option>
                                                        <option value="icon_picker">Icon Picker</option>
                                                    </optgroup>
                                                    <optgroup label="Layout">
                                                        <option value="message">Message</option>
                                                        <option value="group">Group</option>
                                                    </optgroup>
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                    Label</label>
                                                <input type="text" x-model="field.label" :name="`fields[${index}][label]`"
                                                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                                    required @blur="generateName(field, fields)">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                    Name</label>
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
                                            <div
                                                x-show="['select', 'checkbox', 'radio', 'button_group'].includes(field.type)">
                                                <label
                                                    class="block text-xs font-semibold text-gray-500 mb-1">Choices</label>
                                                <p class="text-xs text-gray-400 mb-2">Enter each choice on a new
                                                    line.<br>For
                                                    more control, you may specify both a value and label like
                                                    this:<br><code>red : Red</code><br><code>blue : Blue</code></p>
                                                <textarea x-model="field.options.choices"
                                                    :name="`fields[${index}][options][choices]`"
                                                    class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                                    rows="5" placeholder="red : Red&#10;blue : Blue"></textarea>
                                            </div>
                                            <div x-show="['group', 'block', 'row'].includes(field.type)"
                                                class="md:col-span-2 border border-gray-200 rounded-lg p-4 bg-gray-50">
                                                <div class="flex justify-between items-center mb-4">
                                                    <label class="block text-sm font-semibold text-gray-700">Sub
                                                        Fields</label>
                                                    <button type="button"
                                                        @click="field.sub_fields = field.sub_fields || []; field.sub_fields.push({})"
                                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium border border-indigo-200 px-2 py-1 rounded bg-white">
                                                        + Add Sub Field
                                                    </button>
                                                </div>

                                                <div class="space-y-3" x-data="{ subDragging: null, subDropping: null }">
                                                    <template x-for="(subField, subIndex) in (field.sub_fields || [])"
                                                        :key="subField.key || subField.id || subIndex">
                                                        <div class="bg-white transition-colors drag-box border border-gray-200 rounded"
                                                            :id="subField.id ? `sub-field-card-${subField.id}` : `sub-field-card-new-${subIndex}`"
                                                            x-data="{ subHandleActive: false, subExpanded: false }"
                                                            :class="{ 'border-t-2 border-indigo-500': subDropping === subIndex && subDragging !== subIndex }"
                                                            :draggable="subHandleActive"
                                                            @dragstart.stop="subDragging = subIndex; $el.classList.add('opacity-50')"
                                                            @dragend.stop="subDragging = null; $el.classList.remove('opacity-50'); subHandleActive = false"
                                                            @dragover.prevent.stop="subDropping = subIndex"
                                                            @drop.stop="
                                                                                                                                    if (subDragging !== null && subDragging !== subIndex) {
                                                                                                                                        const el = field.sub_fields.splice(subDragging, 1)[0];
                                                                                                                                        field.sub_fields.splice(subIndex, 0, el);
                                                                                                                                        subDragging = null;
                                                                                                                                        subDropping = null;
                                                                                                                                    }
                                                                                                                                ">

                                                            <!-- Header (Click to Toggle) -->
                                                            <div class="px-4 py-3 flex items-center justify-between cursor-pointer hover:bg-gray-50 bg-gray-50 border-b border-gray-100"
                                                                @click="subExpanded = !subExpanded">
                                                                <div class="flex items-center gap-3">
                                                                    <!-- Drag Handle -->
                                                                    <div class="drag-handle cursor-grab text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-200"
                                                                        title="Drag to reorder"
                                                                        @mousedown="subHandleActive = true"
                                                                        @mouseup="subHandleActive = false">
                                                                        <svg class="w-4 h-4" fill="none"
                                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M4 8h16M4 16h16"></path>
                                                                        </svg>
                                                                    </div>

                                                                    <span
                                                                        class="bg-indigo-100 text-indigo-600 text-xs font-bold px-2 py-1 rounded"
                                                                        x-text="subIndex + 1"></span>

                                                                    <div class="flex flex-col">
                                                                        <span class="font-medium text-gray-900 text-sm"
                                                                            x-text="subField.label || '(No Label)'"></span>
                                                                        <span class="text-xs text-mono text-gray-400"
                                                                            x-text="subField.name"></span>
                                                                    </div>
                                                                </div>
                                                                <div class="flex items-center gap-3">
                                                                    <span
                                                                        class="text-xs text-gray-500 uppercase tracking-wider bg-white border border-gray-200 px-2 py-0.5 rounded"
                                                                        x-text="subField.type"></span>
                                                                    <div class="transform transition-transform duration-200"
                                                                        :class="{'rotate-180': subExpanded}">
                                                                        <svg class="h-5 w-5 text-gray-400" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="2"
                                                                                d="M19 9l-7 7-7-7" />
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Body (Form) -->
                                                            <div x-show="subExpanded" x-transition>
                                                                <div class="p-4 bg-white border-t border-gray-100">
                                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                                        <input type="hidden"
                                                                            :name="`fields[${index}][sub_fields][${subIndex}][id]`"
                                                                            :value="subField.id">

                                                                        <div>
                                                                            <label
                                                                                class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                                                Type</label>
                                                                            <select x-model="subField.type"
                                                                                :name="`fields[${index}][sub_fields][${subIndex}][type]`"
                                                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3">
                                                                                <optgroup label="Basic">
                                                                                    <option value="text">Text</option>
                                                                                    <option value="textarea">Text Area
                                                                                    </option>
                                                                                    <option value="number">Number</option>
                                                                                    <option value="email">Email</option>
                                                                                    <option value="url">Url</option>
                                                                                </optgroup>
                                                                                <optgroup label="Content">
                                                                                    <option value="image">Image</option>
                                                                                    <option value="wysiwyg">WYSIWYG Editor
                                                                                    </option>
                                                                                </optgroup>
                                                                                <optgroup label="Choice">
                                                                                    <option value="select">Select</option>
                                                                                    <option value="checkbox">Checkbox
                                                                                    </option>
                                                                                    <option value="radio">Radio Button
                                                                                    </option>
                                                                                    <option value="true_false">True / False
                                                                                    </option>
                                                                                    <option value="button_group">Button
                                                                                        Group</option>
                                                                                </optgroup>
                                                                                <optgroup label="Advanced">
                                                                                    <option value="date_picker">Date Picker
                                                                                    </option>
                                                                                    <option value="date_time_picker">Date
                                                                                        Time Picker</option>
                                                                                    <option value="time_picker">Time Picker
                                                                                    </option>
                                                                                    <option value="color_picker">Color
                                                                                        Picker</option>
                                                                                    <option value="icon_picker">Icon Picker
                                                                                    </option>
                                                                                </optgroup>
                                                                            </select>
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                                                Label</label>
                                                                            <input type="text" x-model="subField.label"
                                                                                :name="`fields[${index}][sub_fields][${subIndex}][label]`"
                                                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50 p-3"
                                                                                required
                                                                                @blur="generateName(subField, field.sub_fields)">
                                                                        </div>

                                                                        <div>
                                                                            <label
                                                                                class="block text-xs font-semibold text-gray-500 mb-1">Field
                                                                                Name</label>
                                                                            <input type="text" x-model="subField.name"
                                                                                :name="`fields[${index}][sub_fields][${subIndex}][name]`"
                                                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                                                                required>
                                                                        </div>

                                                                        <div x-show="subField.type !== 'image'">
                                                                            <label
                                                                                class="block text-xs font-semibold text-gray-500 mb-1">Default
                                                                                Value</label>
                                                                            <input type="text"
                                                                                x-model="subField.default_value"
                                                                                :name="`fields[${index}][sub_fields][${subIndex}][default_value]`"
                                                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3">
                                                                        </div>
                                                                        <div
                                                                            x-show="['select', 'checkbox', 'radio'].includes(subField.type)">
                                                                            <label
                                                                                class="block text-xs font-semibold text-gray-500 mb-1">Choices</label>
                                                                            <p class="text-xs text-gray-400 mb-2">Enter each
                                                                                choice on a new line.<br>For
                                                                                more control, you may specify both a value
                                                                                and
                                                                                label like
                                                                                this:<br><code>red : Red</code><br><code>blue : Blue</code>
                                                                            </p>
                                                                            <textarea
                                                                                x-model="subField.options = subField.options || {}; subField.options.choices"
                                                                                :name="`fields[${index}][sub_fields][${subIndex}][options][choices]`"
                                                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                                                                rows="5"
                                                                                placeholder="red : Red&#10;blue : Blue"></textarea>
                                                                        </div>

                                                                        <div x-show="subField.type === 'message'">
                                                                            <label
                                                                                class="block text-xs font-semibold text-gray-500 mb-1">Message
                                                                                Body</label>
                                                                            <p class="text-xs text-gray-400 mb-2">Can use
                                                                                HTML.
                                                                            </p>
                                                                            <textarea
                                                                                x-model="subField.options = subField.options || {}; subField.options.message"
                                                                                :name="`fields[${index}][sub_fields][${subIndex}][options][message]`"
                                                                                class="w-full text-sm border-gray-300 rounded-lg shadow-sm bg-gray-50 focus:border-indigo-500 focus:ring-indigo-500 p-3"
                                                                                rows="4"></textarea>
                                                                        </div>

                                                                        <div class="flex items-end justify-end">
                                                                            <button type="button"
                                                                                @click="field.sub_fields.splice(subIndex, 1)"
                                                                                class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center gap-1 px-3 py-2 rounded hover:bg-red-50 transition-colors">
                                                                                <svg class="w-4 h-4" fill="none"
                                                                                    stroke="currentColor"
                                                                                    viewBox="0 0 24 24">
                                                                                    <path stroke-linecap="round"
                                                                                        stroke-linejoin="round"
                                                                                        stroke-width="2"
                                                                                        d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
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

                                                    <div x-show="!field.sub_fields || field.sub_fields.length === 0"
                                                        class="text-center text-xs text-gray-400 py-4 italic">
                                                        No sub fields added.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-end justify-end">
                                                <button type="button" @click="removeField(index)"
                                                    class="text-red-600 hover:text-red-800 text-sm font-medium flex items-center gap-1 px-3 py-2 rounded hover:bg-red-50 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2">
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
                </div> <!-- End Fields Builder -->
            </div> <!-- End Left Column -->

            <!-- Right Column: Settings & Rules -->
            <div class="lg:col-span-1 space-y-6">

                <!-- Status & Save -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-4">Publish</h3>

                    <div class="mb-6">
                        <label class="flex items-center cursor-pointer justify-between">
                            <span class="text-sm font-medium text-gray-900">Active</span>
                            <div class="relative">
                                <input type="hidden" name="active" value="0">
                                <input type="checkbox" name="active" value="1" class="sr-only peer" {{ $group->active ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600">
                                </div>
                            </div>
                        </label>
                        <p class="text-xs text-gray-500 mt-2">Inactive field groups will not appear in editors.</p>
                    </div>

                    <div class="border-t border-gray-100 pt-4 mt-4">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow font-medium transition-colors">
                            Save Changes
                        </button>
                        @if($group->id)
                            <div class="mt-3 text-center">
                                <a href="{{ route('admin.acf.destroy', $group->id) }}"
                                    onclick="return confirm('Are you sure? This will delete all associated data.');"
                                    class="text-xs text-red-500 hover:text-red-700 underline">Move to Trash</a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Location Rules -->
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

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
                    x-data="ruleBuilder(window.acfEditorData.rules, window.acfEditorData.postTypes, window.acfEditorData.templates, window.acfEditorData.roles, window.acfEditorData.pages)">
                    <h3 class="text-sm font-bold text-gray-900 uppercase tracking-wider mb-2">Location Rules</h3>
                    <p class="text-xs text-gray-500 mb-4 leading-relaxed">Show this field group if these rules match.</p>

                    <div class="space-y-3">
                        <template x-for="(group, gIndex) in groups" :key="gIndex">
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg relative group">
                                <div
                                    class="absolute -top-2 left-3 bg-gray-50 px-1 text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                                    <span x-show="gIndex === 0">Rule Group</span>
                                    <span x-show="gIndex > 0">OR</span>
                                </div>

                                <div class="space-y-2 mt-1">
                                    <template x-for="(rule, rIndex) in group" :key="rIndex">
                                        <div class="flex flex-col gap-2 relative">
                                            <div x-show="rIndex > 0"
                                                class="text-[10px] font-bold text-gray-400 uppercase text-center py-1">AND
                                            </div>

                                            <div class="flex gap-2">
                                                <!-- Param -->
                                                <select x-model="rule.param" @change="rule.value = ''"
                                                    class="block w-full text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                    <option value="post_type">Post Type</option>
                                                    <option value="page_template">Page Template</option>
                                                    <option value="post_status">Post Status</option>
                                                    <option value="current_user_role">User Role</option>
                                                </select>
                                            </div>

                                            <div class="flex gap-2">
                                                <!-- Operator -->
                                                <select x-model="rule.operator"
                                                    class="block w-1/3 text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                    <option value="==">is equal to</option>
                                                    <option value="!=">is not equal to</option>
                                                </select>

                                                <!-- Value -->
                                                <div class="w-2/3">
                                                    <template x-if="rule.param === 'post_type'">
                                                        <select x-model="rule.value"
                                                            class="block w-full text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                            <template x-for="(label, value) in options.postTypes"
                                                                :key="value">
                                                                <option :value="value" x-text="label"
                                                                    :selected="value == rule.value"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                    <template x-if="rule.param === 'page_template'">
                                                        <select x-model="rule.value"
                                                            class="block w-full text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                            <template x-for="(label, value) in options.templates"
                                                                :key="value">
                                                                <option :value="value" x-text="label"
                                                                    :selected="value == rule.value"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                    <template x-if="rule.param === 'post_status'">
                                                        <select x-model="rule.value"
                                                            class="block w-full text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                            <option value="publish" :selected="'publish' == rule.value">
                                                                Published</option>
                                                            <option value="draft" :selected="'draft' == rule.value">Draft
                                                            </option>
                                                        </select>
                                                    </template>
                                                    <template x-if="rule.param === 'current_user_role'">
                                                        <select x-model="rule.value"
                                                            class="block w-full text-xs border-gray-300 rounded-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                            <template x-for="(label, value) in options.roles" :key="value">
                                                                <option :value="value" x-text="label"
                                                                    :selected="value == rule.value"></option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                    <template x-if="rule.param === 'page'">
                                                        <select x-model="rule.value"
                                                            class="block w-full text-xs border-gray-300 rounded shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-2">
                                                            <template x-for="page in options.pages" :key="page.id">
                                                                <option :value="String(page.id)" x-text="page.title"
                                                                    :selected="String(page.id) == String(rule.value)">
                                                                </option>
                                                            </template>
                                                        </select>
                                                    </template>
                                                </div>
                                            </div>

                                            <button type="button" @click="removeRule(gIndex, rIndex)"
                                                class="self-end text-xs text-red-400 hover:text-red-600 underline">
                                                Remove
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <div class="mt-2 flex justify-between items-center border-t border-gray-200 pt-2">
                                    <button type="button" @click="addRule(gIndex)"
                                        class="text-xs text-indigo-600 font-bold hover:text-indigo-800">
                                        + AND Rule
                                    </button>
                                    <button type="button" @click="removeGroup(gIndex)"
                                        class="text-xs text-red-500 hover:text-red-700">
                                        Delete Group
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 pt-2 border-t border-gray-100">
                        <button type="button" @click="addGroup()"
                            class="w-full py-2 bg-gray-50 border border-dashed border-gray-300 text-gray-600 text-xs font-bold rounded hover:bg-white hover:border-gray-400 transition-colors">
                            + Add Rule Group (OR)
                        </button>
                    </div>

                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="rules" :value="JSON.stringify(groups)">
                </div>
            </div> <!-- End Right Column -->

        </div> <!-- End Main Grid -->
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
                fields: [],
                init() {
                    console.log('Initial Fields:', initialFields);
                    this.fields = processFieldsRecursive(initialFields || []);
                },
                addField() {
                    this.fields.push({
                        id: null, // New field
                        key: Math.random().toString(36).substr(2, 9),
                        label: '',
                        name: '',
                        type: 'text',
                        default_value: '',
                        placeholder: '',
                        options: {},
                        sub_fields: []
                    });
                },
                duplicateField(index) {
                    const field = JSON.parse(JSON.stringify(this.fields[index]));
                    // Generate completely new unique key for Alpine.js tracking
                    field.key = Math.random().toString(36).substr(2, 9);
                    // Remove ID so it creates a new field in the database
                    delete field.id;
                    // Update label
                    field.label = field.label + ' (Copy)';
                    // Clear the name so generateName will create a new one
                    field.name = '';
                    field._oldLabel = '';
                    // Generate completely new unique field name
                    this.generateName(field, this.fields);
                    
                    // Process sub-fields recursively
                    if (field.sub_fields && Array.isArray(field.sub_fields)) {
                        field.sub_fields = this.duplicateSubFields(field.sub_fields);
                    }
                    
                    // Insert after the original
                    this.fields.splice(index + 1, 0, field);
                    // Expand the new field
                    this.expanded = index + 1;
                },
                duplicateSubFields(subFields) {
                    return subFields.map(subField => {
                        // Deep clone the sub-field
                        const newSubField = JSON.parse(JSON.stringify(subField));
                        // Generate new key for Alpine.js tracking
                        newSubField.key = Math.random().toString(36).substr(2, 9);
                        // Remove ID so it creates a new field
                        delete newSubField.id;
                        // Update label
                        newSubField.label = newSubField.label + ' (Copy)';
                        // Clear the name so it will be regenerated
                        newSubField.name = '';
                        newSubField._oldLabel = '';
                        // Generate new unique name
                        this.generateName(newSubField, subFields);
                        
                        return newSubField;
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
                generateName(field, siblings) {
                    siblings = siblings || this.fields;

                    // Only auto-update if name is empty OR name matches the slugified version of the OLD label
                    if (!field.name || field.name == this.slugify(field._oldLabel)) {
                        let baseName = this.slugify(field.label);
                        let finalName = baseName;
                        let counter = 1;

                        // Check uniqueness against siblings
                        while (this.nameExists(finalName, siblings, field)) {
                            finalName = baseName + '_' + counter;
                            counter++;
                        }

                        field.name = finalName;
                    }
                    field._oldLabel = field.label;
                },
                nameExists(name, siblings, currentField) {
                    // Check within current siblings array
                    if (siblings.some(f => f !== currentField && f.name === name)) {
                        return true;
                    }

                    // GLOBAL CHECK: Also check all root fields and their sub-fields
                    // This ensures sub-fields can't duplicate root field names and vice versa
                    const allFieldNames = this.getAllFieldNames(this.fields, currentField);
                    return allFieldNames.includes(name);
                },
                getAllFieldNames(fields, exclude) {
                    let names = [];
                    fields.forEach(f => {
                        if (f !== exclude && f.name) {
                            names.push(f.name);
                        }
                        // Recursively get sub-field names
                        if (f.sub_fields && Array.isArray(f.sub_fields)) {
                            names = names.concat(this.getAllFieldNames(f.sub_fields, exclude));
                        }
                    });
                    return names;
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

        // Helper function for recursive processing
        function processFieldsRecursive(fields) {
            if (!Array.isArray(fields)) return [];
            return fields.map(f => {
                return {
                    ...f,
                    key: f.key || Math.random().toString(36).substr(2, 9),
                    default_value: f.default_value || '',
                    placeholder: f.placeholder || '',
                    sub_fields: f.sub_fields ? processFieldsRecursive(f.sub_fields) : []
                };
            });
        }
    </script>
@endsection