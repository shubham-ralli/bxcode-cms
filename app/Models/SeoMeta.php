<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    use HasFactory;

    protected $table = 'seo_meta';

    protected $fillable = [
        'post_id',
        'meta_title',
        'meta_description',
        'focus_keyphrase',
        'canonical_url',
        'breadcrumbs_title',
        'robots_index',
        'robots_follow',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
