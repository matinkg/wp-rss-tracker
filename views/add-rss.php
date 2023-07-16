<div class="wrap">
    <h1>Add RSS</h1>
    <p>You can add an RSS feed to track it.</p>

    <!-- form -->
    <form action="<?php echo admin_url('admin.php?page=add-rss'); ?>" method="post">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="rss_name">RSS Name</label></th>
                    <td><input name="rss_name" type="text" id="rss_name" value="" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row"><label for="rss_url">RSS URL</label></th>
                    <td><input name="rss_url" type="text" id="rss_url" value="" class="regular-text" required></td>
                </tr>
                <tr>
                    <th scope="row">Categories</th>
                    <td>
                        <?php
                        $categories = get_terms( array(
                            'taxonomy'   => 'category',
                            'hide_empty' => false,
                        ) );                        
                        foreach ($categories as $category) {
                            echo '<input type="checkbox" name="categories[]" value="' . $category->term_id . '"> ' . $category->name . '<br>';
                        }
                        ?>
                    </td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="action" value="add">
        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Add RSS To Tracker"></p>
    </form>
</div>
