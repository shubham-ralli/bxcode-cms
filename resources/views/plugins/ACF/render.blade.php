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
    <div id="acf-group-{{ $group->id }}" class="acf-group bg-white rounded-lg shadow-sm mb-6"
        data-rules='@json($rulesArray, JSON_HEX_APOS)' style="display: none;">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="font-semibold text-gray-700">{{ $group->title }}</h3>
        </div>
        <div class="p-4 space-y-4">
            @foreach($group->fields->whereNull('parent_id') as $field)
                @include('plugins.ACF.field-render', ['field' => $field, 'post' => $post ?? null])
            @endforeach
        </div>
    </div>
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
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

                        // Map rule params to context values
                        let contextVal;
                        if (param === 'post_type') {
                            contextVal = currentContext.post_type;
                        } else if (param === 'page_template') {
                            contextVal = currentContext.post_template || 'default';
                        } else if (param === 'post_status') {
                            contextVal = currentContext.post_status;
                        } else if (param === 'page') {
                            contextVal = currentContext.page_id;
                        } else if (param === 'current_user_role') {
                            contextVal = currentContext.user_role;
                        } else {
                            contextVal = currentContext[param];
                        }

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

    function removeAcfImage(fieldName) {
        document.getElementById(fieldName + '_input').value = '';
        const preview = document.getElementById(fieldName + '_preview');
        preview.src = '';
        preview.classList.add('hidden');
        document.getElementById(fieldName + '_placeholder').classList.remove('hidden');
        document.getElementById(fieldName + '_actions').classList.add('hidden');
    }
</script>