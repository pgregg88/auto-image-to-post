<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to log events
function aitp_log($message) {
    $logging_enabled = get_option('aitp_logging_enabled', false);
    if (!$logging_enabled) {
        return;
    }

    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/aitp_log.txt';

    $time = current_time('Y-m-d H:i:s');
    $log_entry = "{$time} - {$message}\n";

    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

// Helper function to check if a post ID is within a range
function in_range($post_id, $range) {
    if (preg_match('/^(\d+)-(\d+)$/', $range, $matches)) {
        return $post_id >= $matches[1] && $post_id <= $matches[2];
    }
    return false;
}
?>
