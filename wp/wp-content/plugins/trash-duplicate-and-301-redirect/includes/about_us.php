<?php
define('TDRD_NOTIFIER_XML_FILE', 'https://www.solwininfotech.com/documents/assets/plugin_theme.xml');
$body = get_option('plugin_theme');
if($body == '') {
    $response = wp_remote_post(TDRD_NOTIFIER_XML_FILE);
    $body = wp_remote_retrieve_body($response);
    update_option('plugin_theme', $body);
}
$xml = simplexml_load_string($body);
?>
<div class="wrap about-us-section-tdrd">
    <img style="margin-top: 20px;display: block;" src="<?php echo TDRD_PLUGIN_URL . 'images/logo.png'; ?>" alt="<?php _e('Trash Duplicate and 301 Redirect', 'trash-duplicate-and-301-redirect'); ?>" title="<?php _e('Trash Duplicate and 301 Redirect', 'trash-duplicate-and-301-redirect'); ?>" />
    
    <div class="tdrd_text_header">
        <div class="tdrd-text">
            <?php _e('Find and delete duplicates posts, pages, custom post and 301 redirect to the new or old URL.', 'trash-duplicate-and-301-redirect'); ?>
        </div>
    </div>
    <div class="updated notice notice-success" id="message">
        <p><?php _e('Trash Duplicates and 301 Redirect', 'trash-duplicate-and-301-redirect'); ?> : <a href="https://www.solwininfotech.com/documents/wordpress/trash-duplicate-and-301-redirect/"><?php _e('Live Documentation', 'trash-duplicate-and-301-redirect'); ?></a></p>
        <p><?php _e('Want more trash duplicate options and redirections support with wildcard feature and much more?', 'trash-duplicate-and-301-redirect'); ?> <b><a href="https://codecanyon.net/item/trash-duplicate-and-301-redirect-pro-for-wordpress/20885697?ref=solwin" target="blank"><?php _e('Upgrade to PRO', 'trash-duplicate-and-301-redirect'); ?></a></b></p>
    </div>
    <div class="tdrd-features">
        <div class="tdrd-info-heading"><?php _e('Feature Overview', 'trash-duplicate-and-301-redirect'); ?></div>
        <div class="tdrd-info-content">
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-list-view"></span><?php _e('View Duplicate Content', 'trash-duplicate-and-301-redirect'); ?></strong>
                <p><?php _e('View all duplicate posts, pages, custom post type posts with full detail.', 'trash-duplicate-and-301-redirect'); ?></p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-search"></span><?php _e('Duplicate Search', 'trash-duplicate-and-301-redirect'); ?></strong>
                <p><?php _e('Powerfull searching facility available with custom posts.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-star-filled"></span><?php _e('Newest or Oldest', 'trash-duplicate-and-301-redirect'); ?></strong>
                <p>
                    <?php _e('Trash duplicates posts or pages and keep the newest or oldest.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-arrow-right-alt"></span><?php _e('Redirection', 'trash-duplicate-and-301-redirect'); ?></strong>
                <p>
                    <?php _e('Redirection to kept post will be done automatically.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-trash"></span><?php _e('Trash individual post', 'trash-duplicate-and-301-redirect'); ?></strong>
                <p>
                    <?php _e('Individual post can also be trashed.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-arrow-right-alt"></span><?php _e('Wildcard Redirection', 'trash-duplicate-and-301-redirect'); ?><strong class="highlight_pro"><?php _e('PRO', 'trash-duplicate-and-301-redirect'); ?></strong></strong>
                <p>
                    <?php _e('Create Wildcard Redirection with patterns', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-dismiss"></span><?php _e('Add/Delete redirection', 'trash-duplicate-and-301-redirect'); ?></strong>
                <p>
                    <?php _e('You can add or delete redirection.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-trash"></span><?php _e('Trash duplicates without 301 redirection', 'trash-duplicate-and-301-redirect'); ?><strong class="highlight_pro"><?php _e('PRO', 'trash-duplicate-and-301-redirect'); ?></strong></strong>
                <p>
                    <?php _e('Remove duplicate entries without making 301 redirection call for duplicates.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-yes"></span><?php _e('Enable/Disable redirection', 'trash-duplicate-and-301-redirect'); ?><strong class="highlight_pro"><?php _e('PRO', 'trash-duplicate-and-301-redirect'); ?></strong></strong>
                <p>
                    <?php _e('You can enable and disable redirection anytime with the plugin settings.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-warning"></span><?php _e('301 redirection of duplicates without Trash', 'trash-duplicate-and-301-redirect'); ?><strong class="highlight_pro"><?php _e('PRO', 'trash-duplicate-and-301-redirect'); ?></strong></strong>
                <p>
                    <?php _e('Create 301 redirection calls for duplicates without removing those posts.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-no"></span><?php _e('Delete Post Meta Permanently', 'trash-duplicate-and-301-redirect'); ?><strong class="highlight_pro"><?php _e('PRO', 'trash-duplicate-and-301-redirect'); ?></strong></strong>
                <p>
                    <?php _e('Delete post metas for duplicate entries to save disk space.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <strong class="feature-heading"><span class="dashicons dashicons-admin-tools"></span><?php _e('Import/Export Option', 'trash-duplicate-and-301-redirect'); ?><strong class="highlight_pro"><?php _e('PRO', 'trash-duplicate-and-301-redirect'); ?></strong></strong>
                <p>
                    <?php _e('Import and Export your redirection calls to intergrate that with any third party tool.', 'trash-duplicate-and-301-redirect'); ?>
                </p>
            </div>
            <div class="tdrd-feature-upper">
                <a href="https://wordpress.org/plugins/trash-duplicate-and-301-redirect/" target="_blank" class="view-all-features"><?php _e('View All Features', 'trash-duplicate-and-301-redirect'); ?></a>
            </div>
        </div>
    </div>
    <div style="height: 30px;"></div>
    <div class="tdrd-out-other-work">
        <div class="tdrd-info-heading"><?php _e('Our Other Products', 'trash-duplicate-and-301-redirect'); ?></div>
        <div class="tdrd-info-content">
            <ul class="tdrd_theme_plugin">
                <li class="active"><a href="#" data-toggle="plugins"><?php _e('Plugins', 'trash-duplicate-and-301-redirect'); ?></a></li>
                <li><a href="#" data-toggle="themes"><?php _e('Themes', 'trash-duplicate-and-301-redirect'); ?></a></li>
            </ul>
            <div id="plugins">
                <?php
                if ($xml->plugins) {
                    foreach ($xml->plugins as $single_plugin) {
                        unset($single_plugin->trash_duplicate_and_301_redirect);
                        foreach ($single_plugin as $value) {
                            ?>
                            <div class="image_div_upper">
                                <a href="<?php echo $value->link; ?>" target="_blank">
                                    <img src="<?php echo $value->img; ?>" alt="<?php echo $value->name; ?>">
                                    <div class="tdrd_theme_plugin_name"><span><?php echo $value->name; ?></span></div>
                                </a>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
            </div>
            <div id="themes" style="display: none">
                <?php
                if ($xml->themes) {
                    foreach ($xml->themes as $single_theme) {
                        foreach ($single_theme as $value) {
                            ?>
                            <div class="image_div_upper">
                                <a href="<?php echo $value->link; ?>" target="_blank">
                                    <img src="<?php echo $value->img; ?>" alt="<?php echo $value->name; ?>">
                                    <div class="tdrd_theme_plugin_name"><span><?php echo $value->name; ?></span></div>
                                </a>
                            </div>
                            <?php
                        }
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>