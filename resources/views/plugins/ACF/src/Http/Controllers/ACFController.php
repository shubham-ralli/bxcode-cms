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
            ['group' => new FieldGroup()],
            $this->getRuleOptions()
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
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

        // Validate Unique Field Names
        $fields = $request->input('fields', []);
        if (is_array($fields)) {
            $names = array_column($fields, 'name');
            $counts = array_count_values(array_map('strtolower', $names)); // Case-insensitive check
            foreach ($counts as $name => $count) {
                if ($count > 1) {
                    return back()->withInput()->with('error', "Field name '$name' is used multiple times. Field names must be unique.");
                }
            }
        }

        // Sync Fields (Basic implementation first)
        if ($request->has('fields')) {
            $this->syncFields($group, $request->input('fields'));
        }

        return redirect()->route('admin.acf.index')->with('success', 'Field Group created.');
    }

    public function edit($id)
    {
        $group = FieldGroup::with(['fields', 'locationRules'])->findOrFail($id); // Eager load rules
        return plugin_view('acf::edit', array_merge(
            compact('group'),
            $this->getRuleOptions($group)
        ));
    }

    public function update(Request $request, $id)
    {
        $group = FieldGroup::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
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

        // Validate Unique Field Names
        $fields = $request->input('fields', []);
        if (is_array($fields)) {
            $names = array_column($fields, 'name');
            $counts = array_count_values(array_map('strtolower', $names)); // Case-insensitive check
            foreach ($counts as $name => $count) {
                if ($count > 1) {
                    return back()->withInput()->with('error', "Field name '$name' is used multiple times. Field names must be unique.");
                }
            }
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

        // Cascading Delete Values
        $fieldNames = $group->fields->pluck('name')->toArray();
        if (!empty($fieldNames)) {
            AcfValue::whereIn('field_name', $fieldNames)->delete();
        }

        $group->delete(); // Fields cascade via DB foreign key or model event if configured, relying on DB cascade here mostly.
        return redirect()->route('admin.acf.index')->with('success', 'Field Group and associated data deleted.');
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
        // Soft implementation: Delete all and recreate (not efficient but easiest for MVP)
        // Better: Update existing, delete missing.

        // For MVP: Let's assume fieldsData is an array of field objects
        // Iterate and update/create. 

        // Collect current IDs to handle deletions
        $currentIds = $group->fields->pluck('id')->toArray();
        $processedIds = [];

        if (is_array($fieldsData)) {
            foreach ($fieldsData as $index => $fieldData) {
                if (isset($fieldData['id']) && $fieldData['id']) {
                    // Update
                    $field = Field::find($fieldData['id']);
                    if ($field && $field->group_id == $group->id) {
                        $field->update([
                            'label' => $fieldData['label'],
                            'name' => $fieldData['name'],
                            'type' => $fieldData['type'],
                            'menu_order' => $index,
                            'default_value' => $fieldData['default_value'] ?? null,
                            'placeholder' => $fieldData['placeholder'] ?? null,
                            'options' => $fieldData['options'] ?? null
                        ]);
                        $processedIds[] = $field->id;
                    }
                } else {
                    // Create
                    $newField = $group->fields()->create([
                        'label' => $fieldData['label'],
                        'name' => $fieldData['name'],
                        'type' => $fieldData['type'],
                        'menu_order' => $index,
                        'default_value' => $fieldData['default_value'] ?? null,
                        'placeholder' => $fieldData['placeholder'] ?? null,
                        'options' => $fieldData['options'] ?? null
                    ]);
                    $processedIds[] = $newField->id;
                }
            }
        }

        // Delete removed
        $toDelete = array_diff($currentIds, $processedIds);
        Field::destroy($toDelete);
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
