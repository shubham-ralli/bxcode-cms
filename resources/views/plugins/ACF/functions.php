<?php

// Ensure helpers are loaded
if (file_exists(app_path('Helpers/plugin_helpers.php'))) {
    require_once app_path('Helpers/plugin_helpers.php');
}

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
add_admin_submenu('acf', 'Taxonomies', url('lp-admin/acf/taxonomies'), 30);

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

            // CPTs are now rendered explicitly in admin.blade.php to support full submenu logic.
            // Keeping this commented out prevents duplicate entries in the generated menu loop.
            /*
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

            // Add Taxonomy Submenus for this CPT
            if (\Illuminate\Support\Facades\Schema::hasTable('custom_taxonomies')) {
                $cptTaxonomies = \Plugins\ACF\src\Models\CustomTaxonomy::where('active', 1)->get();
                foreach ($cptTaxonomies as $tax) {
                    if (in_array($type->key, $tax->post_types ?? [])) {
                        add_admin_submenu(
                            $type->key,
                            $tax->plural_label,
                            route('admin.tags.index', ['taxonomy' => $tax->key, 'type' => $type->key]),
                            30
                        );
                    }
                }
            }
            */
        }
    }
} catch (\Exception $e) {
    // Fail silently
}

if (!function_exists('get_field')) {
    function get_field($selector, $postId = null)
    {
        if (!$postId) {
            $post = \Illuminate\Support\Facades\View::shared('post');
            $postId = $post ? $post->id : null;
        }

        if (!$postId)
            return null;

        // 1. Get Field Definition to check type (Group support)
        // IMPORTANT: Only get ROOT-level fields (not sub-fields)
        $fieldDef = \Plugins\ACF\src\Models\Field::where('name', $selector)
            ->whereNull('parent_id')
            ->first();

        if (!$fieldDef) {
            // Field not found or it's a sub-field
            return null;
        }

        if ($fieldDef->type === 'group') {
            // It's a Group! Fetch all children.
            $children = $fieldDef->children;
            $groupData = [];

            foreach ($children as $child) {
                // For groups, we need to get sub-field values directly
                $valueEntry = \Plugins\ACF\src\Models\AcfValue::where('entity_type', 'App\Models\Post')
                    ->where('entity_id', $postId)
                    ->where('field_name', $child->name)
                    ->first();

                $groupData[$child->name] = $valueEntry ? $valueEntry->value : null;
            }

            return $groupData;
        }

        // 2. Standard Value Fetch (for root-level fields only)
        $valueEntry = \Plugins\ACF\src\Models\AcfValue::where('entity_type', 'App\Models\Post')
            ->where('entity_id', $postId)
            ->where('field_name', $selector)
            ->first();

        return $valueEntry ? $valueEntry->value : null;
    }
}

if (!function_exists('get_sub_field')) {
    function get_sub_field($selector, $postId = null)
    {
        if (!$postId) {
            $post = \Illuminate\Support\Facades\View::shared('post');
            $postId = $post ? $post->id : null;
        }

        if (!$postId)
            return null;

        // Only get SUB-fields (has parent_id)
        $fieldDef = \Plugins\ACF\src\Models\Field::where('name', $selector)
            ->whereNotNull('parent_id')
            ->first();

        if (!$fieldDef) {
            // Not a sub-field
            return null;
        }

        // Fetch value
        $valueEntry = \Plugins\ACF\src\Models\AcfValue::where('entity_type', 'App\Models\Post')
            ->where('entity_id', $postId)
            ->where('field_name', $selector)
            ->first();

        return $valueEntry ? $valueEntry->value : null;
    }
}

