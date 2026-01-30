<?php

if (!plugin_is_active('ACF')) {
    return;
}

add_admin_menu(
    'ACF',
    'acf',
    url('lp-admin/acf'),
    '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 0 01.707.293l5.414 5.414a1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
    100
);

// Add submenus
add_admin_submenu('acf', 'Field Groups', url('lp-admin/acf/field-groups'), 10);
add_admin_submenu('acf', 'Post Types', url('lp-admin/acf/post-types'), 20);

// Register Custom Post Types in Admin Menu
try {
    // Check if table exists to avoid migration errors on fresh install
    if (\Illuminate\Support\Facades\Schema::hasTable('custom_post_types')) {
        $customTypes = \Illuminate\Support\Facades\DB::table('custom_post_types')
            ->where('active', 1)
            ->get();

        foreach ($customTypes as $type) {
            $settings = json_decode($type->settings, true) ?? [];

            // Default show_in_menu to false if not set (since checkbox sends 1 if checked, nothing if not)
            // But for existing types created before this setting, defaulting to True might be safer?
            // User says "not working", implying they unchecked it and it still shows.
            // Unchecked = missing key. 
            // So default must be false if we want strict checkbox mapping.
            // However, use '1' string check for safety.
            if (empty($settings['show_in_menu'])) {
                continue;
            }
            $icon = $settings['menu_icon'] ?? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>';

            $labels = $validated['labels'] ?? ($settings['labels'] ?? []);
            // Fallbacks if labels aren't in settings structure (migration support)
            // But usually they are merged in update/store.
            // Let's decode labels from the separate column if it exists or use settings.
            if (isset($type->labels)) {
                $labels = json_decode($type->labels, true) ?? [];
            }

            $menuName = $labels['menu_name'] ?? ($type->plural_label);
            $allItems = $labels['all_items'] ?? ("All " . $type->plural_label);
            $addNew = $labels['add_new'] ?? "Add New";

            add_admin_menu(
                $menuName,
                $type->key,
                url('lp-admin/posts') . '?type=' . $type->key,
                $icon,
                25
            );

            // Add Submenus
            add_admin_submenu($type->key, $allItems, url('lp-admin/posts') . '?type=' . $type->key, 10);
            add_admin_submenu($type->key, $addNew, url('lp-admin/posts/create') . '?type=' . $type->key, 20);
        }
    }
} catch (\Exception $e) {
    // Fail silently
}

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
use Plugins\ACF\src\Http\Controllers\PostTypeController;

// Ensure Autoloader knows about our namespace (PluginServiceProvider does this dynamically)
// But just in case, manual check or let strict class reference trigger it.

// Field Groups Routes (acf-field-group)
add_plugin_admin_route('acf/field-groups', [ACFController::class, 'index'], 'admin.acf.index');
add_plugin_admin_route('acf/field-groups/create', [ACFController::class, 'create'], 'admin.acf.create');
add_plugin_admin_route('acf/field-groups', [ACFController::class, 'store'], 'admin.acf.store', ['POST']);
add_plugin_admin_route('acf/field-groups/{id}/edit', [ACFController::class, 'edit'], 'admin.acf.edit');
add_plugin_admin_route('acf/field-groups/{id}', [ACFController::class, 'update'], 'admin.acf.update', ['PUT']);
add_plugin_admin_route('acf/field-groups/{id}', [ACFController::class, 'destroy'], 'admin.acf.destroy', ['DELETE']);
add_plugin_admin_route('acf/field-groups/action/bulk', [ACFController::class, 'bulkDestroy'], 'admin.acf.bulk', ['POST']);
add_plugin_admin_route('acf/field-groups/{id}/toggle', [ACFController::class, 'toggleStatus'], 'admin.acf.toggle', ['POST']);

// Post Types Routes (acf-post-type)
add_plugin_admin_route('acf/post-types', [PostTypeController::class, 'index'], 'admin.acf.post-types.index');
add_plugin_admin_route('acf/post-types/create', [PostTypeController::class, 'create'], 'admin.acf.post-types.create');
add_plugin_admin_route('acf/post-types', [PostTypeController::class, 'store'], 'admin.acf.post-types.store', ['POST']);
add_plugin_admin_route('acf/post-types/{id}/edit', [PostTypeController::class, 'edit'], 'admin.acf.post-types.edit');
add_plugin_admin_route('acf/post-types/{id}', [PostTypeController::class, 'update'], 'admin.acf.post-types.update', ['PUT']);
add_plugin_admin_route('acf/post-types/{id}', [PostTypeController::class, 'destroy'], 'admin.acf.post-types.destroy', ['DELETE']);
add_plugin_admin_route('acf/post-types/action/bulk', [PostTypeController::class, 'bulkDestroy'], 'admin.acf.post-types.bulk', ['POST']);
add_plugin_admin_route('acf/post-types/{id}/toggle', [PostTypeController::class, 'toggleStatus'], 'admin.acf.post-types.toggle', ['POST']);
