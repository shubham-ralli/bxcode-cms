<?php

namespace Plugins\ACF\src\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomPostType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostTypeController extends Controller
{
    public function index()
    {
        $postTypes = CustomPostType::orderBy('plural_label')->paginate(20);
        $counts = [];
        $status = null;
        $search = null;

        return plugin_view('acf::post-types.index', compact('postTypes', 'counts', 'status', 'search'));
    }

    public function create()
    {
        $supports = CustomPostType::getAllSupports();

        return plugin_view('acf::post-types.create', compact('supports'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plural_label' => 'required|string|max:255',
            'singular_label' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:custom_post_types,key|regex:/^[a-z_]+$/',
            'supports' => 'array',
            'settings' => 'array'
        ]);

        $validated['supports'] = $request->input('supports', CustomPostType::getDefaultSupports());
        $validated['settings'] = $request->input('settings', []);

        // Generate Default Labels
        $labels = $request->input('labels', []);
        $validated['labels'] = $this->generateDefaultLabels($labels, $validated['singular_label'], $validated['plural_label']);

        $postType = CustomPostType::create($validated);

        return redirect()->route('admin.post-types.edit', $postType->id)->with('success', 'Post Type created successfully.');
    }

    public function edit($id)
    {
        $postType = CustomPostType::findOrFail($id);
        $supports = CustomPostType::getAllSupports();

        return plugin_view('acf::post-types.edit', compact('postType', 'supports'));
    }

    public function update(Request $request, $id)
    {
        $postType = CustomPostType::findOrFail($id);

        $validated = $request->validate([
            'plural_label' => 'required|string|max:255',
            'singular_label' => 'required|string|max:255',
            'key' => 'required|string|max:20|regex:/^[a-z_]+$/|unique:custom_post_types,key,' . $id,
            'supports' => 'array',
            'settings' => 'array'
        ]);

        $validated['supports'] = $request->input('supports', []);
        $validated['settings'] = $request->input('settings', []);

        // Generate Default Labels
        $labels = $request->input('labels', []);
        $validated['labels'] = $this->generateDefaultLabels($labels, $validated['singular_label'], $validated['plural_label']);

        $postType->update($validated);

        return back()->with('success', 'Post Type updated successfully.');
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

        return redirect()->route('admin.post-types.index')->with('success', 'Post Type and associated posts deleted successfully.');
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
            'action' => 'required|in:delete',
        ]);

        if ($request->input('action') === 'delete') {
            $ids = $request->input('ids');

            // Get keys of types being deleted
            $keys = CustomPostType::whereIn('id', $ids)->pluck('key');

            // Delete associated posts
            if ($keys->isNotEmpty()) {
                \App\Models\Post::whereIn('type', $keys)->delete();
            }

            CustomPostType::destroy($ids);
            return redirect()->route('admin.post-types.index')->with('success', 'Selected Post Types and associated posts deleted.');
        }

        return redirect()->route('admin.post-types.index')->with('error', 'Invalid action.');
    }
}
