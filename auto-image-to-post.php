<?php
/*
Plugin Name: Auto Image to Post
Description: Automatically assigns images to posts based on naming conventions and updates alt text using the ChatGPT API.
Version: 1.3
Author: Preston Gregg
*/

// Register activation hook to set default options
function aitp_activate() {
    if (get_option('aitp_logging_enabled') === false) {
        add_option('aitp_logging_enabled', '0');
    }
    if (get_option('aitp_post_limit') === false) {
        add_option('aitp_post_limit', '');
    }
    if (get_option('aitp_post_select') === false) {
        add_option('aitp_post_select', '');
    }
    if (get_option('aitp_allowed_post_types') === false) {
        add_option('aitp_allowed_post_types', 'capabilities,consulting-services');
    }
}
register_activation_hook(__FILE__, 'aitp_activate');

// Include other plugin files
include(plugin_dir_path(__FILE__) . 'includes/settings-page.php');
include(plugin_dir_path(__FILE__) . 'includes/attachment-handler.php');
include(plugin_dir_path(__FILE__) . 'includes/image-sync.php');
include(plugin_dir_path(__FILE__) . 'includes/utilities.php');
