<div class="wrap">
    <h1>RSS List</h1>
    <p>This plugin currently tracking these RSS feeds:</p>

    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th>Feed Name</th>
                <th>Feed URL</th>
                <th>Source Name</th>
                <th>Get Images</th>
                <th>Status</th>
                <th>Categories</th>
                <th>Last Update</th>
                <th>Start / Stop</th>
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
                    <td><?php echo $item->src_name; ?></td>
                    <td><?php echo $item->get_images ? 'Yes' : 'No'; ?></td>
                    <td><span class="badge <?php echo $item->status ? 'active' : 'inactive'; ?>"><?php echo $item->status ? 'Active' : 'Inactive'; ?></span></td>
                    <td><?php echo $item->categories; ?></td>
                    <td><?php echo $item->last_update; ?></td>
                    <td><a href="<?php echo admin_url('admin.php?page=rss-list&action=toggle&rss_id=' . $item->id); ?>" class="toggle-item" data-item-id="<?php echo $item->id; ?>"><?php echo $item->status ? 'Stop' : 'Start'; ?></a></td>
                    <td><a href="<?php echo admin_url('admin.php?page=rss-list&action=update&rss_id=' . $item->id); ?>" class="update-item" data-item-id="<?php echo $item->id; ?>">Update</a></td>
                    <td><a href="<?php echo admin_url('admin.php?page=rss-list&action=delete&rss_id=' . $item->id); ?>" class="delete-item" data-item-id="<?php echo $item->id; ?>">Delete</a></td>
                </tr>
            <?php endforeach;
            ?>
        </tbody>
    </table>
</div>

<style>
    /* Common styles for both badges */
    .badge {
        padding: 3px 6px;
        border-radius: 5px;
        text-transform: uppercase;
        font-size: 10px;
    }

    /* Styles for the Active Badge */
    .badge.active {
        background-color: #4caf50;
        /* Green color */
        color: #ffffff;
        /* White text */
    }

    /* Styles for the Inactive Badge */
    .badge.inactive {
        background-color: #f44336;
        /* Red color */
        color: #ffffff;
        /* White text */
    }
</style>