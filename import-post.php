<?php
/**
 * WordPress Post Import Script
 *
 * Import post from remote server to local WordPress
 */

// WordPress setup
require_once './html/wp-config.php';
require_once './html/wp-load.php';

// Read post data from JSON file
$json_data = file_get_contents('./post-68-export.json');
$post_data = json_decode($json_data, true);

if (!$post_data) {
    die("Error: Could not parse JSON data\n");
}

$post = $post_data['post'];
$post_meta = $post_data['post_meta'];
$images = $post_data['images'];

echo "Importing post: " . $post['post_title'] . "\n";

// Insert the post
$new_post_id = wp_insert_post(array(
    'post_title' => $post['post_title'],
    'post_content' => $post['post_content'],
    'post_excerpt' => $post['post_excerpt'],
    'post_status' => $post['post_status'],
    'post_type' => $post['post_type'],
    'post_date' => $post['post_date']
));

if (is_wp_error($new_post_id)) {
    die("Error inserting post: " . $new_post_id->get_error_message() . "\n");
}

echo "Post created with ID: $new_post_id\n";

// Add post meta (except thumbnail_id for now)
foreach ($post_meta as $meta_key => $meta_value) {
    if ($meta_key !== '_thumbnail_id') {
        update_post_meta($new_post_id, $meta_key, $meta_value);
        echo "Added meta: $meta_key\n";
    }
}

echo "Post import completed successfully!\n";
echo "New post ID: $new_post_id\n";
echo "Next step: Upload images to media library\n";