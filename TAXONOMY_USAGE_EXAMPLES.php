<?php
/**
 * EXAMPLE: Using the Taxonomy Registration System
 * 
 * This demonstrates how to create custom taxonomies for any post type
 */

// ========================================
// EXAMPLE 1: Register Genre for Books
// ========================================
register_taxonomy('genre', 'book', [
    'label' => 'Genres',
    'hierarchical' => true,  // Like categories (checkbox list)
    'priority' => 15
]);
// Result: "Genres" meta box appears on book edit screen with checkbox list

// ========================================
// EXAMPLE 2: Register Actors for Movies
// ========================================
register_taxonomy('actor', ['movie', 'show'], [
    'label' => 'Actors',
    'hierarchical' => false,  // Like tags (tag input)
    'priority' => 20
]);
// Result: "Actors" meta box appears on movie AND show edit screens with tag input

// ========================================
// EXAMPLE 3: Product Categories
// ========================================
register_taxonomy('product_category', 'product', [
    'label' => 'Product Categories',
    'labels' => [
        'name' => 'Product Categories',
        'singular_name' => 'Product Category',
        'add_new' => 'Add Category'
    ],
    'hierarchical' => true
]);

// ========================================
// EXAMPLE 4: Custom Meta Box Callback
// ========================================
register_taxonomy('difficulty', 'recipe', [
    'label' => 'Difficulty',
    'meta_box_callback' => function ($post, $taxonomy, $args) {
        $selected = get_the_terms($post->id, 'difficulty');
        $selected_name = !empty($selected) ? $selected[0]['name'] : '';

        echo '<div class="p-4">';
        echo '<select name="difficulty[]" class="w-full">';
        echo '<option value="">Select Difficulty</option>';
        foreach (['Easy', 'Medium', 'Hard'] as $level) {
            $sel = $selected_name == $level ? 'selected' : '';
            echo "<option value='$level' $sel>$level</option>";
        }
        echo '</select>';
        echo '</div>';
    }
]);

// ========================================
// HELPER FUNCTIONS USAGE
// ========================================

// Get all taxonomies for a post type
$book_taxonomies = get_taxonomies('book');
// Returns: ['genre']

// Check if taxonomy exists
if (taxonomy_exists('genre')) {
    // Do something
}

// Get all terms in a taxonomy
$genres = get_terms('genre');
// Returns collection of all genre terms

// Get terms for a specific post
$post_genres = get_the_terms($post_id, 'genre');
// Returns array of genre terms for this post

// Check if post has specific term
if (has_term('Science Fiction', 'genre', $post_id)) {
    // This book is science fiction
}

// ========================================
// SAVE HANDLER (automatic via relationships)
// ========================================
// The taxonomy data is automatically saved when form submits
// because the meta boxes use the correct input names (e.g., genre[], actor[])
