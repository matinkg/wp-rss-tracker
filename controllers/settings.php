<?php

global $wpdb;

// Check if the form is submitted and update the value
if (isset($_POST['submit'])) {
    if (empty($_POST['posts_expiration_time'])) {
        $expiration_time = null;
    } else {
        $expiration_time = absint($_POST['posts_expiration_time']);
    }

    $wpdb->update(
        $wpdb->prefix . 'rss_tracker_settings',
        array('value' => $expiration_time),
        array('name' => 'posts_expiration_time')
    );
}

// Get the value from the table wp_rss_tracker_settings
$expiration_time = $wpdb->get_var("SELECT value FROM {$wpdb->prefix}rss_tracker_settings WHERE name = 'posts_expiration_time'");

include_once(plugin_dir_path(WPRT_PLUGIN_FILE) . 'views/settings.php');
