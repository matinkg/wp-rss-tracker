<?php


$rssUrl = 'https://www.farsnews.ir/rss/world'; // Replace with the URL of the RSS feed

// Retrieve the RSS feed content
$rssContent = file_get_contents($rssUrl);

// Parse the RSS feed
$xml = simplexml_load_string($rssContent);

// Check if the XML parsing was successful
if ($xml) {
    // Access the RSS feed elements
    $channelTitle = $xml->channel->title;
    $channelDescription = $xml->channel->description;

    // Create a new post with the RSS feed content
    $postContent = '<h1>' . $channelTitle . '</h1>';
    $postContent .= '<p>' . $channelDescription . '</p>';

    foreach ($xml->channel->item as $item) {
        $itemTitle = $item->title;
        $itemLink = $item->link;
        $itemDescription = $item->description;

        $postContent .= '<h3><a href="' . $itemLink . '">' . $itemTitle . '</a></h3>';
        $postContent .= '<p>' . $itemDescription . '</p>';
    }

    // Create a new post in the WordPress database
    $newPost = array(
        'post_title' => 'RSS Content',
        'post_content' => $postContent,
        'post_status' => 'publish',
        'post_author' => 1, // Replace with the desired author ID
        'post_type' => 'post',
    );

    // Insert the post into the WordPress database
    $postID = wp_insert_post($newPost);

    if ($postID) {
        echo 'RSS content added successfully!'; // Success message
    } else {
        echo 'Error adding RSS content.'; // Error message
    }
} else {
    echo 'Error parsing the RSS feed.'; // Error message
}