// 2. Hook into Post Form (Add Meta Boxes)
add_action('admin_post_add', function ($postOrContext = null) {
    // 1. Determine Context & Post Type
    $context = [];
    $post = null;
    $currentPostType = null;

    if (is_object($postOrContext)) {
        // It's a Post Object (passed from form.blade.php)
        $post = $postOrContext;
        $currentPostType = $post->type ?? null;
        $context['post_type'] = $currentPostType;
    } elseif (is_array($postOrContext)) {
        // It's a Context Array (legacy support)
        $context = $postOrContext;
        $currentPostType = $context['post_type'] ?? null;
    }

    // Fallback if type is still missing
    if (!$currentPostType) {
        $currentPostType = request('type', 'post');
    }

    // Populate context for JavaScript
    $context['post_type'] = $currentPostType;
    if ($post) {
        $context['page_id'] = $post->id;
        $context['post_template'] = $post->template ?? 'default';
        $context['post_status'] = $post->status ?? 'draft';
    } else {
        $context['post_template'] = request('template', 'default');
        $context['post_status'] = request('status', 'draft');
    }
    $context['user_role'] = auth()->user()->role ?? 'subscriber';

    // 2. Register Custom Fields (ACF)
    $matchedGroups = collect();
    if (\Illuminate\Support\Facades\Schema::hasTable('acf_field_groups')) {
        $groups = \Plugins\ACF\src\Models\FieldGroup::where('active', 1)
            ->with([
                'locationRules',
                'fields' => function ($query) {
                    $query->orderBy('menu_order', 'asc');
                }
            ])
            ->orderBy('menu_order', 'asc')
            ->get();

        $matchedGroups = $groups->filter(function ($group) use ($currentPostType, $post) {
            if ($group->locationRules->isEmpty()) {
                return false;
            }

            // Group rules by group_index (OR logic between groups)
            $ruleGroups = $group->locationRules->groupBy('group_index');

            // Check ANY group (OR logic)
            foreach ($ruleGroups as $index => $rules) {
                // Check ALL rules in this group (AND logic)
                $groupMatch = true;
                foreach ($rules as $rule) {
                    $match = false;

                    // POST TYPE CHECK
                    if ($rule->param === 'post_type') {
                        if ($rule->operator === '==') {
                            $match = ($rule->value === $currentPostType);
                        } elseif ($rule->operator === '!=') {
                            $match = ($rule->value !== $currentPostType);
                        }
                    }
                    // PAGE TEMPLATE CHECK
                    elseif ($rule->param === 'page_template') {
                        // Get template from post or request
                        $currentTemplate = $post ? ($post->template ?? 'default') : request('template', 'default');
                        if (empty($currentTemplate)) {
                            $currentTemplate = 'default';
                        }

                        if ($rule->operator === '==') {
                            $match = ($rule->value === $currentTemplate);
                        } elseif ($rule->operator === '!=') {
                            $match = ($rule->value !== $currentTemplate);
                        }
                    }
                    // POST STATUS CHECK
                    elseif ($rule->param === 'post_status') {
                        // Get status from post or request
                        $currentStatus = $post ? ($post->status ?? 'draft') : request('status', 'draft');

                        if ($rule->operator === '==') {
                            $match = ($rule->value === $currentStatus);
                        } elseif ($rule->operator === '!=') {
                            $match = ($rule->value !== $currentStatus);
                        }
                    }
                    // PAGE (by ID) CHECK
                    elseif ($rule->param === 'page') {
                        // Only check if current post type is 'page'
                        if ($currentPostType === 'page' && $post) {
                            $currentPageId = (string) $post->id;
                            $rulePageId = (string) $rule->value;

                            if ($rule->operator === '==') {
                                $match = ($rulePageId === $currentPageId);
                            } elseif ($rule->operator === '!=') {
                                $match = ($rulePageId !== $currentPageId);
                            }
                        } else {
                            // Not a page, so this rule doesn't apply
                            $match = false;
                        }
                    }
                    // USER ROLE CHECK
                    elseif ($rule->param === 'current_user_role') {
                        $currentUser = auth()->user();
                        $currentRole = $currentUser ? ($currentUser->role ?? 'subscriber') : 'subscriber';

                        if ($rule->operator === '==') {
                            $match = ($rule->value === $currentRole);
                        } elseif ($rule->operator === '!=') {
                            $match = ($rule->value !== $currentRole);
                        }
                    }
                    // DEFAULT (Unknown rule param)
                    else {
                        $match = false;
                    }

                    if (!$match) {
                        $groupMatch = false;
                        break; // Fail this AND group
                    }
                }

                if ($groupMatch) {
                    return true; // Use this field group
                }
            }

            return false;
        });

        foreach ($matchedGroups as $group) {
            add_meta_box(
                'acf_group_' . $group->id,
                $group->title,
                function ($post) use ($group) {
                    echo plugin_view('acf::render', ['post' => $post, 'groups' => [$group]]);
                },
                $currentPostType,
                'main', // Default to main since 'position' column doesn't exist for context
                $group->menu_order ?? 10
            );
        }
    }

    // 3. Register Meta Boxes for Custom Taxonomies (Side)
    if ($currentPostType && \Illuminate\Support\Facades\Schema::hasTable('custom_taxonomies')) {
        $allTaxonomies = \Plugins\ACF\src\Models\CustomTaxonomy::where('active', 1)->get();

        $matchedTaxonomies = $allTaxonomies->filter(function ($tax) use ($currentPostType) {
            $types = $tax->post_types;
            if (is_string($types)) {
                $types = json_decode($types, true) ?? [];
            }
            return is_array($types) && in_array($currentPostType, $types);
        });

        foreach ($matchedTaxonomies as $tax) {
            add_meta_box(
                'taxonomy_' . $tax->key,
                $tax->plural_label,
                function ($p) use ($tax) { // Rename arg to $p to avoid closure var conflict
                    echo plugin_view('acf::render_taxonomy_box', ['taxonomy' => $tax, 'post' => $p]);
                },
                $currentPostType,
                'side',
                10
            );
        }
    }
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

// 4. Hook into Save Post (Save Taxonomies)
add_action('save_post', function ($postId, $request) {
    if (!$request->has('tax_input')) {
        return;
    }

    $taxInput = $request->input('tax_input');
    if (is_array($taxInput)) {
        $post = \App\Models\Post::find($postId);
        if (!$post)
            return;

        $currentTags = $post->allTags;
        $currentTagIds = $currentTags->pluck('id')->toArray();

        // Find IDs to REMOVE (tags belonging to the taxonomies we are updating)
        $taxonomiesToUpdate = array_keys($taxInput);

        // Get IDs of tags that belong to these taxonomies
        $idsToRemove = \App\Models\Tag::whereIn('taxonomy', $taxonomiesToUpdate)
            ->whereIn('id', $currentTagIds) // Only ones currently attached
            ->pluck('id')
            ->toArray();

        // Remove them from the "Keep" list
        $idsToKeep = array_diff($currentTagIds, $idsToRemove);

        $newIds = [];

        foreach ($taxInput as $taxKey => $data) {
            $taxonomy = \Plugins\ACF\src\Models\CustomTaxonomy::where('key', $taxKey)->first();
            if (!$taxonomy)
                continue;

            if ($taxonomy->hierarchical) {
                // $data is array of IDs
                if (is_array($data)) {
                    foreach ($data as $id) {
                        $newIds[] = (int) $id;
                    }
                }
            } else {
                // $data can be array of names OR comma separated string
                $names = [];

                if (is_array($data)) {
                    $names = $data;
                } elseif (is_string($data)) {
                    $names = explode(',', $data);
                }

                foreach ($names as $name) {
                    $name = trim($name);
                    if (empty($name))
                        continue;

                    $tag = \App\Models\Tag::firstOrCreate(
                        ['slug' => \Illuminate\Support\Str::slug($name), 'taxonomy' => $taxKey],
                        ['name' => $name]
                    );
                    $newIds[] = $tag->id;
                }
            }
        }

        // Final Sync List
        $finalSync = array_merge($idsToKeep, $newIds);
        $post->tags()->sync($finalSync);
    }
});


// 4. Admin Routes (Seo-Pattern)
use Plugins\ACF\src\Http\Controllers\ACFController;
use Plugins\ACF\src\Http\Controllers\PostTypeController;
use Plugins\ACF\src\Http\Controllers\TaxonomyController;

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

// Taxonomies Routes (acf-taxonomies)
add_plugin_admin_route('acf/taxonomies', [TaxonomyController::class, 'index'], 'admin.acf.taxonomies.index');
add_plugin_admin_route('acf/taxonomies/create', [TaxonomyController::class, 'create'], 'admin.acf.taxonomies.create');
add_plugin_admin_route('acf/taxonomies', [TaxonomyController::class, 'store'], 'admin.acf.taxonomies.store', ['POST']);
add_plugin_admin_route('acf/taxonomies/{id}/edit', [TaxonomyController::class, 'edit'], 'admin.acf.taxonomies.edit');
add_plugin_admin_route('acf/taxonomies/{id}', [TaxonomyController::class, 'update'], 'admin.acf.taxonomies.update', ['PUT']);
add_plugin_admin_route('acf/taxonomies/{id}', [TaxonomyController::class, 'destroy'], 'admin.acf.taxonomies.destroy', ['DELETE']);
add_plugin_admin_route('acf/taxonomies/action/bulk', [TaxonomyController::class, 'bulkDestroy'], 'admin.acf.taxonomies.bulk', ['POST']);
add_plugin_admin_route('acf/taxonomies/{id}/toggle', [TaxonomyController::class, 'toggleStatus'], 'admin.acf.taxonomies.toggle', ['POST']);
