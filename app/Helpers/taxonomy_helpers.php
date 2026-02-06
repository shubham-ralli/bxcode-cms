<?php
/**
 * Taxonomy Registration System
 * Simplified and working version
 */

global $bx_taxonomies;
$bx_taxonomies = [];

if (!function_exists('register_taxonomy')) {
    function register_taxonomy($taxonomy, $post_types, $args = [])
    {
        global $bx_taxonomies;

        $post_types = (array) $post_types;

        $defaults = [
            'label' => ucfirst($taxonomy),
            'hierarchical' => false,
            'show_in_menu' => true,
            'priority' => 20,
        ];

        $args = array_merge($defaults, $args);

        $bx_taxonomies[$taxonomy] = [
            'name' => $taxonomy,
            'post_types' => $post_types,
            'args' => $args
        ];

        // Register with supports_meta_box system
        add_filter('post_type_supports_meta_box', function ($supports, $post_type, $meta_box) use ($taxonomy, $post_types) {
            if ($meta_box === $taxonomy) {
                if (!isset($supports[$post_type])) {
                    $supports[$post_type] = [];
                }
                if (in_array($post_type, $post_types)) {
                    $supports[$post_type][] = $taxonomy;
                }
            }
            return $supports;
        }, 10);

        return true;
    }
}

if (!function_exists('get_taxonomies')) {
    function get_taxonomies($post_type = null)
    {
        global $bx_taxonomies;

        if ($post_type === null) {
            return array_keys($bx_taxonomies);
        }

        $result = [];
        foreach ($bx_taxonomies as $taxonomy => $data) {
            if (in_array($post_type, $data['post_types'])) {
                $result[] = $taxonomy;
            }
        }

        return $result;
    }
}

if (!function_exists('taxonomy_exists')) {
    function taxonomy_exists($taxonomy)
    {
        global $bx_taxonomies;
        return isset($bx_taxonomies[$taxonomy]);
    }
}

if (!function_exists('get_terms')) {
    function get_terms($taxonomy, $args = [])
    {
        $query = \App\Models\Tag::where('taxonomy', $taxonomy);

        if (isset($args['orderby'])) {
            $query->orderBy($args['orderby'], $args['order'] ?? 'asc');
        }

        return $query->get();
    }
}

if (!function_exists('get_the_terms')) {
    function get_the_terms($post, $taxonomy)
    {
        if (is_numeric($post)) {
            $post = \App\Models\Post::find($post);
        }

        if (!$post) {
            return [];
        }

        return $post->tags()->where('taxonomy', $taxonomy)->get()->toArray();
    }
}

if (!function_exists('has_term')) {
    function has_term($term, $taxonomy, $post)
    {
        $terms = get_the_terms($post, $taxonomy);

        foreach ($terms as $t) {
            if ((is_numeric($term) && $t['id'] == $term) || (is_string($term) && $t['name'] == $term)) {
                return true;
            }
        }

        return false;
    }
}

// Register core taxonomies
add_action('init', function () {
    // Register category for posts
    register_taxonomy('category', 'post', [
        'label' => 'Categories',
        'hierarchical' => true,
        'priority' => 20
    ]);

    // Register tags for posts  
    register_taxonomy('post_tag', 'post', [
        'label' => 'Tags',
        'hierarchical' => false,
        'priority' => 21
    ]);
}, 5);
