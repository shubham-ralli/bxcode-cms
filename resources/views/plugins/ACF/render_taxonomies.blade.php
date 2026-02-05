@if($taxonomies && $taxonomies->count() > 0)
    <div class="space-y-6 mt-6">
        @foreach($taxonomies as $tax)
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-gray-900 mb-2">{{ $tax->plural_label }}</h3>

                @if($tax->hierarchical)
                    {{-- Hierarchical (Checkboxes) --}}
                    <div class="max-h-48 overflow-y-auto border border-gray-200 rounded p-2 mb-2 bg-gray-50">
                        @php
                            $existingTerms = \App\Models\Tag::where('taxonomy', $tax->key)->get();
                            $selectedIds = $post ? $post->tags->where('taxonomy', $tax->key)->pluck('id')->toArray() : [];
                        @endphp

                        @forelse($existingTerms as $term)
                            <label class="flex items-center space-x-2 mb-1">
                                <input type="checkbox" name="tax_input[{{ $tax->key }}][]" value="{{ $term->id }}"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ in_array($term->id, $selectedIds) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $term->name }}</span>
                            </label>
                        @empty
                            <p class="text-xs text-gray-500">No {{ $tax->plural_label }} found.</p>
                        @endforelse
                    </div>
                @else
                    {{-- Non-Hierarchical (Input) --}}
                    @php
                        $selectedNames = $post ? $post->tags->where('taxonomy', $tax->key)->pluck('name')->implode(', ') : '';
                    @endphp
                    <div>
                        <input type="text" name="tax_input[{{ $tax->key }}]" value="{{ $selectedNames }}"
                            class="w-full border-gray-300 rounded-md text-sm focus:border-indigo-500 focus:ring-indigo-500 p-2"
                            placeholder="Add {{ $tax->singular_label }}...">
                        <p class="text-xs text-gray-500 mt-1">Separate with commas</p>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif