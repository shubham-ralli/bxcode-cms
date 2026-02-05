<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'taxonomy'];

    public function getUrlAttribute()
    {
        $base = $this->taxonomy === 'category'
            ? \App\Models\Setting::get('category_base', 'category')
            : \App\Models\Setting::get('tag_base', 'tag');

        // Clean base (remove leading/trailing slashes)
        $base = trim($base, '/');

        return url($base . '/' . $this->slug);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
}
