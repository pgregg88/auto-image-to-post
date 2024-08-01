<?php
// Function to handle new attachments
function aitp_handle_new_attachment($attachment_id) {
    $attachment = get_post($attachment_id);
    if ($attachment->post_type != 'attachment') {
        return;
    }

    $file = get_attached_file($attachment_id);
    $filename = basename($file);

    aitp_log("Version 1.3 - New attachment detected: $filename");

    if (preg_match('/^(\d{4,5})_(.+)\.\w+$/', $filename, $matches)) {
        $post_id = $matches[1];
        $image_type = $matches[2];

        aitp_log("Version 1.3 - Extracted post_id: $post_id and image_type: $image_type from filename: $filename");

        $post = get_post($post_id);
        if (!$post) {
            aitp_log("Version 1.3 - No post found with ID $post_id");
            return;
        }

        $tile_image = get_post_meta($post_id, 'tile_image', true);
        $featured_image = get_post_meta($post_id, '_thumbnail_id', true);

        if (empty($tile_image) && $image_type == 'tile') {
            update_post_meta($post_id, 'tile_image', $attachment_id);
            aitp_log("Version 1.3 - Set tile_image for post $post_id to attachment $attachment_id");
        } elseif (empty($featured_image) && $image_type == 'hero') {
            update_post_meta($post_id, '_thumbnail_id', $attachment_id);
            aitp_log("Version 1.3 - Set featured_image for post $post_id to attachment $attachment_id");
        } else {
            aitp_log("Version 1.3 - Image type $image_type already set for post $post_id, no update needed");
        }
    } else {
        aitp_log("Version 1.3 - Filename $filename does not match the expected pattern");
    }
}

add_action('add_attachment', 'aitp_handle_new_attachment');
