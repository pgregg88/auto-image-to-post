<?php
// Function to sync tile_image and featured image manually
function aitp_sync_images() {
    $post_limit = get_option('aitp_post_limit', '');
    $selected_post_id = get_option('aitp_post_select', '');

    $args = [
        'post_type' => explode(',', get_option('aitp_allowed_post_types', 'capabilities,consulting-services')),
        'posts_per_page' => -1,
    ];

    if (!empty($post_limit)) {
        $args['post__in'] = array_filter(array_map('trim', explode(',', $post_limit)));
    }

    if (!empty($selected_post_id)) {
        $args['post__in'] = [$selected_post_id];
    }

    aitp_log("Version 1.3 - Starting image synchronization");

    $posts = get_posts($args);

    if (!$posts) {
        aitp_log("No posts found for the given criteria.");
    } else {
        aitp_log("Found " . count($posts) . " posts to process.");
    }

    foreach ($posts as $post) {
        $post_id = $post->ID;

        aitp_log("Processing post ID: {$post_id}");

        $tile_image = get_post_meta($post_id, 'tile_image', true);
        $featured_image = get_post_meta($post_id, '_thumbnail_id', true);

        if (empty($tile_image) && !empty($featured_image)) {
            update_post_meta($post_id, 'tile_image', $featured_image);
            aitp_log("Set tile_image for post {$post_id} to the featured image {$featured_image}");
        } elseif (!empty($tile_image)) {
            if ($tile_image != $featured_image) {
                update_post_meta($post_id, '_thumbnail_id', $tile_image);
                aitp_log("Updated featured image for post {$post_id} to the tile_image {$tile_image}");
            } else {
                aitp_log("Tile image and featured image are the same for post {$post_id}, no update needed.");
            }
        } else {
            aitp_log("No tile image or featured image found for post {$post_id}, no update performed.");
        }
    }

    aitp_log("Image synchronization completed");
}
