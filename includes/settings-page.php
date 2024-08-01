<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Add a settings page to manage the post limit and logging
function aitp_settings_page() {
    add_options_page(
        'Auto Image to Post Settings',
        'Auto Image to Post',
        'manage_options',
        'auto-image-to-post',
        'aitp_settings_page_html'
    );
}
add_action('admin_menu', 'aitp_settings_page');

// Settings page HTML
function aitp_settings_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_POST['aitp_logging_enabled']) || isset($_POST['aitp_post_limit']) || isset($_POST['aitp_post_select']) || isset($_POST['aitp_allowed_post_types']) || isset($_POST['clear_log']) || isset($_POST['download_log']) || isset($_POST['sync_images'])) {
        check_admin_referer('aitp_settings');
        if (isset($_POST['aitp_logging_enabled'])) {
            update_option('aitp_logging_enabled', isset($_POST['aitp_logging_enabled']) ? '1' : '0');
            echo '<div class="updated"><p>Logging settings saved.</p></div>';
        }
        if (isset($_POST['aitp_post_limit'])) {
            update_option('aitp_post_limit', sanitize_text_field($_POST['aitp_post_limit']));
            echo '<div class="updated"><p>Post limit saved.</p></div>';
        }
        if (isset($_POST['aitp_post_select'])) {
            update_option('aitp_post_select', sanitize_text_field($_POST['aitp_post_select']));
            echo '<div class="updated"><p>Post selection saved.</p></div>';
        }
        if (isset($_POST['aitp_allowed_post_types'])) {
            update_option('aitp_allowed_post_types', sanitize_text_field($_POST['aitp_allowed_post_types']));
            echo '<div class="updated'><p>Allowed post types saved.</p></div>';
        }
        if (isset($_POST['clear_log'])) {
            aitp_clear_log();
        }
        if (isset($_POST['download_log'])) {
            aitp_download_log();
        }
        if (isset($_POST['sync_images'])) {
            aitp_sync_images();
            echo '<div class="updated'><p>Tile and featured images synchronized.</p></div>';
        }
    }

    $logging_enabled = get_option('aitp_logging_enabled', false);
    $post_limit = get_option('aitp_post_limit', '');
    $selected_post_id = get_option('aitp_post_select', '');
    $allowed_post_types = get_option('aitp_allowed_post_types', 'capabilities,consulting-services');

    $post_types = explode(',', $allowed_post_types);
    $posts = get_posts([
        'post_type' => $post_types,
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $selected_post_title = 'All posts';
    if ($selected_post_id) {
        $selected_post = get_post($selected_post_id);
        if ($selected_post) {
            $selected_post_title = $selected_post->post_title;
        }
    }

    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/aitp_log.txt';
    $log_content = file_exists($log_file) ? file_get_contents($log_file) : 'Log file is empty or does not exist.';

    ?>
    <div class="wrap">
        <h1>Auto Image to Post Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('aitp_settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="aitp_logging_enabled">Enable Logging</label></th>
                    <td>
                        <input type="checkbox" id="aitp_logging_enabled" name="aitp_logging_enabled" value="1" <?php checked($logging_enabled, true); ?>>
                        <p class="description">Enable or disable logging of events.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="aitp_post_limit">Post ID Limit</label></th>
                    <td>
                        <input type="text" id="aitp_post_limit" name="aitp_post_limit" value="<?php echo esc_attr($post_limit); ?>" class="regular-text">
                        <p class="description">Enter a post ID or a comma-separated list of post IDs (e.g., 1,2,3) or a range of post IDs (e.g., 1-10).</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="aitp_post_select">Select Post</label></th>
                    <td>
                        <select id="aitp_post_select" name="aitp_post_select">
                            <option value="">-- All Posts --</option>
                            <?php foreach ($posts as $post): ?>
                                <option value="<?php echo esc_attr($post->ID); ?>" <?php selected($selected_post_id, $post->ID); ?>><?php echo esc_html($post->post_title); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Optionally select a post to limit functionality.</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="aitp_allowed_post_types">Allowed Post Types</label></th>
                    <td>
                        <input type="text" id="aitp_allowed_post_types" name="aitp_allowed_post_types" value="<?php echo esc_attr($allowed_post_types); ?>" class="regular-text">
                        <p class="description">Comma-separated list of post types to include (e.g., post,page).</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <form method="post" action="">
            <?php wp_nonce_field('aitp_settings'); ?>
            <input type="hidden" name="sync_images" value="1">
            <?php submit_button('Sync Tile and Featured Images'); ?>
        </form>

        <h2>Selected Post: <?php echo esc_html($selected_post_title); ?></h2>

        <h2>Log File</h2>
        <textarea readonly rows="20" cols="100"><?php echo esc_textarea($log_content); ?></textarea>
        <form method="post" action="">
            <?php wp_nonce_field('aitp_settings'); ?>
            <input type="hidden" name="clear_log" value="1">
            <?php submit_button('Clear Log'); ?>
        </form>
        <form method="post" action="">
            <?php wp_nonce_field('aitp_settings'); ?>
            <input type="hidden" name="download_log" value="1">
            <?php submit_button('Download Log'); ?>
        </form>
        <button id="refresh-log">Refresh Log</button>
    </div>

    <script>
        document.getElementById('refresh-log').addEventListener('click', function() {
            location.reload();
        });
    </script>
    <?php
}

// Clear log file
function aitp_clear_log() {
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/aitp_log.txt';
    if (file_exists($log_file)) {
        file_put_contents($log_file, '');
        echo '<div class="updated"><p>Log file cleared.</p></div>';
    } else {
        echo '<div class="error'><p>Log file does not exist.</p></div>';
    }
}

// Download log file
function aitp_download_log() {
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/aitp_log.txt';
    if (file_exists($log_file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($log_file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($log_file));
        readfile($log_file);
        exit;
    } else {
        echo '<div class="error"><p>Log file does not exist.</p></div>';
    }
}
?>
