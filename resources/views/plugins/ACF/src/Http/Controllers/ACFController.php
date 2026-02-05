<?php

namespace Plugins\ACF\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Manual require for models due to autoloading issues
require_once __DIR__ . '/../../Models/FieldGroup.php';
require_once __DIR__ . '/../../Models/FieldGroupRule.php';
require_once __DIR__ . '/../../Models/Field.php';
require_once __DIR__ . '/../../Models/AcfValue.php';

use Plugins\ACF\src\Models\FieldGroup;
use Plugins\ACF\src\Models\FieldGroupRule;
use Plugins\ACF\src\Models\Field;
use Plugins\ACF\src\Models\AcfValue;


class ACFController extends Controller
{
    private function getRuleOptions($group = null)
    {
        // 1. Post Types
        $postTypes = [
            'post' => 'Post',
            'page' => 'Page',
        ];

        // Add custom post types dynamically from DB
        try {
            $dbTypes = \App\Models\Post::distinct()->pluck('type')->toArray();
            foreach ($dbTypes as $type) {
                if (!empty($type)) {
                    $key = strtolower($type);
                    if (!isset($postTypes[$key])) {
                        $postTypes[$key] = ucfirst($type);
                    }
                }
            }
        } catch (\Exception $e) {
            // Fallback if table doesn't exist yet
        }

        // 2. Page Templates
        $templates = ['default' => 'Default Template'];
        $themePath = resource_path('views/themes/bxcode-theme'); // hardcoded active theme for now
        if (is_dir($themePath)) {
            $files = glob($themePath . '/*.blade.php');
            foreach ($files as $file) {
                $content = file_get_contents($file);
                if (preg_match('/Template Name:\s*(.*?)(?:\s*--}}|\s*\*\/|$)/mi', $content, $matches)) {
                    $basename = basename($file, '.blade.php');
                    if ($basename !== 'index') {
                        $templates[$basename] = trim($matches[1]);
                    }
                }
            }
        }

        // 3. User Roles
        $roles = [
            'administrator' => 'Administrator',
            'editor' => 'Editor',
            'author' => 'Author',
            'subscriber' => 'Subscriber'
        ];

        // 4. Pages (for ID selection)
        $query = \App\Models\Post::where('type', 'page')->select('id', 'title');

        // Include currently selected pages if any
        if ($group) {
            $selectedIds = $group->locationRules()->where('param', 'page')->pluck('value')->toArray();
            if (!empty($selectedIds)) {
                $query->orWhereIn('id', $selectedIds);
            }
        }

        $pages = $query->limit(100)->get();

        return compact('postTypes', 'templates', 'roles', 'pages');
    }

    public function index(Request $request)
    {
        $search = $request->input('s');
        $status = $request->input('status', 'all');

        $query = FieldGroup::orderBy('title');

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status === 'active') {
            $query->where('active', true);
        } elseif ($status === 'inactive') {
            $query->where('active', false);
        }

        $groups = $query->paginate(10)->withQueryString();

        // Calculate Counts
        $counts = [
            'all' => FieldGroup::count(),
            'active' => FieldGroup::where('active', true)->count(),
            'inactive' => FieldGroup::where('active', false)->count(),
        ];

