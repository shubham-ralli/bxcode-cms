<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Setting;
// CoreHelpers functions are autoloaded
use Liquid\Template;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use App\Models\Tag;
use Illuminate\Support\Facades\Schema;

class FrontendController extends Controller
{
    public function tag($slug, $taxonomy = null)
    {
        if (function_exists('load_theme_functions')) {
            load_theme_functions();
        }

        // DEBUG: Trace execution
        // dd('Tag Method Reached', $slug, $taxonomy);

        $query = Tag::where('slug', $slug);

        if ($taxonomy) {
            $query->where('taxonomy', $taxonomy);
        } else {
            // Fallback: If no taxonomy specified, ANY tag with this slug matches (legacy behavior or first found)
            // Ideally we should prefer post_tag or category if specific base wasn't matched
        }

        $tag = $query->firstOrFail();

        $perPage = Setting::get('posts_per_page', 10);

        $posts = $tag->posts()
            ->when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))
            ->where('status', 'publish')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $this->secureSeo($posts);

        $theme = get_active_theme();

        $view = null;

        // 1. Specific Taxonomy Template
        if ($taxonomy === 'category') {
            if (view()->exists("themes.{$theme}.category")) {
                $view = "themes.{$theme}.category";
            }
        } elseif ($taxonomy === 'post_tag') {
            if (view()->exists("themes.{$theme}.tag")) {
                $view = "themes.{$theme}.tag";
            }
        } else {
            // Custom Taxonomy: taxonomy-{slug}.blade.php or taxonomy.blade.php
            if (view()->exists("themes.{$theme}.taxonomy-{$taxonomy}")) {
                $view = "themes.{$theme}.taxonomy-{$taxonomy}";
            } elseif (view()->exists("themes.{$theme}.taxonomy")) {
                $view = "themes.{$theme}.taxonomy";
            }
        }

        // 2. Generic Archive
        if (!$view) {
            if (view()->exists("themes.{$theme}.archive")) {
                $view = "themes.{$theme}.archive";
            }
        }

        // 3. Fallbacks
        if (!$view) {
            $view = "themes.{$theme}.blog";
            if (!view()->exists($view)) {
                $view = "themes.{$theme}.index";
            }
        }

        View::share('currentTemplate', $view);

        // Pass extra data for the view
        return view($view, [
            'posts' => $posts,
            'archiveTitle' => $tag->name,
            'tag' => $tag
        ]);
    }

    public function handle($slug = null)
    {
        if (function_exists('load_theme_functions')) {
            load_theme_functions();
        }

        // --- Pretty Pagination Logic ---
        // Check if slug ends with /page/{n}
        if ($slug && preg_match('/(.*)\/page\/(\d+)$/', $slug, $matches)) {
            $baseSlug = $matches[1];
            $pageNumber = (int) $matches[2];

            // 1. Force Paginator to use this page
            \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($pageNumber) {
                return $pageNumber;
            });

            // 2. Pretend the slug is just the base slug (without /page/n)
            $slug = $baseSlug;
        }
        // Handle root pagination (e.g. /page/2 for homepage)
        elseif ($slug && preg_match('/^page\/(\d+)$/', $slug, $matches)) {
            $pageNumber = (int) $matches[1];
            \Illuminate\Pagination\Paginator::currentPageResolver(function () use ($pageNumber) {
                return $pageNumber;
            });
            $slug = null; // Treat as homepage
        }
        // -------------------------------

        $showOnFront = Setting::get('show_on_front', 'posts');
        $pageOnFront = Setting::get('page_on_front');
        $pageForPosts = Setting::get('page_for_posts');

        // 0. Preview Logic (Drafts)
        if (request()->has('p') && request()->get('preview') == 'true') {
            $previewId = request()->get('p');
            $previewPost = Post::when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))->find($previewId);

            if ($previewPost) {
                View::share('post', $previewPost);
                return $this->renderPost($previewPost);
            }
        }

        // 0.5. Plain Permalinks support (?p=123)
        // If site is using plain settings, this is how they reach posts.
        if (request()->has('p')) {
            $post = Post::where('id', request()->get('p'))
                ->where('type', 'post')
                ->whereIn('status', ['publish', 'private'])
                ->first();

            if ($post) {
                if ($post->status === 'private' && !Auth::check())
                    abort(404);
                View::share('post', $post);
                return $this->renderPost($post);
            }
        }

        // 1. Home Page Request
        if (!$slug) {
            if ($showOnFront === 'page' && $pageOnFront) {
                $post = Post::when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))->find($pageOnFront);
                if ($post && $post->status === 'publish') {
                    View::share('post', $post);
                    return $this->renderPost($post, true);
                }
            }
            return $this->renderBlog();
        }

        // 2. Try Resolving as Custom Tag/Category Base
        // Check if slug starts with custom base
        $tagBase = trim(Setting::get('tag_base') ?: 'tag', '/');
        $catBase = trim(Setting::get('category_base') ?: 'category', '/');

        // Check Tag Base
        if (str_starts_with($slug, $tagBase . '/')) {
            $remaining = substr($slug, strlen($tagBase) + 1); // +1 for slash
            // If remainder has no slashes, it's likely the tag slug
            if (!str_contains($remaining, '/')) {
                return $this->tag($remaining, 'post_tag');
            }
        }

        // Check Category Base (Logic similar to tag, but reusing tag method for now as it handles tags/cats via slug anyway?)
        // Wait, 'tag()' method queries 'Tag' model. 'Tag' model contains both tags and categories now.
        // So we can reuse 'tag($slug)'.
        if (str_starts_with($slug, $catBase . '/')) {
            $remaining = substr($slug, strlen($catBase) + 1);
            if (!str_contains($remaining, '/')) {
                return $this->tag($remaining, 'category');
            }
        }

        // 2.abc Check Custom Taxonomies
        if (class_exists('\Plugins\ACF\src\Models\CustomTaxonomy')) {
            $customTaxonomies = \Plugins\ACF\src\Models\CustomTaxonomy::where('active', 1)->get();
            foreach ($customTaxonomies as $tax) {
                $taxBase = $tax->key;
                // TODO: Check for rewrite slug in settings if needed, for now assume key
                // $settings = json_decode($tax->settings ?? '{}', true);
                // $taxBase = $settings['slug'] ?? $tax->key;

                if (str_starts_with($slug, $taxBase . '/')) {
                    $remaining = substr($slug, strlen($taxBase) + 1);
                    if (!str_contains($remaining, '/')) {
                        if (!$tax->publicly_queryable) {
                            abort(404);
                        }
                        return $this->tag($remaining, $tax->key);
                    }
                }
            }
        }

        // 2.5 Author Archive
        if (str_starts_with($slug, 'author/')) {
            $username = substr($slug, 7);
            // Try to find user by name (slug-like assumption) or ID if numeric
            $user = \App\Models\User::where('name', $username)->first();
            // If stricter checking needed, add username column to users table

            if ($user) {
                // Determine posts
                $posts = Post::when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))
                    ->where('author_id', $user->id)
                    ->where('type', 'post') // Or any type? Usually authors are for posts.
                    ->where('status', 'publish')
                    ->orderBy('created_at', 'desc')
                    ->paginate(Setting::get('posts_per_page', 10));

                return $this->renderArchive($posts, 'Author: ' . $user->name);
            }
        }

        // 2.6 Date Archive (YYYY or YYYY/MM)
        // Regex: 4 digits, optional / 2 digits
        if (preg_match('/^(\d{4})(\/(\d{2}))?$/', $slug, $matches)) {
            $year = $matches[1];
            $month = $matches[3] ?? null;

            $query = Post::when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))
                ->where('type', 'post')
                ->where('status', 'publish')
                ->whereYear('created_at', $year);

            if ($month) {
                $query->whereMonth('created_at', $month);
            }

            $posts = $query->orderBy('created_at', 'desc')->paginate(Setting::get('posts_per_page', 10));
            $title = $month ? "Date: $year / $month" : "Date: $year";
            return $this->renderArchive($posts, $title);
        }

        // 2.7 Custom Post Types (Root Archive) e.g. /products/
        // Only if it doesn't contain further slashes
        if (!str_contains($slug, '/')) {
            // Lookup CPT by configured slug or key
            $cpt = DB::table('custom_post_types')->get()->first(function ($type) use ($slug) {
                $settings = json_decode($type->settings, true) ?? [];
                $typeSlug = $settings['slug'] ?? $type->key;
                return $typeSlug === $slug;
            });

            if ($cpt) {
                // Check if archive is enabled in settings
                $settings = json_decode($cpt->settings, true) ?? [];
                if (empty($settings['has_archive'])) {
                    abort(404);
                }

                $posts = Post::when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))
                    ->where('type', $cpt->key)
                    ->whereIn('status', ['publish'])
                    ->orderBy('created_at', 'desc')
                    ->paginate(Setting::get('posts_per_page', 10));

                return $this->renderArchive($posts, $cpt->plural_label, $cpt->key);
            }
        }

        // 3. Try Resolving as Page (Hierarchical)
        // Pages usually take precedence in standard WP logic if they exist with that explicit path
        // OR we try to resolve standard page hierarchy logic first.
        $segments = explode('/', $slug);

        $parentId = null;
        $pageCandidate = null;
        $isPageChainValid = true;

        foreach ($segments as $segment) {
            $query = Post::where('slug', $segment)
                ->where('type', 'page')
                ->whereIn('status', ['publish', 'private']);

            if ($parentId) {
                $query->where('parent_id', $parentId);
            } else {
                // Determine if this segment *could* be a root page
                $query->whereNull('parent_id');
            }

            $found = $query->first();

            if (!$found) {
                $isPageChainValid = false;
                break;
            }
            $parentId = $found->id;
            $pageCandidate = $found;
        }

        if ($isPageChainValid && $pageCandidate) {
            if ($pageCandidate->status === 'private' && !Auth::check())
                abort(404);

            // Check if it matches blog page
            if ($showOnFront === 'page' && $pageForPosts && $pageCandidate->id == $pageForPosts) {
                return $this->renderBlog();
            }

            View::share('post', $pageCandidate);
            return $this->renderPost($pageCandidate);
        }

        // 3. Try Resolving as Post (Smart Resolver)
        // If it wasn't a valid hierarchical page, it might be a post with a custom permalink structure.
        // E.g. /2023/10/my-post or /archives/123 or /category/my-post
        // We look at the LAST segment primarily for the slug (or ID).

        $lastSegment = end($segments);

        // a. Try ID (Numeric)
        if (is_numeric($lastSegment)) {
            $post = Post::where('id', $lastSegment)->where('type', 'post')->whereIn('status', ['publish', 'private'])->first();
            if ($post) {
                // Check if the URL actually matches the structure? 
                // Strictly, we should, but for robustness we can be lenient or do a redirect.
                // Ideally, we verify the structure.

                // If the computed URL is different, we should 301 Redirect.
                // Avoiding infinite loops: compare paths
                // $correctUrl = $post->url; 
                // if (request()->url() !== $correctUrl) return redirect($correctUrl, 301);

                if ($post->status === 'private' && !Auth::check())
                    abort(404);
                View::share('post', $post);
                return $this->renderPost($post);
            }
        }

        // b. Try Slug (Post Name)
        $post = Post::where('slug', $lastSegment)->where('type', 'post')->whereIn('status', ['publish', 'private'])->first();
        if ($post) {
            if ($post->status === 'private' && !Auth::check())
                abort(404);
            View::share('post', $post);
            return $this->renderPost($post);
        }

        // 4. Try Custom Post Types (e.g. /movie/title)
        if (count($segments) >= 2) {
            $possibleTypeSlug = $segments[0];
            // If we have intermediate segments (hierarchy), use end logic, but usually CPTs are flat /type/slug
            $possibleSlug = end($segments);

            // Resolve Slug to Key
            $cptType = DB::table('custom_post_types')->get()->first(function ($type) use ($possibleTypeSlug) {
                $settings = json_decode($type->settings, true) ?? [];
                $typeSlug = $settings['slug'] ?? $type->key;
                return $typeSlug === $possibleTypeSlug;
            });

            $cptKey = $cptType ? $cptType->key : $possibleTypeSlug;

            // Check if this type exists and has this post
            // We allow any type name here, effectively dynamic routing
            $cptPost = Post::where('type', $cptKey)
                ->where('slug', $possibleSlug)
                ->whereIn('status', ['publish', 'private'])
                ->first();

            if ($cptPost) {
                // Check if CPT is publicly queryable
                if ($cptType) {
                    $settings = json_decode($cptType->settings, true) ?? [];
                    if (empty($settings['publicly_queryable'])) {
                        abort(404);
                    }
                }

                if ($cptPost->status === 'private' && !Auth::check())
                    abort(404);
                View::share('post', $cptPost);
                return $this->renderPost($cptPost);
            }
        }

        abort(404);
    }

    private function renderBlog()
    {
        $perPage = Setting::get('posts_per_page', 10);

        $posts = Post::when(Schema::hasTable('seo_meta'), fn($q) => $q->with('seo'))->where('type', 'post')
            ->where('status', 'publish')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $this->secureSeo($posts);

        $theme = get_active_theme();
        $view = "themes.{$theme}.blog";
        View::share('currentTemplate', $view);
        return view($view, compact('posts'));
    }

    private function renderPost($post, $isHome = false)
    {
        $this->secureSeo($post);

        $template = new Template();

        $data = [
            'title' => $post->title,
            'post' => $post->toArray(),
            'site_title' => Setting::get('site_title'),
            'tagline' => Setting::get('tagline'),
        ];

        // Merge User-Defined Globals
        $data = array_merge($data, \App\Services\LiquidService::getGlobals());

        // Register User-Defined Tags
        $tags = \App\Services\LiquidService::getTags();
        foreach ($tags as $tag => $class) {
            $template->registerTag($tag, $class);
        }

        $template->parse($post->content);
        $content = $template->render($data);

        // Apply WordPress-style Shortcodes
        if (function_exists('do_shortcode')) {
            $content = do_shortcode($content);
        }

        $theme = get_active_theme();

        $view = "themes.{$theme}.page"; // Default

        if ($isHome) {
            $view = "themes.{$theme}.index";
        } elseif ($post->template && $post->template !== 'default') {
            $view = "themes.{$theme}.{$post->template}";
        } elseif ($post->type === 'page') {
            $view = "themes.{$theme}.page";
        } else {
            // For Post and CPTs: Use 'single' hierarchy
            // 1. single-{type}.blade.php
            $view = "themes.{$theme}.single-{$post->type}";
            if (!view()->exists($view)) {
                // 2. single.blade.php
                $view = "themes.{$theme}.single";
                if (!view()->exists($view)) {
                    // 3. Fallback to index
                    $view = "themes.{$theme}.index";
                }
            }
        }

        if (view()->exists($view)) {
            View::share('currentTemplate', $view);
            return view($view, compact('content', 'post'));
        }

        return view('frontend.page', compact('content', 'post'));
    }

    private function renderArchive($posts, $title, $postType = 'post')
    {
        $this->secureSeo($posts);

        $theme = get_active_theme();

        // 1. Try Specific Archive: themes.{theme}.archive-{post_type}
        $view = "themes.{$theme}.archive-{$postType}";

        if (!view()->exists($view)) {
            // 2. Generic Archive
            $view = "themes.{$theme}.archive";
            if (!view()->exists($view)) {
                // 3. Blog Fallback
                $view = "themes.{$theme}.blog";
                if (!view()->exists($view)) {
                    // 4. Index Fallback
                    $view = "themes.{$theme}.index";
                }
            }
        }

        View::share('currentTemplate', $view);
        View::share('archiveTitle', $title);

        return view($view, [
            'posts' => $posts,
            'archiveTitle' => $title
        ]);
    }

    /**
     * Prevents lazy loading crash if seo_meta table is missing
     */
    private function secureSeo($data)
    {
        if (Schema::hasTable('seo_meta')) {
            return;
        }

        if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator || $data instanceof \Illuminate\Support\Collection) {
            foreach ($data as $post) {
                $post->setRelation('seo', null);
            }
        } elseif ($data instanceof Post) {
            $data->setRelation('seo', null);
        }
    }
}
