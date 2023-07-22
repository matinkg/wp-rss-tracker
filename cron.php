<?php

require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');

// You can check if the cron is working by uncommenting the following line
//file_get_contents("https://api.telegram.org/bot{BOT_TOKEN}/sendMessage?chat_id={CHAT_ID}&text=CRON WORKING");

// get all rss
$rss_list = getAllRss();

foreach ($rss_list as $rss) {
    updateRssPostsById($rss->id);
}