        return plugin_view('acf::index', compact('groups', 'search', 'counts', 'status'));
    }

    public function create()
    {
        return plugin_view('acf::edit', array_merge(
            [
                'group' => new FieldGroup(),
                'fieldsTree' => []
            ],
            $this->getRuleOptions()
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'active' => 'boolean',
            'rules' => 'nullable', // Allow array or json
        ]);

        $rulesData = [];
        if (isset($data['rules']) && is_string($data['rules'])) {
            $decoded = json_decode($data['rules'], true);
            if (is_array($decoded)) {
                $rulesData = $decoded;
            }
        }

        // Remove rules from data (don't save to JSON column)
        unset($data['rules']);

        $group = FieldGroup::create($data);

        // Sync Rules
        $this->syncRules($group, $rulesData);


        // Auto-number field names to ensure global uniqueness
        $fields = $request->input('fields', []);
        if (is_array($fields)) {
            $fields = $this->ensureUniqueFieldNames($fields, $group->id);
            // Update the request with renamed fields
            $request->merge(['fields' => $fields]);
        }


        // Sync Fields (Basic implementation first)
        if ($request->has('fields')) {
            $this->syncFields($group, $request->input('fields'));
        }

        return redirect()->route('admin.acf.edit', $group->id)->with('success', 'Field Group created.');
    }

    public function edit($id)
    {
        $group = FieldGroup::with(['fields', 'locationRules'])->findOrFail($id); // Eager load rules

        // Build Fields Tree for Editor
        $fieldsTree = $this->buildFieldsTree($group->fields);

        return plugin_view('acf::edit', array_merge(
            compact('group', 'fieldsTree'),
            $this->getRuleOptions($group)
        ));
    }

    private function buildFieldsTree($fields)
    {
        // 1. Convert to Array and Key by ID
        $keyed = [];
        foreach ($fields->sortBy('menu_order') as $field) {
            $keyed[$field->id] = $field->toArray();
            $keyed[$field->id]['sub_fields'] = []; // Initialize empty sub_fields
        }

        $tree = [];

        // 2. Build Tree Structure
        foreach ($keyed as $id => &$field) {
            if ($field['parent_id'] && isset($keyed[$field['parent_id']])) {
                // Add reference to parent's sub_fields
                $keyed[$field['parent_id']]['sub_fields'][] = &$field;
            } else {
                // Root element (or orphan)
                $tree[] = &$field;
            }
        }
        unset($field); // Break reference

        // 3. Clean and Return
        // Break references and ensure clean array
        $json = json_encode(array_values($tree));
        if (json_last_error() !== JSON_ERROR_NONE) {
            \Illuminate\Support\Facades\Log::error('ACF Tree JSON Error: ' . json_last_error_msg());
            return []; // Fail safe
        }
        return json_decode($json, true);
    }

    public function update(Request $request, $id)
    {
        $group = FieldGroup::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'active' => 'boolean',
            'rules' => 'nullable',
        ]);

        \Illuminate\Support\Facades\Log::info('Update Rules Raw', ['rules' => $request->input('rules'), 'type' => gettype($request->input('rules'))]);

        $rulesData = [];
        if (isset($data['rules'])) {
            if (is_string($data['rules'])) {
                $decoded = json_decode($data['rules'], true);
                if (is_array($decoded))
                    $rulesData = $decoded;
            } elseif (is_array($data['rules'])) {
                $rulesData = $data['rules'];
            }
        }

        // Remove rules from data
        unset($data['rules']);

        $group->update($data);

        // Sync Rules
        $this->syncRules($group, $rulesData);


        // Auto-number field names to ensure global uniqueness
        $fields = $request->input('fields', []);
        if (is_array($fields)) {
            $fields = $this->ensureUniqueFieldNames($fields, $id);
            // Update the request with renamed fields
            $request->merge(['fields' => $fields]);
        }


        // Sync Fields
        if ($request->has('fields')) {
            $this->syncFields($group, $request->input('fields'));
        }

        return redirect()->route('admin.acf.edit', $id)->with('success', 'Field Group updated.');
    }

    public function destroy($id)
    {
        $group = FieldGroup::with('fields')->findOrFail($id);

        // Recursively collect ALL field names (including nested sub-fields)
        $allFieldNames = $this->getAllFieldNames($group->fields);

        // Delete all associated values
        if (!empty($allFieldNames)) {
            AcfValue::whereIn('field_name', $allFieldNames)->delete();
        }

        $group->delete(); // Fields cascade via DB foreign key
        return redirect()->route('admin.acf.index')->with('success', 'Field Group and associated data deleted.');
    }

    private function getAllFieldNames($fields)
    {
        $names = [];
        foreach ($fields as $field) {
            $names[] = $field->name;
            // Recursively get children
            if ($field->children && $field->children->count() > 0) {
                $names = array_merge($names, $this->getAllFieldNames($field->children));
            }
        }
        return $names;
    }

    private function ensureUniqueFieldNames($fieldsArray, $currentGroupId)
    {
        $usedNames = []; // Track names used in this submission

        // Recursively ensure all field names are globally unique
        foreach ($fieldsArray as &$field) {
            if (!empty($field['name'])) {
                $originalName = $field['name'];
                $baseName = $originalName;
                $counter = 1;

                // Check for conflicts:
                // 1. Within current submission (usedNames)
                // 2. In database (all groups including current)
                while (
                    in_array($field['name'], $usedNames) ||
                    $this->fieldNameExists($field['name'], $currentGroupId, $field['id'] ?? null)
                ) {
                    $field['name'] = $baseName . '_' . $counter;
                    $counter++;
                }

                // Add to used names
                $usedNames[] = $field['name'];
            }

            // Recursively process sub-fields
            if (isset($field['sub_fields']) && is_array($field['sub_fields'])) {
                $field['sub_fields'] = $this->ensureUniqueFieldNamesRecursive($field['sub_fields'], $currentGroupId, $usedNames);
            }
        }

        return $fieldsArray;
    }

    private function ensureUniqueFieldNamesRecursive($fieldsArray, $currentGroupId, &$usedNames)
    {
        foreach ($fieldsArray as &$field) {
            if (!empty($field['name'])) {
                $originalName = $field['name'];
                $baseName = $originalName;
                $counter = 1;

                while (
                    in_array($field['name'], $usedNames) ||
                    $this->fieldNameExists($field['name'], $currentGroupId, $field['id'] ?? null)
                ) {
                    $field['name'] = $baseName . '_' . $counter;
                    $counter++;
                }

                $usedNames[] = $field['name'];
            }

            if (isset($field['sub_fields']) && is_array($field['sub_fields'])) {
                $field['sub_fields'] = $this->ensureUniqueFieldNamesRecursive($field['sub_fields'], $currentGroupId, $usedNames);
            }
        }

        return $fieldsArray;
    }

    private function fieldNameExists($name, $currentGroupId, $currentFieldId = null)
    {
        // Check ALL existing fields (both same group and other groups)
        $query = Field::where('name', $name);

        if ($currentFieldId) {
            // Exclude the current field being edited
            $query->where('id', '!=', $currentFieldId);
        }

        return $query->exists();
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:delete',
        ]);

        if ($request->input('action') === 'delete') {
            $ids = $request->input('ids');

            // Clean up values
            // 1. Get all fields for these groups
            $fieldNames = Field::whereIn('group_id', $ids)->pluck('name')->toArray();
            if (!empty($fieldNames)) {
                AcfValue::whereIn('field_name', $fieldNames)->delete();
            }

            FieldGroup::destroy($ids);
            return redirect()->route('admin.acf.index')->with('success', 'Selected Field Groups and associated data deleted.');
        }

        return redirect()->route('admin.acf.index')->with('error', 'Invalid action.');
    }

    public function toggleStatus($id)
    {
        $group = FieldGroup::findOrFail($id);
        $group->update(['active' => !$group->active]);

        return back()->with('success', 'Field Group status updated.');
    }

    private function syncFields($group, $fieldsData)
    {
        // 1. Get all current field IDs for this group to handle deletions
        $currentIds = $group->fields()->pluck('id')->toArray();
        $processedIds = [];

        if (is_array($fieldsData)) {
            $processedIds = $this->processFieldsRecursive($group->id, null, $fieldsData);
        }

        // 3. Delete fields that were not processed (removed from UI)
        $toDelete = array_diff($currentIds, $processedIds);
        Field::destroy($toDelete);
    }

    /**
     * Recursive function to save fields and their children
     *
     * @param int $groupId
     * @param int|null $parentId
     * @param array $fields
     * @return array Array of processed Field IDs
     */
    private function processFieldsRecursive($groupId, $parentId, $fields)
    {
        $processedIds = [];

        foreach ($fields as $index => $fieldData) {
            // Check if it's an existing field or new
            $fieldId = $fieldData['id'] ?? null;

            $data = [
                'group_id' => $groupId,
                'parent_id' => $parentId,
                'label' => $fieldData['label'],
                'name' => $fieldData['name'],
                'type' => $fieldData['type'],
                'menu_order' => $index,
                'required' => isset($fieldData['required']) ? (bool) $fieldData['required'] : false,
                'default_value' => $fieldData['default_value'] ?? null,
                'placeholder' => $fieldData['placeholder'] ?? null,
                'options' => $fieldData['options'] ?? null,
            ];

            if ($fieldId) {
                $field = Field::find($fieldId);
                if ($field) {
                    $field->update($data);
                } else {
                    $field = Field::create($data);
                }
            } else {
                $field = Field::create($data);
            }

            $processedIds[] = $field->id;

            // Handle Sub Fields (Recursive)
            if (isset($fieldData['sub_fields']) && is_array($fieldData['sub_fields'])) {
                $childIds = $this->processFieldsRecursive($groupId, $field->id, $fieldData['sub_fields']);
                $processedIds = array_merge($processedIds, $childIds);
            }
        }

        return $processedIds;
    }

    private function syncRules($group, $rulesData)
    {
        // Delete existing rules
        $group->locationRules()->delete();

        // Add new rules
        // Structure: [[{param, operator, value}, ... (AND)], ... (OR)]
        if (is_array($rulesData)) {
            foreach ($rulesData as $gIndex => $groupRules) {
                // Ensure groupRules is an array (AND group)
                if (is_array($groupRules)) {
                    foreach ($groupRules as $rule) {
                        if (isset($rule['param'], $rule['operator'], $rule['value'])) {
                            \Illuminate\Support\Facades\Log::info('Saving Rule', [
                                'group_id' => $group->id,
                                'index' => $gIndex,
                                'val' => $rule['value']
                            ]);
                            $group->locationRules()->create([
                                'group_index' => $gIndex,
                                'param' => $rule['param'],
                                'operator' => $rule['operator'],
                                'value' => $rule['value']
                            ]);
                        }
                    }
                }
            }
        }
    }
}
