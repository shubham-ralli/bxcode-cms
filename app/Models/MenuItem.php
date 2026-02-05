<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'order',
        'title',
        'url',
        'target',
        'css_class',
        'type',
        'type_id'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function children()
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('order');
    }

    public function related()
    {
        return $this->belongsTo(Post::class, 'type_id');
    }

    public function getUrlAttribute($value)
    {
        // Custom links return the stored URL
        if ($this->type === 'custom' || empty($this->type)) {
            return $value;
        }

        // Dynamic links (Page, Post, CPT)
        // Check if relationship is loaded to avoid N+1 if possible, or just query
        $post = $this->related;

        // If related post exists, return its dynamic URL
        if ($post) {
            return $post->url;
        }

        // Fallback for missing/deleted posts
        return $value ?? '#';
    }
}
