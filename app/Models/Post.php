<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'type', 'status', 'template', 'author_id', 'featured_image', 'parent_id', 'excerpt'];

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function getUrlAttribute()
    {
        // For Pages, keep hierarchy default
        if ($this->type === 'page') {
            $segments = [$this->slug];
            $parent = $this->parent;
            while ($parent) {
                array_unshift($segments, $parent->slug);
                $parent = $parent->parent;
            }
            return url(implode('/', $segments));
        }

        // For Custom Post Types (not post, page, attachment)
        if (!in_array($this->type, ['post', 'page', 'attachment', 'revision', 'nav_menu_item'])) {
            $cptSlug = \Illuminate\Support\Facades\Cache::remember("cpt_slug_{$this->type}", 600, function () {
                $cpt = \Illuminate\Support\Facades\DB::table('custom_post_types')->where('key', $this->type)->first();
                if ($cpt) {
                    $settings = json_decode($cpt->settings, true) ?? [];
                    return $settings['slug'] ?? $this->type;
                }
                return $this->type;
            });

            return url($cptSlug . '/' . $this->slug);
        }

        // For Standard Posts
        $structure = Setting::get('permalink_structure', '/%postname%/');

        // Plain structure
        if (trim($structure) === '' || $structure === '?p=%post_id%') {
            return url('/?p=' . $this->id);
        }

        // Token Replacement
        $replacements = [
            '%post_id%' => $this->id,
            '%postname%' => $this->slug,
            '%year%' => $this->created_at->format('Y'),
            '%monthnum%' => $this->created_at->format('m'),
            '%day%' => $this->created_at->format('d'),
            '%hour%' => $this->created_at->format('H'),
            '%minute%' => $this->created_at->format('i'),
            '%second%' => $this->created_at->format('s'),
            '%author%' => $this->author ? ($this->author->username ?? $this->author->id) : 'admin',
        ];

        // Categories replacement (get first category slug)
        if (str_contains($structure, '%category%')) {
            $cat = $this->categories->first();
            $replacements['%category%'] = $cat ? $cat->slug : 'uncategorized';
        }

        $path = str_replace(array_keys($replacements), array_values($replacements), $structure);

        // Clean up double slashes but allow structure to define trailing slash
        // $path = preg_replace('#/+#', '/', $path); 

        return url($path);
    }

    public function featuredMedia()
    {
        return $this->belongsTo(Media::class, 'featured_image');
    }

    public function getFeaturedImageUrlAttribute()
    {
        // Check if relationship loaded or exists
        if ($this->featuredMedia) {
            return asset($this->featuredMedia->path);
        }

        // Fallback: Check if it's a legacy raw URL stored in the column (if it contains http or /)
        if ($this->featured_image && (str_contains($this->featured_image, 'http') || str_contains($this->featured_image, '/'))) {
            return $this->featured_image;
        }

        return null;
    }

    public function getFeaturedImageMimeTypeAttribute()
    {
        if ($this->featuredMedia) {
            return $this->featuredMedia->mime_type;
        }
        return 'image/jpeg'; // Default fallback
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function seo()
    {
        return $this->morphOne(SeoMeta::class, 'seoable')->withDefault();
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class)->where('taxonomy', 'post_tag');
    }

    public function categories()
    {
        return $this->belongsToMany(Tag::class)->where('taxonomy', 'category');
    }

    /**
     * Get ALL tags (including custom taxonomies).
     */
    public function allTags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
