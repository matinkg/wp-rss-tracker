<?php
/*
Plugin Name: WP RSS Tracker
Description: This plugin, tracks the RSS feeds of websites and update the posts on your website if there is any new news.
*/

include_once(plugin_dir_path(__FILE__) . 'functions.php');
define('WPRT_PLUGIN_FILE', __FILE__);

/* create database */
register_activation_hook(__FILE__, function () {
    global $wpdb;

    // Define your table name
    $table_name = $wpdb->prefix . 'rss_tracker';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        // Table does not exist, create it
        $charset_collate = $wpdb->get_charset_collate();

        // Define your table structure
        $sql = "CREATE TABLE {$table_name} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            src_name VARCHAR(255) NOT NULL,
            rss_url TEXT NOT NULL,
            get_images TINYINT(1) NOT NULL DEFAULT 0,
            status TINYINT(1) NOT NULL DEFAULT 1,
            categories TEXT DEFAULT NULL,
            last_update DATETIME DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Execute the SQL query
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
});

function wprt_uninstall_plugin()
{
    global $wpdb;

    // Define your table name
    $table_name = $wpdb->prefix . 'rss_tracker';

    // Delete the table
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}
register_uninstall_hook(__FILE__, 'wprt_uninstall_plugin');

/* add admin pages to admin menu */
add_action('admin_menu', function () {
    add_menu_page(
        'RSS list',
        'WP RSS Tracker',
        'manage_options',
        'rss-list',
        function () {
            include_once(plugin_dir_path(__FILE__) . 'controllers/rss-list.php');
        },
        'dashicons-rss'
    );

    /* add rss */
    add_submenu_page(
        'rss-list',
        'Add RSS',
        'Add RSS',
        'manage_options',
        'add-rss',
        function () {
            include_once(plugin_dir_path(__FILE__) . 'controllers/add-rss.php');
        }
    );
});


/* --------------------------------- Cronjob -------------------------------- */
// Schedule the cron event when the plugin is activated
register_activation_hook(__FILE__, 'wprt_activate');
function wprt_activate() {
    wp_schedule_event(time(), 'every_15_minutes', 'wprt_function');
}

// Unschedule the cron event when the plugin is deactivated
register_deactivation_hook(__FILE__, 'wprt_deactivate');
function wprt_deactivate() {
    wp_clear_scheduled_hook('wprt_function');
}

// Function to run every 15 minutes
add_action('wprt_function', 'wprt_do_something');
function wprt_do_something() {
    include_once(plugin_dir_path(__FILE__) . 'cron.php');
}

// Add custom cron interval for every 15 minutes
add_filter('cron_schedules', 'wprt_custom_cron_interval');
function wprt_custom_cron_interval($schedules) {
    $schedules['every_15_minutes'] = array(
        'interval' => 900, // 15 minutes in seconds
        'display'  => __('Every 15 Minutes')
    );
    return $schedules;
}

