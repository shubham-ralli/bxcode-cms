<?php

namespace Plugins\ACF\src\Http\Controllers;

use App\Http\Controllers\Controller;
use Plugins\ACF\src\Models\CustomTaxonomy;
use Plugins\ACF\src\Models\CustomPostType; // Assuming this exists for selecting post types
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TaxonomyController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('s');

        $query = CustomTaxonomy::query();

        if ($search) {
            $query->where('plural_label', 'like', "%{$search}%")
                ->orWhere('key', 'like', "%{$search}%");
        }

        // Calculate counts
        $counts = [
            'all' => CustomTaxonomy::count(),
            'publish' => CustomTaxonomy::where('active', 1)->count(),
            'inactive' => CustomTaxonomy::where('active', 0)->count(),
        ];

        // Apply Status Filter
        if ($status === 'publish') {
            $query->where('active', 1);
        } elseif ($status === 'inactive') {
            $query->where('active', 0);
        }

        $taxonomies = $query->orderBy('plural_label')->paginate(20);

        return plugin_view('acf::taxonomies.index', compact('taxonomies', 'counts', 'status', 'search'));
    }

    public function create()
    {
        // Get all active post types to attach to
        $postTypes = \App\Models\CustomPostType::where('active', 1)->get(); // Use full namespace or import if different

        return plugin_view('acf::taxonomies.create', compact('postTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plural_label' => 'required|string|max:255',
            'singular_label' => 'required|string|max:255',
            'key' => 'required|string|max:20|unique:custom_taxonomies,key|regex:/^[a-z_]+$/',
            'hierarchical' => 'required|boolean',
            'post_types' => 'array',
            'publicly_queryable' => 'boolean'
        ]);

        $validated['post_types'] = $request->input('post_types', []);
        $validated['publicly_queryable'] = $request->has('publicly_queryable') ? (bool) $request->input('publicly_queryable') : true;

        $taxonomy = CustomTaxonomy::create($validated);

        return redirect()->route('admin.acf.taxonomies.edit', $taxonomy->id)->with('success', 'Taxonomy created successfully.');
    }

    public function edit($id)
    {
        $taxonomy = CustomTaxonomy::findOrFail($id);
        $postTypes = \App\Models\CustomPostType::where('active', 1)->get();

        return plugin_view('acf::taxonomies.edit', compact('taxonomy', 'postTypes'));
    }

    public function update(Request $request, $id)
    {
        $taxonomy = CustomTaxonomy::findOrFail($id);

        $validated = $request->validate([
            'plural_label' => 'required|string|max:255',
            'singular_label' => 'required|string|max:255',
            'key' => 'required|string|max:20|regex:/^[a-z_]+$/|unique:custom_taxonomies,key,' . $id,
            'hierarchical' => 'required|boolean',
            'post_types' => 'array',
            'publicly_queryable' => 'boolean'
        ]);

        $validated['post_types'] = $request->input('post_types', []);
        $validated['publicly_queryable'] = $request->has('publicly_queryable') ? (bool) $request->input('publicly_queryable') : true;

        $taxonomy->update($validated);

        return back()->with('success', 'Taxonomy updated successfully.');
    }

    public function destroy($id)
    {
        $taxonomy = CustomTaxonomy::findOrFail($id);
        $taxonomy->delete();

        return redirect()->route('admin.acf.taxonomies.index')->with('success', 'Taxonomy deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $taxonomy = CustomTaxonomy::findOrFail($id);
        $taxonomy->update(['active' => !$taxonomy->active]);

        return back()->with('success', 'Taxonomy status updated.');
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
            CustomTaxonomy::destroy($ids);
            return redirect()->route('admin.acf.taxonomies.index')->with('success', 'Selected Taxonomies deleted.');
        } elseif ($action === 'activate') {
            CustomTaxonomy::whereIn('id', $ids)->update(['active' => 1]);
            return redirect()->route('admin.acf.taxonomies.index')->with('success', 'Selected Taxonomies activated.');
        } elseif ($action === 'deactivate') {
            CustomTaxonomy::whereIn('id', $ids)->update(['active' => 0]);
            return redirect()->route('admin.acf.taxonomies.index')->with('success', 'Selected Taxonomies deactivated.');
        }

        return redirect()->route('admin.acf.taxonomies.index')->with('error', 'Invalid action.');
    }
}
