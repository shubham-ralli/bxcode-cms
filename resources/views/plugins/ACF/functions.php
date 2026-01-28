<?php

if (!plugin_is_active('ACF')) {
    return;
}

add_admin_menu(
    'Custom Fields',
    'acf',
    url('lp-admin/acf'),
    '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>',
    80
);

if (!function_exists('get_field')) {
    function get_field($key, $postId = null)
    {
        if (!$postId) {
            $post = \Illuminate\Support\Facades\View::shared('post');
            $postId = $post ? $post->id : null;
        }

        if (!$postId)
            return null;

        // Try to find the value in acf_values
        // Using raw query for speed or model? Model is safer.
        $valueEntry = \Plugins\ACF\src\Models\AcfValue::where('entity_type', 'App\Models\Post')
            ->where('entity_id', $postId)
            ->where('field_name', $key)
            ->first();

        return $valueEntry ? $valueEntry->value : null;
        return $valueEntry ? $valueEntry->value : null;
    }
}

// 2. Hook into Post Editor (Render Fields)
// Note: 'admin_post_add' is called on both Create and Edit in this CMS setup.
// 2. Hook into Post Editor (Render Fields)
// 2. Hook into Post Editor (Render Fields)
add_action('admin_post_add', function ($post) {
    // 1. Build Context
    $context = [
        'post_type' => ($post ? $post->type : null) ?? request('type', 'post'),
        'post_status' => ($post ? $post->status : null) ?? 'draft',
        'post_template' => ($post ? $post->template : null) ?? 'default',
        'page' => ($post ? $post->id : null) ?? null,
        'current_user_role' => auth()->user() ? auth()->user()->role : 'guest',
    ];

    // 2. Fetch All Active Groups
    $matchedGroups = \Plugins\ACF\src\Models\FieldGroup::where('active', true)
        ->orderBy('menu_order')
        ->with('fields')
        ->get();

    if ($matchedGroups->isEmpty()) {
        return;
    }

    // Normalize Rules for Frontend
    $matchedGroups->each(function ($group) {
        if (is_string($group->rules)) {
            $group->rules = json_decode($group->rules, true) ?? [];
        }

        // Ensure array of arrays structure (Groups of Rules)
        // If legacy single object or empty
        if (empty($group->rules)) {
            // No rules = Show everywhere? or Nowhere? 
            // Let's assume defaults to: Show everywhere or handle in JS.
            // Actually, usually no rules means it doesn't show up.
        } else if (!isset($group->rules[0]) || !is_array($group->rules[0])) {
            // Legacy flat array/object -> wrap in array
            $legacyGroup = [];
            foreach ($group->rules as $k => $v) {
                if (is_string($k)) // "post_type": "page"
                    $legacyGroup[] = ['param' => $k, 'operator' => '==', 'value' => $v];
                elseif (is_array($v)) // [{param...}, {param...}]
                    $legacyGroup[] = $v;
            }
            $group->rules = [$legacyGroup];
        }
    });

    // Debug Output (Hidden)
    echo "<!-- ACF Debug: Context=" . json_encode($context) . " -->";

    echo plugin_view('acf::render', ['post' => $post, 'groups' => $matchedGroups, 'context' => $context]);
});

// 3. Hook into Save Post (Save Fields)
add_action('save_post', function ($postId, $request) {
    if (!$request->has('acf')) {
        return;
    }

    $acfData = $request->input('acf');
    if (!is_array($acfData)) {
        return;
    }

    foreach ($acfData as $fieldKey => $value) {
        // Update or Create
        \Plugins\ACF\src\Models\AcfValue::updateOrCreate(
            [
                'entity_type' => 'App\Models\Post',
                'entity_id' => $postId,
                'field_name' => $fieldKey
            ],
            [
                'value' => $value
            ]
        );
    }
});


// 4. Admin Routes (Seo-Pattern)
use Plugins\ACF\src\Http\Controllers\ACFController;

// Ensure Autoloader knows about our namespace (PluginServiceProvider does this dynamically)
// But just in case, manual check or let strict class reference trigger it.

add_plugin_admin_route('acf', [ACFController::class, 'index'], 'admin.acf.index');
add_plugin_admin_route('acf/create', [ACFController::class, 'create'], 'admin.acf.create');
add_plugin_admin_route('acf', [ACFController::class, 'store'], 'admin.acf.store', ['POST']);
add_plugin_admin_route('acf/{id}/edit', [ACFController::class, 'edit'], 'admin.acf.edit');
add_plugin_admin_route('acf/{id}', [ACFController::class, 'update'], 'admin.acf.update', ['PUT']);
add_plugin_admin_route('acf/{id}', [ACFController::class, 'destroy'], 'admin.acf.destroy', ['DELETE']);
add_plugin_admin_route('acf/action/bulk', [ACFController::class, 'bulkDestroy'], 'admin.acf.bulk', ['POST']);
add_plugin_admin_route('acf/{id}/toggle', [ACFController::class, 'toggleStatus'], 'admin.acf.toggle', ['POST']);

