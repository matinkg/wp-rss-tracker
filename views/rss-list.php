<div class="wrap">
    <h1>RSS List</h1>
    <p>This plugin currently tracking these RSS feeds:</p>

    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th>Feed Name</th>
                <th>Feed URL</th>
                <th>Categories</th>
                <th>Last Update</th>
                <th>Update</th>
                <th>Delete</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rss_list as $item) :
            ?>
                <tr>
                    <td><?php echo $item->name; ?></td>
                    <td><?php echo $item->rss_url; ?></td>
                    <td><?php echo $item->categories; ?></td>
                    <td><?php echo $item->last_update; ?></td>
                    <td><a href="<?php echo admin_url('admin.php?page=rss-list&action=update&rss_id=' . $item->id); ?>" class="update-item" data-item-id="<?php echo $item->id; ?>">Update</a></td>
                    <td><a href="<?php echo admin_url('admin.php?page=rss-list&action=delete&rss_id=' . $item->id); ?>" class="delete-item" data-item-id="<?php echo $item->id; ?>">Delete</a></td>
                </tr>
            <?php endforeach;
            ?>
        </tbody>
    </table>
</div>