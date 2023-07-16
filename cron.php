<?php

//file_get_contents("https://api.telegram.org/bot5809391264:AAG4PGosuFIc7MnYLx6xteUw-j893H9QAlM/sendMessage?chat_id=114785662&text=cronworking!");

// get all rss
$rss_list = getAllRss();

foreach ($rss_list as $rss) {
    updateRssPostsById($rss->id);
}