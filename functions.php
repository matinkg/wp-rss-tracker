<?php

function getRssContent($rssUrl)
{
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $rssUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Cookie: __arcsco=98f0188b187b391d6e7e2085df2fd592',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'TE: trailers'
        ),
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $rssContent = curl_exec($curl);
    curl_close($curl);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $rssUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Accept-Encoding: gzip, deflate, br',
            'Connection: keep-alive',
            'Cookie: __arcsco=98f0188b187b391d6e7e2085df2fd592',
            'Upgrade-Insecure-Requests: 1',
            'Sec-Fetch-Dest: document',
            'Sec-Fetch-Mode: navigate',
            'Sec-Fetch-Site: none',
            'Sec-Fetch-User: ?1',
            'TE: trailers'
        ),
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false
    ));

    $rssContent = curl_exec($curl);
    curl_close($curl);

    // Parse the RSS feed
    $xml = simplexml_load_string($rssContent);

    // Check if the XML parsing was successful
    if ($xml) {
        // Access the RSS feed elements
        $channelTitle = $xml->channel->title;
        $channelDescription = $xml->channel->description;

        $rssItems = [];
        foreach ($xml->channel->item as $item) {
            $itemTitle = (!empty($item->title)) ? $item->title : "";
            $itemLink = (!empty($item->link)) ? $item->link : "";
            $itemDescription = (!empty($item->description)) ? $item->description : $item->title;
            $itemPic = (!empty($item->enclosure['url'])) ? $item->enclosure['url'] : "";
            $itemPubDate = (!empty($item->pubDate)) ? $item->pubDate : "";

            /* convert to string */
            $itemTitle = (string) $itemTitle;
            $itemLink = (string) $itemLink;
            $itemDescription = (string) $itemDescription;
            $itemPic = (string) $itemPic;
            $itemPubDate = (string) $itemPubDate;

            $rssItems[] = [
                'title' => $itemTitle,
                'link' => $itemLink,
                'description' => $itemDescription,
                'pic' => $itemPic,
                'pub_date' => $itemPubDate,
                'hash' => md5($itemTitle),
            ];
        }

        return [
            'title' => $channelTitle,
            'description' => $channelDescription,
            'items' => $rssItems
        ];
    } else {
        return false;
    }
}

function getAllRss()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rss_tracker';
    $rss_list = $wpdb->get_results("SELECT * FROM $table_name");
    return $rss_list;
}

function getRssById($rssId)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'rss_tracker';
    $rss = $wpdb->get_row("SELECT * FROM $table_name WHERE id = $rssId");
    return $rss;
}

function addRssPost($rssData)
{
    $postData = [
        'post_title'   => $rssData['title'],
        'post_content' => '',
        'post_excerpt' => $rssData['description'],
        'post_status'  => 'publish',
        'post_type'    => 'post'
    ];

    if(!empty($rssData['categories'])) {
        $postData['post_category'] = $rssData['categories'];
    }

    // Create the post
    $post_id = wp_insert_post($postData);

    // Upload and attach the image to the post
    if (!empty($rssData['pic'])) {
        $image_id = media_sideload_image($rssData['pic'], $post_id, $rssData['title'], 'id');
        if (!is_wp_error($image_id)) {
            set_post_thumbnail($post_id, $image_id);
        }
    }

    // Add the post meta
    add_post_meta($post_id, 'rss_link', $rssData['link']);
    add_post_meta($post_id, 'rss_hash', $rssData['hash']);
    add_post_meta($post_id, 'rss_pub_date', $rssData['pub_date']);

    return $post_id;
}

function isPostWithHashExists($rssHash)
{
    $args = array(
        'post_type' => 'post',
        'meta_query' => array(
            array(
                'key' => 'rss_hash',
                'value' => $rssHash,
                'compare' => '='
            )
        )
    );
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        return true;
    } else {
        return false;
    }
}

function updateRssPostsById($rssId)
{
    $rss = getRssById($rssId);

    if ($rss->status == 0) {
        return;
    }

    $rssCategories = explode(',', $rss->categories);
    $rssContent = getRssContent($rss->rss_url);

    foreach ($rssContent['items'] as $item) {
        if (isPostWithHashExists($item['hash'])) {
            continue;
        }

        $rssData = [
            'title' => $item['title'],
            'description' => $item['description'],
            'link' => $item['link'],
            'pub_date' => $item['pub_date'],
            'categories' => $rssCategories,
            'hash' => $item['hash']
        ];

        if ($rss->get_images == 1) {
            $rssData['pic'] = $item['pic'];
        }

        addRssPost($rssData);
    }

    /* update rss last update */
    global $wpdb;
    $table_name = $wpdb->prefix . 'rss_tracker';
    $wpdb->update(
        $table_name,
        array(
            'last_update' => date('Y-m-d H:i:s')
        ),
        array(
            'id' => $rssId
        )
    );
}
