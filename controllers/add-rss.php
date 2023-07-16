<?php

/* here we should handle add rss */
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    global $wpdb;

    $rss_name = $_POST['rss_name'];
    $rss_url = $_POST['rss_url'];
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];

    /* check if rss_url is valid */
    if (!filter_var($rss_url, FILTER_VALIDATE_URL)) : ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('Invalid RSS URL!', 'sample-text-domain'); ?></p>
        </div>
        <?php else :

        $wpdb->insert(
            $wpdb->prefix . 'rss_tracker',
            array(
                'name' => $rss_name,
                'rss_url' => $rss_url,
                'categories' => implode(',', $categories),
            )
        );
        /* check if rss was added */
        if ($wpdb->insert_id) : ?>
            <div class="notice notice-success is-dismissible">
                <p><?php _e('Done!', 'sample-text-domain'); ?></p>
            </div>

        <?php else : ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Something went wrong!', 'sample-text-domain'); ?></p>
            </div>
<?php endif;
    endif;
}

include_once(plugin_dir_path(WPRT_PLUGIN_FILE) . 'views/add-rss.php');
