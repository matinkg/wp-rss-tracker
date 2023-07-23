<?php

/*
Plugin Name: WP RSS Tracker
Description: This plugin tracks the RSS feeds of websites and updates the posts on your website if there is any new news.
Version: 1.0.0
Author: Rexomin
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

    // Define your table name
    $table_name = $wpdb->prefix . 'rss_tracker_settings';

    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
        // Table does not exist, create it
        $charset_collate = $wpdb->get_charset_collate();

        // Define your table structure
        $sql = "CREATE TABLE {$table_name} (
            id INT(11) NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            value TEXT DEFAULT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        // Execute the SQL query
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // add setting values
        $wpdb->insert($wpdb->prefix . 'rss_tracker_settings', array('name' => 'posts_expiration_time', 'value' => '604800',));
    }

});

function wprt_uninstall_plugin()
{
    global $wpdb;

    // Define your table name
    $table_name = $wpdb->prefix . 'rss_tracker';

    // Delete the table
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");

    // Define your table name
    $table_name = $wpdb->prefix . 'rss_tracker_settings';

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

    /* settings */
    add_submenu_page(
        'rss-list',
        'Settings',
        'Settings',
        'manage_options',
        'settings',
        function () {
            include_once(plugin_dir_path(__FILE__) . 'controllers/settings.php');
        }
    );
});

/* ---------------- Custom link for posts added by the plugin --------------- */
// Filter the post permalink
function custom_post_permalink($permalink, $post)
{
    // Check if the post has a rss link
    $rss_link = get_post_meta($post->ID, 'rss_link', true);
    if (!empty($rss_link)) {
        return $rss_link;
    }

    return $permalink;
}
add_filter('post_link', 'custom_post_permalink', 10, 2);
add_filter('post_type_link', 'custom_post_permalink', 10, 2);

// Add custom class to posts added by wprt plugin
function wprt_add_custom_class_to_posts($classes, $class, $post_id) {
    if (get_post_meta($post_id, 'added_by_wprt', true)) {
        $classes[] = 'wprt-post';
    }
    return $classes;
}
add_filter('post_class', 'wprt_add_custom_class_to_posts', 10, 3);

// Enqueue custom JavaScript on the front-end
function wprt_enqueue_scripts() {
    wp_enqueue_script( 'wprt-script-handle', plugins_url( 'wprt-script.js', __FILE__ ), array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'wprt_enqueue_scripts' );


/* --------------- Custom title for posts added by the plugin --------------- */
// Add a custom tag before the wp-block-post-title block
function add_custom_tag_before_post_title($block_content, $block)
{
    global $post;

    if ($post && isset($post->ID) && 'core/post-title' === $block['blockName']) {
        $post_id = $post->ID;

        /* check if rss_source meta exist */
        $rss_source = get_post_meta($post_id, 'rss_source', true);
        if (!empty($rss_source)) {
            $custom_tag = '<span class="rss-src">' . $rss_source . '</span>';

            // Append the custom tag with post meta to the block content
            $block_content = $custom_tag . $block_content;
        }
    }

    return $block_content;
}
add_filter('render_block', 'add_custom_tag_before_post_title', 10, 2);

/* ---------------------------- Delete old posts ---------------------------- */
// Register the scheduled event upon plugin activation
function delete_old_posts_schedule() {
    if (!wp_next_scheduled('wprt_delete_old_posts_event')) {
        wp_schedule_event(time(), 'daily', 'wprt_delete_old_posts_event');
    }
}
register_activation_hook(__FILE__, 'delete_old_posts_schedule');

// Delete old posts when the scheduled event is triggered
function wprt_delete_old_posts() {
    /* get posts_expiration_time */
    global $wpdb;
    $table_name = $wpdb->prefix . 'rss_tracker_settings';
    $posts_expiration_time = $wpdb->get_var("SELECT value FROM $table_name WHERE name = 'posts_expiration_time'");
    
    if (empty($posts_expiration_time)) 
    return;
    
    $args = array(
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key' => 'added_by_wprt', // Custom meta field added by the plugin
                'value' => '1', // Custom value to identify posts added by the plugin
            ),
        ),
        'date_query' => array(
            'before' => date('Y-m-d H:i:s', time() - $posts_expiration_time),
        ),
        'posts_per_page' => -1,
        'fields' => 'ids',
    );
    
    $old_posts = get_posts($args);

    foreach ($old_posts as $post_id) {
        wp_delete_post($post_id, true); // true: Force delete to bypass trash
    }
}
add_action('wprt_delete_old_posts_event', 'wprt_delete_old_posts');

// Remove the scheduled event upon plugin deactivation
function wprt_delete_old_posts_remove_schedule() {
    $timestamp = wp_next_scheduled('wprt_delete_old_posts_event');
    wp_unschedule_event($timestamp, 'wprt_delete_old_posts_event');
}
register_deactivation_hook(__FILE__, 'wprt_delete_old_posts_remove_schedule');

/* --------------------------------- Cronjob -------------------------------- */
// Schedule the cron event when the plugin is activated
register_activation_hook(__FILE__, 'wprt_activate');
function wprt_activate()
{
    wp_schedule_event(time(), 'every_15_minutes', 'wprt_cron_function');
}

// Unschedule the cron event when the plugin is deactivated
register_deactivation_hook(__FILE__, 'wprt_deactivate');
function wprt_deactivate()
{
    wp_clear_scheduled_hook('wprt_cron_function');
}

// Function to run every 15 minutes
add_action('wprt_cron_function', 'wprt_cron_do');
function wprt_cron_do()
{
    include_once(plugin_dir_path(__FILE__) . 'cron.php');
}

// Add custom cron interval for every 15 minutes
add_filter('cron_schedules', 'wprt_custom_cron_interval');
function wprt_custom_cron_interval($schedules)
{
    $schedules['every_15_minutes'] = array(
        'interval' => 900, // 15 minutes in seconds
        'display'  => __('Every 15 Minutes')
    );
    return $schedules;
}
