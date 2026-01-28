<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $taxonomy = $request->get('taxonomy', 'post_tag');

        $query = Tag::where('taxonomy', $taxonomy)->withCount('posts')->latest();

        if ($request->has('s')) {
            $search = $request->get('s');
            $query->where('name', 'like', "%{$search}%");
        } else {
            $search = null;
        }

        $tags = $query->paginate(20)->withQueryString();

        $counts = [
            'all' => Tag::where('taxonomy', $taxonomy)->count()
        ];

        // Pass status='all' to satisfy component if needed, though it defaults. 
        // We can use $taxonomy as a 'type' if we wanted tabs like 'Category | Tag', but user visited specific route.
        // For now, simple All count.

        return view('admin.tags.index', compact('tags', 'taxonomy', 'search', 'counts'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $slug = $request->slug ? Str::slug($request->slug) : Str::slug($request->name);
        $taxonomy = $request->get('taxonomy', 'post_tag');

        // Ensure unique slug within taxonomy? Or global? Ideally global to avoid URL conflicts if using /tag/{slug} vs /category/{slug}
        // WP does global slug uniqueness generally.
        $count = Tag::where('slug', $slug)->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $tag = Tag::create([
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'taxonomy' => $taxonomy
        ]);

        if ($request->expectsJson()) {
            return response()->json($tag);
        }

        return back()->with('success', ucfirst($taxonomy === 'category' ? 'Category' : 'Tag') . ' created.');
    }

    public function edit(Tag $tag)
    {
        return view('admin.tags.edit', compact('tag'));
    }

    public function update(Request $request, Tag $tag)
    {
        $request->validate(['name' => 'required|string|max:255']);

        $data = ['name' => $request->name, 'description' => $request->description];

        if ($request->slug) {
            $data['slug'] = Str::slug($request->slug);
        }

        $tag->update($data);

        // If coming from the separate edit page, redirect back to index
        if ($request->has('from_edit_page')) {
            return redirect()->route('admin.tags.index')->with('success', 'Tag updated.');
        }

        return back()->with('success', 'Tag updated.');
    }

    public function destroy(Tag $tag)
    {
        $tag->delete();
        return back()->with('success', 'Tag deleted.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required',
            'ids' => 'required|array',
            'ids.*' => 'exists:tags,id',
        ]);

        if ($request->action === 'delete') {
            Tag::whereIn('id', $request->ids)->delete();
        }

        return back()->with('success', 'Bulk action applied.');
    }
}
