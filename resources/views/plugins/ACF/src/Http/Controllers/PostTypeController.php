<?php

namespace Plugins\ACF\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomPostType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Plugins\ACF\src\Models\CustomTaxonomy;

class PostTypeController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('s');

        $query = CustomPostType::query();

        if ($search) {
            $query->where('plural_label', 'like', "%{$search}%")
                ->orWhere('key', 'like', "%{$search}%");
        }

        // Calculate counts
        $counts = [
            'all' => CustomPostType::count(),
            'publish' => CustomPostType::where('active', 1)->count(),
            'inactive' => CustomPostType::where('active', 0)->count(),
        ];

        // Apply Status Filter
        if ($status === 'publish') {
            $query->where('active', 1);
        } elseif ($status === 'inactive') {
            $query->where('active', 0);
        }

        $postTypes = $query->orderBy('plural_label')->paginate(20);

        return plugin_view('acf::post-types.index', compact('postTypes', 'counts', 'status', 'search'));
    }

    public function create()
    {
        $supports = CustomPostType::getAllSupports();
        $taxonomies = CustomTaxonomy::where('active', 1)->get();

        return plugin_view('acf::post-types.create', compact('supports', 'taxonomies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plural_label' => 'required|string|max:255',
            'singular_label' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:custom_post_types,key|regex:/^[a-z_]+$/',
            'supports' => 'array',
            'settings' => 'array',
            'taxonomies' => 'array'
        ]);

        $validated['supports'] = $request->input('supports', CustomPostType::getDefaultSupports());
        $validated['settings'] = $request->input('settings', []);

        // Auto-assign supports based on Capability Type
        $capabilityType = $validated['settings']['capability_type'] ?? 'post';

        if ($capabilityType === 'post') {
            if (!in_array('post_formats', $validated['supports'])) {
                $validated['supports'][] = 'post_formats';
            }
        } elseif ($capabilityType === 'page') {
            if (!in_array('page_attributes', $validated['supports'])) {
                $validated['supports'][] = 'page_attributes';
            }
        }

        // Generate Default Labels
        $labels = $request->input('labels', []);
        $validated['labels'] = $this->generateDefaultLabels($labels, $validated['singular_label'], $validated['plural_label']);

        $postType = CustomPostType::create($validated);

        // Handle Taxonomies
        $selectedTaxonomies = $request->input('taxonomies', []); // Array of Taxonomy IDs or Keys? Let's assume keys for consistency or IDs. 
        // View will likely send IDs or Keys. Let's use IDs for query but we need Keys for storage.
        // Actually, CustomTaxonomy stores `post_types` as array of keys (e.g. ['movie', 'book']).
        // So we need to update the CustomTaxonomy records.

        $this->syncTaxonomies($postType->key, $selectedTaxonomies);

        return redirect()->route('admin.acf.post-types.edit', $postType->id)->with('success', 'Post Type created successfully.');
    }

    public function edit($id)
    {
        $postType = CustomPostType::findOrFail($id);
        $supports = CustomPostType::getAllSupports();
        $taxonomies = CustomTaxonomy::where('active', 1)->get();

        return plugin_view('acf::post-types.edit', compact('postType', 'supports', 'taxonomies'));
    }

    public function update(Request $request, $id)
    {
        $postType = CustomPostType::findOrFail($id);

        $validated = $request->validate([
            'plural_label' => 'required|string|max:255',
            'singular_label' => 'required|string|max:255',
            'key' => 'required|string|max:20|regex:/^[a-z_]+$/|unique:custom_post_types,key,' . $id,
            'supports' => 'array',
            'settings' => 'array',
            'taxonomies' => 'array'
        ]);

        $validated['supports'] = $request->input('supports', []);
        $validated['settings'] = $request->input('settings', []);

        // Auto-assign supports based on Capability Type
        $capabilityType = $validated['settings']['capability_type'] ?? 'post';

        if ($capabilityType === 'post') {
            if (!in_array('post_formats', $validated['supports'])) {
                $validated['supports'][] = 'post_formats';
            }
        } elseif ($capabilityType === 'page') {
            if (!in_array('page_attributes', $validated['supports'])) {
                $validated['supports'][] = 'page_attributes';
            }
        }

        // Generate Default Labels
        $labels = $request->input('labels', []);
        $validated['labels'] = $this->generateDefaultLabels($labels, $validated['singular_label'], $validated['plural_label']);

        $oldKey = $postType->key;
        $postType->update($validated);

        // If key changed, we might need to update references in taxonomies... 
        // For now let's assume key doesn't change often or handle it if it does.
        // But `syncTaxonomies` uses the current key.
        // If key changed, we should probably clean up old key? 
        // Complex. Let's stick to syncing the CURRENT key for now.

        $selectedTaxonomies = $request->input('taxonomies', []);
        $this->syncTaxonomies($postType->key, $selectedTaxonomies, $oldKey !== $postType->key ? $oldKey : null);

        return back()->with('success', 'Post Type updated successfully.');
    }

    private function syncTaxonomies($postTypeKey, $selectedTaxonomyIds, $oldKey = null)
    {
        $allTaxonomies = CustomTaxonomy::all();

        foreach ($allTaxonomies as $taxonomy) {
            $postTypes = $taxonomy->post_types ?? [];
            $shouldHave = in_array($taxonomy->id, $selectedTaxonomyIds);

            // Remove old key if it exists and key changed
            if ($oldKey && in_array($oldKey, $postTypes)) {
                $postTypes = array_diff($postTypes, [$oldKey]);
            }

            if ($shouldHave) {
                if (!in_array($postTypeKey, $postTypes)) {
                    $postTypes[] = $postTypeKey;
                }
            } else {
                if (in_array($postTypeKey, $postTypes)) {
                    $postTypes = array_diff($postTypes, [$postTypeKey]);
                }
            }

            // Clean up indexes
            $postTypes = array_values($postTypes);

            if ($postTypes !== ($taxonomy->post_types ?? [])) {
                $taxonomy->update(['post_types' => $postTypes]);
            }
        }
    }

    /**
     * Helper to generate default labels if missing
     */
    private function generateDefaultLabels(array $inputLabels, string $singular, string $plural)
    {
        $defaults = [
            'menu_name' => $plural,   // Default to Plural Label
            'all_items' => "All $plural",
            'add_new' => 'Add New', // Usually just "Add New"
            'add_new_item' => "Add New $singular",
            'edit_item' => "Edit $singular",
            'new_item' => "New $singular",
            'view_item' => "View $singular",
            'search_items' => "Search $plural",
            'not_found' => "No $singular found",
            'not_found_in_trash' => "No $singular found in Trash",
            'name' => $plural,        // Ensure these exist as fallbacks
            'singular_name' => $singular
        ];

        foreach ($defaults as $key => $default) {
            // If key is missing or empty, use default
            if (empty($inputLabels[$key])) {
                $inputLabels[$key] = $default;
            }
        }

        return $inputLabels;
    }

    public function destroy($id)
    {
        $postType = CustomPostType::findOrFail($id);

        // Delete all posts associated with this post type
        \App\Models\Post::where('type', $postType->key)->delete();

        $postType->delete();

        return redirect()->route('admin.acf.post-types.index')->with('success', 'Post Type and associated posts deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $postType = CustomPostType::findOrFail($id);
        $postType->update(['active' => !$postType->active]);

        return back()->with('success', 'Post Type status updated.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'action' => 'required|in:delete,activate,deactivate',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        if ($action === 'delete') {
            // Get keys of types being deleted
            $keys = CustomPostType::whereIn('id', $ids)->pluck('key');

            // Delete associated posts
            if ($keys->isNotEmpty()) {
                \App\Models\Post::whereIn('type', $keys)->delete();
            }

            CustomPostType::destroy($ids);
            return redirect()->route('admin.acf.post-types.index')->with('success', 'Selected Post Types and associated posts deleted.');
        } elseif ($action === 'activate') {
            CustomPostType::whereIn('id', $ids)->update(['active' => 1]);
            return redirect()->route('admin.acf.post-types.index')->with('success', 'Selected Post Types activated.');
        } elseif ($action === 'deactivate') {
            CustomPostType::whereIn('id', $ids)->update(['active' => 0]);
            return redirect()->route('admin.acf.post-types.index')->with('success', 'Selected Post Types deactivated.');
        }

        return redirect()->route('admin.acf.post-types.index')->with('error', 'Invalid action.');
    }
}
