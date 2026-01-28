<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomPostType extends Model
{
    protected $fillable = [
        'key',
        'plural_label',
        'singular_label',
        'labels',
        'supports',
        'settings',
        'active'
    ];

    protected $casts = [
        'labels' => 'array',
        'supports' => 'array',
        'settings' => 'array',
        'active' => 'boolean'
    ];

    /**
     * Get default supports
     */
    public static function getDefaultSupports()
    {
        return [
            'title',
            'editor',
            'featured_image'
        ];
    }

    /**
     * Get all available supports
     */
    public static function getAllSupports()
    {
        return [
            'title' => 'Title',
            'editor' => 'Editor',
            'excerpt' => 'Excerpt',
            'author' => 'Author',
            'featured_image' => 'Featured Image',
            'custom_fields' => 'Custom Fields',
            'comments' => 'Comments',
            'revisions' => 'Revisions',
            'trackbacks' => 'Trackbacks',
            'page_attributes' => 'Page Attributes',
            'post_formats' => 'Post Formats'
        ];
    }
}
