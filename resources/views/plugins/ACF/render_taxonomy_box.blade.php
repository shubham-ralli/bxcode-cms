<div class="taxonomy-meta-box p-4" id="taxonomy-{{ $taxonomy->key }}">
    @if($taxonomy->hierarchical)
        {{-- Hierarchical (Like Categories) --}}
        <div x-data="{
                        selected: {{ json_encode($post->allTags->where('taxonomy', $taxonomy->key)->pluck('id')->values()) }},
                        showAdd: false,
                        newTermName: '',
                        terms: {{ \App\Models\Tag::where('taxonomy', $taxonomy->key)->get()->map(function ($t) {
            return ['id' => $t->id, 'name' => $t->name]; }) }},
                        async addTerm() {
                            if (!this.newTermName.trim()) return;
                            try {
                                const response = await fetch('{{ route('admin.tags.store') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({
                                        name: this.newTermName,
                                        taxonomy: '{{ $taxonomy->key }}'
                                    })
                                });
                                if (response.ok) {
                                    const data = await response.json();
                                    this.terms.unshift(data);
                                    this.selected.push(data.id);
                                    this.newTermName = '';
                                    this.showAdd = false;
                                } else {
                                    alert('Error adding term');
                                }
                            } catch (e) {
                                console.error(e);
                                alert('Failed to add term');
                            }
                        }
                    }">
            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded p-2 bg-gray-50 mb-3 space-y-2">
                <template x-for="term in terms" :key="term.id">
                    <label class="flex items-start space-x-2 cursor-pointer">
                        <input type="checkbox" name="tax_input[{{ $taxonomy->key }}][]" :value="term.id" x-model="selected"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 mt-1">
                        <span class="text-sm text-gray-700 select-none" x-text="term.name"></span>
                    </label>
                </template>
                <div x-show="terms.length === 0" class="text-xs text-gray-400 text-center py-2">
                    No {{ strtolower($taxonomy->plural_label) }} found.
                </div>
            </div>

            <button type="button" @click="showAdd = !showAdd"
                class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
                <span x-text="showAdd ? '-' : '+'"></span> Add New {{ Str::singular($taxonomy->plural_label) }}
            </button>

            <div x-show="showAdd" x-transition class="mt-3">
                <input type="text" x-model="newTermName" @keydown.enter.prevent="addTerm()"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm mb-2"
                    placeholder="New Name">
                <div class="flex justify-between items-center">
                    <button type="button" @click="addTerm()"
                        class="bg-white border border-gray-300 text-gray-700 px-3 py-1 rounded text-xs font-medium hover:bg-gray-50">
                        Add New
                    </button>
                </div>
            </div>
        </div>

    @else
        {{-- Non-Hierarchical (Like Tags) --}}
        <div x-data="{
                        tags: {{ json_encode($post->allTags->where('taxonomy', $taxonomy->key)->pluck('name')->values()) }},
                        newTag: '',
                        availableTags: {{ \App\Models\Tag::where('taxonomy', $taxonomy->key)->pluck('name') }},
                        filteredTags: [],
                        addTag(tag) {
                            tag = tag.trim();
                            if (tag !== '' && !this.tags.includes(tag)) {
                                this.tags.push(tag);
                            }
                            this.newTag = '';
                            this.filteredTags = [];
                        },
                        removeTag(index) {
                            this.tags.splice(index, 1);
                        },
                        addFromInput() {
                            if (this.newTag.includes(',')) {
                                this.newTag.split(',').forEach(t => this.addTag(t));
                            } else {
                                this.addTag(this.newTag);
                            }
                        },
                        init() {
                            this.$watch('newTag', (val) => {
                                if (val.length < 1) {
                                    this.filteredTags = [];
                                    return;
                                }
                                this.filteredTags = this.availableTags.filter(tag => 
                                    tag.toLowerCase().includes(val.toLowerCase()) && !this.tags.includes(tag)
                                );
                            });
                        }
                    }">
            {{-- Selected Tags Display --}}
            <div class="flex flex-wrap gap-2 mb-3">
                <template x-for="(tag, index) in tags" :key="index">
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                        <span x-text="tag"></span>
                        <button type="button" @click="removeTag(index)"
                            class="flex-shrink-0 ml-1.5 h-4 w-4 rounded-full text-indigo-400 hover:bg-indigo-200 hover:text-indigo-500 focus:outline-none focus:bg-indigo-500 focus:text-white inline-flex items-center justify-center">
                            <span class="sr-only">Remove</span>
                            <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
                                <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
                            </svg>
                        </button>
                        {{-- Hidden input for form submission --}}
                        <input type="hidden" name="tax_input[{{ $taxonomy->key }}][]" :value="tag">
                    </span>
                </template>
            </div>

            {{-- Input Area --}}
            <div class="relative">
                <input type="text" x-model="newTag" @keydown.enter.prevent="addFromInput()"
                    @keydown.comma.prevent="addFromInput()"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Add New (press Enter)">

                {{-- Suggestions --}}
                <div x-show="filteredTags.length > 0" @click.away="filteredTags = []"
                    class="absolute z-10 w-full bg-white border border-gray-200 rounded-md mt-1 max-h-40 overflow-y-auto shadow-lg"
                    style="display: none;">
                    <template x-for="tag in filteredTags">
                        <div @click="addTag(tag)" class="px-3 py-2 hover:bg-indigo-50 cursor-pointer text-sm text-gray-700">
                            <span x-text="tag"></span>
                        </div>
                    </template>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Separate with commas or the Enter key.</p>
        </div>
    @endif
</div>