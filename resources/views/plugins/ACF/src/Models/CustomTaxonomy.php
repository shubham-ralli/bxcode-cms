<?php

namespace Plugins\ACF\src\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomTaxonomy extends Model
{
    use HasFactory;

    protected $table = 'custom_taxonomies';

    protected $fillable = [
        'key',
        'plural_label',
        'singular_label',
        'hierarchical',
        'post_types',
        'active',
        'publicly_queryable'
    ];

    protected $casts = [
        'hierarchical' => 'boolean',
        'post_types' => 'array',
        'active' => 'boolean',
        'publicly_queryable' => 'boolean'
    ];
}
