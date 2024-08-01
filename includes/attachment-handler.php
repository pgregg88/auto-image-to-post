<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

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

        // Define the meta keys for different image types
        $meta_keys = [
            'tile' => ['tile_image', '_thumbnail_id'],
            'hero' => ['hero_image'],
            'uc-0' => ['uc_graphic_0'],
            'uc-1' => ['uc_graphic_0'],
            'uc-2' => ['uc_graphic_1'],
            'uc-3' => ['uc_graphic_2'],
        ];

        if (isset($meta_keys[$image_type])) {
            foreach ($meta_keys[$image_type] as $meta_key) {
                $current_image = get_post_meta($post_id, $meta_key, true);

                if (empty($current_image)) {
                    update_post_meta($post_id, $meta_key, $attachment_id);
                    aitp_log("Version 1.3 - Set $meta_key for post $post_id to attachment $attachment_id");
                } else {
                    aitp_log("Version 1.3 - $meta_key already set for post $post_id, no update needed");
                }
            }
        } else {
            aitp_log("Version 1.3 - Image type $image_type not recognized for post $post_id");
        }
    } else {
        aitp_log("Version 1.3 - Filename $filename does not match the expected pattern");
    }
}

add_action('add_attachment', 'aitp_handle_new_attachment');
?>
