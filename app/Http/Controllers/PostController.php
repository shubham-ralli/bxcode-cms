<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

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

        // Fetch Custom Post Type Object if applicable
        $postTypeObj = null;
        if (!in_array($type, ['post', 'page'])) {
            $postTypeObj = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('key', $type)->first();
        }

        return view('admin.posts.index', compact('posts', 'counts', 'type', 'status', 'search', 'postTypeObj'));
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

        // Fetch CPT Object
        $postTypeObj = null;
        if (!in_array($request->type, ['post', 'page'])) {
            $postTypeObj = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('key', $request->type)->first();
        }

        $post = new Post(['type' => $request->type ?? 'post']);
        $action = route('admin.posts.store');

        return view('admin.posts.create', compact('parents', 'users', 'tags', 'categories', 'templates', 'postTypeObj', 'post', 'action'));
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
            'slug' => 'required',
            'content' => 'nullable',
            'type' => 'required',
            'status' => 'required',
            'template' => 'nullable|string',
            'parent_id' => 'nullable|exists:posts,id',
            'excerpt' => 'nullable',
            'tags' => 'nullable|array',
            'categories' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        $uniqueSlug = $this->generateUniqueSlug(
            $request->input('slug'),
            null,
            $request->input('type'),
            $request->input('parent_id')
        );


        // Auto-detect scheduled status based on publish date
        $status = $request->input('status');
        $publishedAt = $request->input('published_at');

        if ($publishedAt && \Carbon\Carbon::parse($publishedAt)->isFuture() && in_array($status, ['publish', 'scheduled'])) {
            $status = 'scheduled';
        }

        $post = Post::create([
            'title' => $request->input('title'),
            'slug' => $uniqueSlug,
            'content' => $request->input('content'),
            'type' => $request->input('type'),
            'status' => $status,
            'template' => $request->input('template') ?: 'default',
            'parent_id' => $request->input('parent_id'),
            'author_id' => Auth::id(),
            'featured_image' => $request->input('featured_image') ?? null,
            'excerpt' => $request->input('excerpt'),
            'published_at' => $publishedAt,
        ]);

        $this->syncTagsByName($post, $request->input('tags', []), 'post_tag');

        // Handle Categories (Default to first if empty)
        $categories = $request->input('categories', []);
        if (empty($categories)) {
            $defaultCategory = \App\Models\Tag::where('taxonomy', 'category')->orderBy('id')->first();
            if ($defaultCategory) {
                $categories[] = $defaultCategory->id;
            }
        }
        $this->syncTaxonomyIds($post, $categories, 'category');

        // Save SEO Meta
        if ($request->has('seo') && Schema::hasTable('seo_meta')) {
            $post->seo()->create($request->input('seo'));
        }

        // Trigger Save Post Hook (for Plugins like ACF)
        do_action('save_post', $post->id, $request);

        return redirect()->route('admin.posts.edit', ['post' => $post->id, 'action' => 'edit'])->with('success', 'Post created successfully');
    }

    public function edit(Request $request)
    {
        $id = $request->query('post');

        $relations = ['author', 'tags', 'categories'];
        if (Schema::hasTable('seo_meta')) {
            $relations[] = 'seo';
        }

        $post = Post::with($relations)->findOrFail($id);

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

        // Fetch CPT Object
        $postTypeObj = null;
        if (!in_array($post->type, ['post', 'page'])) {
            $postTypeObj = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('key', $post->type)->first();
        }

        $action = route('admin.posts.update', $post->id);

        return view('admin.posts.edit', compact('post', 'parents', 'users', 'tags', 'categories', 'templates', 'postTypeObj', 'action'));
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        $request->validate([
            'title' => 'required',
            'slug' => 'required',
            'content' => 'nullable',
            'type' => 'required',
            'status' => 'required',
            'template' => 'nullable|string',
            'parent_id' => 'nullable|exists:posts,id',
            'excerpt' => 'nullable',
            'tags' => 'nullable|array',
            'categories' => 'nullable|array',
            'published_at' => 'nullable|date',
        ]);

        $uniqueSlug = $this->generateUniqueSlug(
            $request->input('slug'),
            $post->id,
            $request->input('type'),
            $request->input('parent_id')
        );


        // Auto-detect scheduled status based on publish date
        $status = $request->input('status');
        $publishedAt = $request->input('published_at');

        if ($publishedAt && \Carbon\Carbon::parse($publishedAt)->isFuture() && in_array($status, ['publish', 'scheduled'])) {
            $status = 'scheduled';
        }

        $post->update([
            'title' => $request->input('title'),
            'slug' => $uniqueSlug,
            'content' => $request->input('content'),
            'type' => $request->input('type'),
            'status' => $status,
            'template' => $request->input('template') ?: 'default',
            'parent_id' => $request->input('parent_id'),
            'featured_image' => $request->input('featured_image'),
            'excerpt' => $request->input('excerpt'),
            'published_at' => $publishedAt,
        ]);

        $this->syncTagsByName($post, $request->input('tags', []), 'post_tag');

        // Handle Categories (Default to first if empty)
        $categories = $request->input('categories', []);
        if (empty($categories)) {
            $defaultCategory = \App\Models\Tag::where('taxonomy', 'category')->orderBy('id')->first();
            if ($defaultCategory) {
                $categories[] = $defaultCategory->id;
            }
        }
        $this->syncTaxonomyIds($post, $categories, 'category');

        // Update or Create SEO Meta
        if ($request->has('seo') && Schema::hasTable('seo_meta')) {
            $post->seo()->updateOrCreate(
                [],
                $request->input('seo')
            );
        }

        // Trigger Save Post Hook
        do_action('save_post', $post->id, $request);


        return back()->with('success', ucfirst($post->type) . ' "' . $post->title . '" updated successfully.');
    }

    /**
     * Generate a unique slug scoped by type and parent_id.
     */
    private function generateUniqueSlug($slug, $ignoreId = null, $type = 'post', $parentId = null)
    {
        $slug = Str::slug($slug);
        $originalSlug = $slug;
        $count = 1;

        // Check if slug exists in DB within the same scope (type & parent)
        while (
            Post::where('slug', $slug)
                ->where('type', $type)
                ->where('parent_id', $parentId)
                ->when($ignoreId, function ($q) use ($ignoreId) {
                    return $q->where('id', '!=', $ignoreId);
                })->exists()
        ) {

            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
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
            // Move to trash - add __trash suffix to slug
            $slug = $post->slug;
            $trashSlug = $slug . '__trash';

            // Check for duplicates and add number if needed
            $counter = 1;
            while (Post::where('slug', $trashSlug)->where('id', '!=', $id)->exists()) {
                $trashSlug = $slug . '__trash_' . $counter;
                $counter++;
            }

            $post->update([
                'status' => 'trash',
                'slug' => $trashSlug
            ]);
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

        // Remove __trash suffix from slug
        $slug = $post->slug;
        if (str_ends_with($slug, '__trash')) {
            $slug = substr($slug, 0, -7); // Remove '__trash'
        } elseif (preg_match('/(.+)__trash_(\d+)$/', $slug, $matches)) {
            $slug = $matches[1]; // Remove '__trash_N'
        }

        $post->update([
            'status' => 'draft',
            'slug' => $slug
        ]);

        return redirect()->route('admin.posts.index', ['type' => $post->type, 'status' => 'trash'])->with('success', 'Post restored from trash');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:trash,restore,delete,draft,private,publish',
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');
        $count = 0;

        // Perform actions
        if ($action === 'trash') {
            foreach ($ids as $id) {
                $post = Post::find($id);
                if ($post && $post->status !== 'trash') {
                    // Add __trash suffix to slug
                    $slug = $post->slug;
                    $trashSlug = $slug . '__trash';

                    // Check for duplicates and add number if needed
                    $counter = 1;
                    while (Post::where('slug', $trashSlug)->where('id', '!=', $id)->exists()) {
                        $trashSlug = $slug . '__trash_' . $counter;
                        $counter++;
                    }

                    $post->update([
                        'status' => 'trash',
                        'slug' => $trashSlug
                    ]);
                    $count++;
                }
            }
        } elseif ($action === 'restore') {
            foreach ($ids as $id) {
                $post = Post::find($id);
                if ($post && $post->status === 'trash') {
                    // Remove __trash suffix
                    $slug = $post->slug;
                    if (str_ends_with($slug, '__trash')) {
                        $slug = substr($slug, 0, -7);
                    } elseif (preg_match('/(.+)__trash_(\d+)$/', $slug, $matches)) {
                        $slug = $matches[1];
                    }

                    $post->update([
                        'status' => 'draft',
                        'slug' => $slug
                    ]);
                    $count++;
                }
            }
        } elseif ($action === 'delete') {
            $count = Post::whereIn('id', $ids)->delete();
        } elseif ($action === 'draft') {
            $count = Post::whereIn('id', $ids)->update(['status' => 'draft']);
        } elseif ($action === 'private') {
            $count = Post::whereIn('id', $ids)->update(['status' => 'private']);
        } elseif ($action === 'publish') {
            $count = Post::whereIn('id', $ids)->update(['status' => 'publish']);
        }

        return back()->with('success', "Bulk action applied to selected items.");
    }
}
