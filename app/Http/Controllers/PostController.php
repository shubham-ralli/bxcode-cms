<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'post');
        $status = $request->get('status');
        $search = $request->get('s');

        $query = Post::where('type', $type);

        // Status Filter
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        } elseif ($status !== 'trash') {
            // By default, if not explicitly asking for trash or specific status, hide trash
            $query->where('status', '!=', 'trash');
        }

        // Search Filter
        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $posts = $query->latest()->paginate(15)->appends($request->query());

        // Counts for tabs
        $baseQuery = Post::where('type', $type);
        $counts = [
            'all' => (clone $baseQuery)->where('status', '!=', 'trash')->count(),
            'publish' => (clone $baseQuery)->where('status', 'publish')->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'private' => (clone $baseQuery)->where('status', 'private')->count(),
            'trash' => (clone $baseQuery)->where('status', 'trash')->count(),
        ];

        return view('admin.posts.index', compact('posts', 'counts', 'type', 'status', 'search'));
    }

    public function create(Request $request)
    {
        // If creating a page, we might need parent pages for dropdown
        $parents = [];
        if ($request->type === 'page') {
            $parents = Post::where('type', 'page')->where('status', 'publish')->get();
        }

        // Get all users for author selection
        $users = \App\Models\User::orderBy('name')->get();

        // Get all tags
        $tags = \App\Models\Tag::where('taxonomy', 'post_tag')->orderBy('name')->get();
        // Get all categories
        $categories = \App\Models\Tag::where('taxonomy', 'category')->orderBy('name')->get();

        // Get Templates
        $templates = $this->getAvailableTemplates();

        return view('admin.posts.create', compact('parents', 'users', 'tags', 'categories', 'templates'));
    }

    /**
     * Scan active theme for potential templates.
     */
    private function getAvailableTemplates()
    {
        $theme = get_active_theme();
        $themePath = resource_path("views/themes/{$theme}");

        $files = glob("{$themePath}/*.blade.php");
        $templates = ['default' => 'Default Template'];

        foreach ($files as $file) {
            $filename = basename($file, '.blade.php');

            // Ignore partials, and standard theme files that wouldn't be templates
            if (Str::startsWith($filename, ['_', 'header', 'footer', 'sidebar', 'functions', '404'])) {
                continue;
            }

            // If it's page.blade.php, it's the default, so skip (or label explicit)
            if ($filename === 'page')
                continue;

            // Read the first 8kb of the file to check for "Template Name:"
            $content = file_get_contents($file, false, null, 0, 8192);
            if (preg_match('/Template Name:\s*(.*?)(?=\s*(\*\/|\-\-\}))/mi', $content, $matches) || preg_match('/Template Name:\s*(.*)$/mi', $content, $matches)) {
                $templates[$filename] = trim(str_replace(['-->', '}}'], '', $matches[1]));
            }
        }

        return $templates;
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'slug' => 'required|unique:posts,slug',
            'content' => 'nullable',
            'type' => 'required',
            'status' => 'required',
            'template' => 'nullable|string',
            'parent_id' => 'nullable|exists:posts,id',
            'excerpt' => 'nullable',
            'tags' => 'nullable|array',
            'categories' => 'nullable|array',
        ]);

        $post = Post::create([
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('slug')),
            'content' => $request->input('content'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'template' => $request->input('template') ?: 'default',
            'parent_id' => $request->input('parent_id'),
            'author_id' => Auth::id(),
            'featured_image' => $request->input('featured_image') ?? null,
            'excerpt' => $request->input('excerpt'),
        ]);

        $this->syncTagsByName($post, $request->input('tags', []), 'post_tag');
        $this->syncTaxonomyIds($post, $request->input('categories', []), 'category');

        // Save SEO Meta
        if ($request->has('seo')) {
            $post->seo()->create($request->input('seo'));
        }

        // Trigger Save Post Hook (for Plugins like ACF)
        do_action('save_post', $post->id, $request);

        return redirect()->route('admin.posts.edit', ['post' => $post->id, 'action' => 'edit'])->with('success', 'Post created successfully');
    }

    public function edit(Request $request)
    {
        $id = $request->query('post');
        $post = Post::with(['seo', 'author', 'tags', 'categories'])->findOrFail($id);

        $parents = [];
        if ($post->type === 'page') {
            // Get all other pages except self to avoid recursion
            $parents = Post::where('type', 'page')
                ->where('status', 'publish')
                ->where('id', '!=', $id)
                ->get();
        }

        // Get all users for author selection
        $users = \App\Models\User::orderBy('name')->get();

        // Get all tags
        $tags = \App\Models\Tag::where('taxonomy', 'post_tag')->orderBy('name')->get();
        // Get all categories
        $categories = \App\Models\Tag::where('taxonomy', 'category')->orderBy('name')->get();

        // Get Templates
        $templates = $this->getAvailableTemplates();

        return view('admin.posts.edit', compact('post', 'parents', 'users', 'tags', 'categories', 'templates'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required',
            // Allow same slug if it's the current post
            'slug' => 'required|unique:posts,slug,' . $post->id,
            'content' => 'nullable',
            'type' => 'required',
            'status' => 'required',
            'template' => 'nullable|string',
            'parent_id' => 'nullable|exists:posts,id',
            'excerpt' => 'nullable',
            'tags' => 'nullable|array',
            'categories' => 'nullable|array',
        ]);

        $post->update([
            'title' => $request->input('title'),
            'slug' => Str::slug($request->input('slug')),
            'content' => $request->input('content'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'template' => $request->input('template') ?: 'default',
            'parent_id' => $request->input('parent_id'),
            'featured_image' => $request->input('featured_image'),
            'excerpt' => $request->input('excerpt'),
        ]);

        $this->syncTagsByName($post, $request->input('tags', []), 'post_tag');
        $this->syncTaxonomyIds($post, $request->input('categories', []), 'category');

        // Update or Create SEO Meta
        if ($request->has('seo')) {
            $post->seo()->updateOrCreate(
                ['post_id' => $post->id],
                $request->input('seo')
            );
        }

        // Trigger Save Post Hook
        do_action('save_post', $post->id, $request);


        return back()->with('success', ucfirst($post->type) . ' "' . $post->title . '" updated successfully.');
    }

    /**
     * Sync tags by list of Names (create if not exists).
     */
    private function syncTagsByName(Post $post, array $tagNames, string $taxonomy = 'post_tag')
    {
        // Get tag IDs from names
        $newTagIds = [];
        foreach ($tagNames as $name) {
            $name = trim($name);
            if (empty($name))
                continue;

            $tag = \App\Models\Tag::firstOrCreate(
                ['name' => $name, 'taxonomy' => $taxonomy],
                ['slug' => Str::slug($name)]
            );
            $newTagIds[] = $tag->id;
        }

        $this->performSafeSync($post, $newTagIds, $taxonomy);
    }

    /**
     * Sync tags by list of IDs (checkboxes).
     */
    private function syncTaxonomyIds(Post $post, array $ids, string $taxonomy)
    {
        // Filter out invalid/empty IDs
        $ids = array_filter($ids, fn($id) => is_numeric($id));
        $this->performSafeSync($post, $ids, $taxonomy);
    }

    /**
     * Perform the Safe Sync (Diff) logic to avoid clobbering other taxonomies.
     */
    private function performSafeSync(Post $post, array $newIds, string $taxonomy)
    {
        $relationship = $taxonomy === 'category' ? 'categories' : 'tags';
        $currentIds = $post->$relationship()->pluck('tags.id')->toArray();

        $toAttach = array_diff($newIds, $currentIds);
        $toDetach = array_diff($currentIds, $newIds);

        if (!empty($toAttach)) {
            $post->$relationship()->attach($toAttach);
        }
        if (!empty($toDetach)) {
            $post->$relationship()->detach($toDetach);
        }
    }

    public function destroy($id)
    {
        $post = Post::findOrFail($id);
        $type = $post->type;

        if ($post->status !== 'trash') {
            // Move to trash
            $post->update(['status' => 'trash']);
            $message = 'moved to trash';
        } else {
            // Permanently delete
            $post->delete();
            $message = 'deleted permanently';
        }

        return redirect()->route('admin.posts.index', ['type' => $type, 'status' => 'trash'])->with('success', ucfirst($type) . ' ' . $message);
    }

    public function restore($id)
    {
        $post = Post::findOrFail($id);
        $post->update(['status' => 'draft']); // Restore as draft for safety

        return redirect()->route('admin.posts.index', ['type' => $post->type, 'status' => 'trash'])->with('success', 'Post restored from trash');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:trash,restore,delete',
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');
        $count = 0;

        // Perform actions
        if ($action === 'trash') {
            $count = Post::whereIn('id', $ids)->where('status', '!=', 'trash')->update(['status' => 'trash']);
        } elseif ($action === 'restore') {
            $count = Post::whereIn('id', $ids)->where('status', 'trash')->update(['status' => 'draft']);
        } elseif ($action === 'delete') {
            $count = Post::whereIn('id', $ids)->delete(); // Permanent delete based on model event or standard delete? 
            // Since we are using a manual status for trash, delete() deletes the record.
            // If checking strictness: only delete if in trash?
            // User requested "Restore or Delete permanently option show" usually implies from trash.
            // But if I want to support bulk delete from anywhere?
            // Let's stick to safe logic: simple delete() removes the row.
        }

        return back()->with('success', "Bulk action applied to selected items.");
    }
}
