<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form method="post" action="">
        <?php wp_nonce_field('rss_tracker_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="posts_expiration_time">Posts Expiration Time (Seconds)</label>
                </th>
                <td>
                    <input type="tel" id="posts_expiration_time" name="posts_expiration_time" value="<?php echo esc_attr($expiration_time); ?>" />
                </td>
                <td>
                    <p class="description">
                        Enter the number of seconds after which the posts added by the plugin will be deleted.
                    </p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <input type="submit" name="submit" class="button-primary" value="Save Changes">
        </p>
    </form>
</div>
