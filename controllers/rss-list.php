<?php

// handle toggle
if (isset($_GET['action']) && $_GET['action'] == 'toggle' && isset($_GET['rss_id'])) {
    global $wpdb;

    $rss_id = $_GET['rss_id'];
    $rss = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}rss_tracker WHERE id = $rss_id");
    $status = $rss->status ? 0 : 1;
    $wpdb->update(
        $wpdb->prefix . 'rss_tracker',
        array(
            'status' => $status
        ),
        array(
            'id' => $rss_id
        )
    );
}

// handle delete
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['rss_id'])) {
    global $wpdb;

    $rss_id = $_GET['rss_id'];
    $wpdb->delete(
        $wpdb->prefix . 'rss_tracker',
        array(
            'id' => $rss_id
        )
    );
    /* check if rss was deleted */
    if ($wpdb->rows_affected) : ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Done!', 'sample-text-domain'); ?></p>
        </div>

    <?php else : ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Something went wrong!', 'sample-text-domain'); ?></p>
        </div>
<?php endif;
}


// handle update
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_GET['rss_id'])) {
    global $wpdb;

    $rss_id = $_GET['rss_id'];
    updateRssPostsById($rss_id);
}

// Fetch the rss list from the database
global $wpdb;
$table_name = $wpdb->prefix . 'rss_tracker';
$rss_list = $wpdb->get_results("SELECT * FROM $table_name");

/* get rss_list categories */
foreach ($rss_list as $rss) {
    if(empty($rss->categories)) {
        continue;
    }
    
    $categories = explode(',', $rss->categories);
    $rss->categories = array();
    foreach ($categories as $category) {
        $rss->categories[] = get_category($category)->name;
    }
    $rss->categories = implode(', ', $rss->categories);
}

include_once(plugin_dir_path(WPRT_PLUGIN_FILE) . 'views/rss-list.php');