<?php
/*
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Use add menu in admin side
 */
if (!function_exists('tdrd_menu')) {

    function tdrd_menu() {
        global $screen_option_page;
        $tdrd_is_optin = get_option('tdrd_is_optin');
        if($tdrd_is_optin == 'yes' || $tdrd_is_optin == 'no') {
            $screen_option_page = add_menu_page(__('Trash Duplicates', 'trash-duplicate-and-301-redirect'), __('Trash Duplicates', 'trash-duplicate-and-301-redirect'), 'update_plugins', 'trash_duplicates', 'tdrd_admin_func', 'dashicons-media-text');
        }
        else {
            $screen_option_page = add_menu_page(__('Trash Duplicates', 'trash-duplicate-and-301-redirect'), __('Trash Duplicates', 'trash-duplicate-and-301-redirect'), 'update_plugins', 'trash_duplicates_welcome', 'tdrd_admin_welcome_func', 'dashicons-media-text');
        }
        
        add_action("load-$screen_option_page", 'tdrd_screen_options');
        add_submenu_page('trash_duplicates', __('301 Redirects', 'trash-duplicate-and-301-redirect'), __('301 Redirects', 'trash-duplicate-and-301-redirect'), 'update_plugins', '301_redirects', 'tdrd_301_redirect');
        add_submenu_page('trash_duplicates', __('About Trash Duplicates', 'trash-duplicate-and-301-redirect'), __('About Trash Duplicates', 'trash-duplicate-and-301-redirect'), 'update_plugins', 'about_trash_duplicates', 'tdrd_about_us');
    }

}
add_action('admin_menu', 'tdrd_menu', 5);

/**
 * add per page option in screen option in single post templates list
 * @global string $bdp_screen_option_page
 */
if (!function_exists('tdrd_screen_options')) {

    function tdrd_screen_options() {
        global $screen_option_page;
        $screen = get_current_screen();
        // get out of here if we are not on our settings page
        if (!is_object($screen) || ($screen->id != $screen_option_page && $screen->id != 'toplevel_page_trash_duplicates'))
            return;

        $args = array(
            'label' => __('Number of items per page:', 'trash-duplicate-and-301-redirect'),
            'default' => 10,
            'option' => 'tdrd_per_page'
        );
        add_screen_option('per_page', $args);
    }

}

/**
 *
 * @param type $status
 * @param type $option
 * @param type $value
 * @return type
 */
if (!function_exists('tdrd_set_screen_option')) {

    function tdrd_set_screen_option($status, $option, $value) {
        if ('tdrd_per_page' == $option)
            return $value;
    }

}

/**
 * Include admin shortcode list page
 */
if (!function_exists('tdrd_about_us')) {

    function tdrd_about_us() {
        include_once( 'includes/about_us.php' );
    }

}

/**
 *
 * @param $actions for take a action for redirection setting
 * @param $plugin_file give path of plugin file
 * @return action for setting link
 */
if (!function_exists('tdrd_settings_link')) {

    function tdrd_settings_link($actions, $plugin_file) {
        static $plugin;
        if (empty($plugin))
            $plugin = dirname(plugin_basename(__FILE__)) . '/trash-duplicates.php';
        if ($plugin_file == $plugin) {
            $settings_link = '<a href="' . admin_url('admin.php?page=trash_duplicates') . '">' . __('Settings', 'trash-duplicate-and-301-redirect') . '</a>';
            array_unshift($actions, $settings_link);
        }
        return $actions;
    }

}
add_filter('plugin_action_links', 'tdrd_settings_link', 10, 2);

/**
 * Use for register and enqueue style and script
 */
if (!function_exists('tdrd_enqueue')) {

    function tdrd_enqueue() {
        // register and enqueue stylesheet and scripts
        $trash_duplicates_options = get_option('trash_duplicates_options');
        $screen = get_current_screen();
        if ($screen->id == 'dashboard' || $screen->id == 'plugins' || (isset($_GET['page']) && ($_GET['page'] == 'trash_duplicates' || $_GET['page'] == '301_redirects' || $_GET['page'] == 'about_trash_duplicates' || $_GET['page'] == 'trash_duplicates_welcome'))) {
            wp_register_style('admin_css', plugins_url('/css/trash-duplicates.css', __FILE__), false, $trash_duplicates_options['version']);
            wp_enqueue_style('admin_css');
        }
        wp_enqueue_script('admin_js', plugins_url('/js/trash-duplicates.js', __FILE__), array('jquery'), $trash_duplicates_options['version']);
        wp_localize_script('admin_js', 'tdrc_obj', array(
            'all_post_move_trash' => __('Make sure all posts will be moved to the trash?', 'trash-duplicate-and-301-redirect'),
            'selected_all_posts' => __('Make sure you have selected all posts below?', 'trash-duplicate-and-301-redirect')
        ));

        if (is_rtl()) {
            wp_enqueue_style('admin_rtl_css', plugins_url('/css/trash-duplicates-rtl.css', __FILE__), false, $trash_duplicates_options['version']);
        }
    }

}
add_action('admin_enqueue_scripts', 'tdrd_enqueue');

/**
 *
 * @global type $wpdb
 * Use for table create in database when activate this plugin.
 */
if (!function_exists('tdrd_table')) {

    function tdrd_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'tdrd_redirection';
        $sql_create = "CREATE TABLE IF NOT EXISTS " . $table_name . " (
            ID int(15) NOT NULL AUTO_INCREMENT,
            old_url TEXT,
            new_url TEXT,
            date_time datetime,
            PRIMARY KEY  (ID)
        );";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_create);
    }

}
register_activation_hook(__FILE__, 'tdrd_table');
tdrd_table();

/**
 * Function for rating and download display
 */
if (!function_exists('tdrd_get_total_downloads')) {

    function tdrd_get_total_downloads() {
        // Set the arguments. For brevity of code, I will set only a few fields.
        $plugins = $response = "";
        $args = array(
            'author' => 'solwininfotech',
            'fields' => array(
                'downloaded' => true,
                'downloadlink' => true
            )
        );
        // Make request and extract plug-in object. Action is query_plugins
        $response = wp_remote_post(
                'http://api.wordpress.org/plugins/info/1.0/', array(
            'body' => array(
                'action' => 'query_plugins',
                'request' => serialize((object) $args)
            )
                )
        );
        if (!is_wp_error($response)) {
            $returned_object = unserialize(wp_remote_retrieve_body($response));
            $plugins = $returned_object->plugins;
        } else {

        }
        $current_slug = 'trash-duplicate-and-301-redirect';
        if ($plugins) {
            foreach ($plugins as $plugin) {
                if ($current_slug == $plugin->slug) {
                    if ($plugin->downloaded) {
                        ?>
                        <span class="total-downloads">
                            <span class="download-number"><?php echo $plugin->downloaded; ?></span>
                        </span><?php
                    }
                }
            }
        }
    }

}

$wp_version = get_bloginfo('version');
if ($wp_version > 3.8) {
    if (!function_exists('tdrd_wp_trash_duplicates_star_rating')) {

        function tdrd_wp_trash_duplicates_star_rating($args = array()) {
            $plugins = $response = "";
            $args = array(
                'author' => 'solwininfotech',
                'fields' => array(
                    'downloaded' => true,
                    'downloadlink' => true
                )
            );
            // Make request and extract plug-in object. Action is query_plugins
            $response = wp_remote_post(
                    'http://api.wordpress.org/plugins/info/1.0/', array(
                'body' => array(
                    'action' => 'query_plugins',
                    'request' => serialize((object) $args)
                )
                    )
            );
            if (!is_wp_error($response)) {
                $returned_object = unserialize(wp_remote_retrieve_body($response));
                $plugins = $returned_object->plugins;
            } else {

            }
            $current_slug = 'trash-duplicate-and-301-redirect';
            if ($plugins) {
                foreach ($plugins as $plugin) {
                    if ($current_slug == $plugin->slug) {
                        $rating = $plugin->rating * 5 / 100;
                        if ($rating > 0) {
                            $args = array(
                                'rating' => $rating,
                                'type' => 'rating',
                                'number' => $plugin->num_ratings,
                            );
                            wp_star_rating($args);
                        }
                    }
                }
            }
        }

    }
}

/**
 * @return html for display redirection entries
 */
if (!function_exists('tdrd_admin_func')) {

    function tdrd_admin_func() {
        global $trash_final_result;
        $group = 1;
        global $wpdb;
        // get the options
        $trash_duplicates_options = get_option('trash_duplicates_options');
        // get the list of custom post types, attachments etc.
        $trash_duplicates_post_types = get_post_types(array('show_ui' => true));
        // set up the database details
        $tbl_nm = $wpdb->prefix . 'posts';
        // set query variable based on post types
        if (isset($_GET['trash_post_type']) && esc_html($_GET['trash_post_type']) != '0') {
            $post_type = esc_html($_GET['trash_post_type']);
            $trash_post_type = $wpdb->prepare("$tbl_nm.post_type = %s", $post_type);
        } else {
            $post_type = 0;
            $post_type_array = array();
            foreach ($trash_duplicates_post_types as $key => $value) {
                $post_type_array[] = $wpdb->prepare("$tbl_nm.post_type = '%s'", $key);
            }
            $trash_post_type = implode($post_type_array, ' OR ');
            $trash_post_type = '(' . $trash_post_type . ')';
        }
        // For filter post as per search parameter
        if (isset($_GET['search']) && $_GET['search'] != '') {
            $trash_duplicates_search_query = esc_html($_GET['search']);
            $main_search_query = $wpdb->prepare(" AND $tbl_nm.post_title LIKE %s", '%' . $wpdb->esc_like($trash_duplicates_search_query) . '%');
            $main_search_query2 = $wpdb->prepare(" AND first.post_title LIKE %s", '%' . $wpdb->esc_like($trash_duplicates_search_query) . '%');
        } else {
            $trash_duplicates_search_query = '';
            $main_search_query = '';
            $main_search_query2 = '';
        }
        // setup variable and SQL string based for show draft
        $show_drafts = 0;
        $show_drafts_query = "$tbl_nm.post_status ='publish'";

        if (isset($_REQUEST['show-draft']) && esc_html($_REQUEST['show-draft']) == 1) {
            $show_drafts = 1;
            $show_drafts_query = '(' . "$tbl_nm.post_status ='publish' OR $tbl_nm.post_status ='draft'" . ")";
        }

        if (strpos($trash_post_type, 'attachment') !== false) {
            $show_drafts_query =  "( $tbl_nm.post_status ='publish' OR $tbl_nm.post_status ='inherit' ) AND NOT $tbl_nm.post_type = 'revision'";
            if (isset($_REQUEST['show-draft']) && esc_html($_REQUEST['show-draft']) == 1) {
                $show_drafts = 1;
                $show_drafts_query = "( $tbl_nm.post_status ='publish' OR $tbl_nm.post_status ='draft' OR $tbl_nm.post_status ='inherit' ) AND NOT $tbl_nm.post_type = 'revision'";
            }
        }
        // Number of duplicate titles
        $trash_count_record = "SELECT COUNT(first.post_title) FROM ( SELECT post_title, post_type,post_author FROM $tbl_nm WHERE $trash_post_type $main_search_query AND $show_drafts_query GROUP BY post_title,post_type,post_author HAVING COUNT(*)>1 ) AS first INNER JOIN ( SELECT post_title, post_type,post_author FROM $tbl_nm WHERE $trash_post_type $main_search_query AND $show_drafts_query GROUP BY post_title,post_type,post_author HAVING COUNT(*)>1 )AS second ON first.post_title = second.post_title AND first.post_type = second.post_type AND first.post_author = second.post_author";
        $trash_count_result = $wpdb->get_var($trash_count_record);
        // pagination-code
        $user = get_current_user_id();
        $screen = get_current_screen();
        $screen_option = $screen->get_option('per_page', 'option');
        $limit = get_user_meta($user, $screen_option, true);
        if (isset($_GET['page']) && absint($_GET['page'])) {
            $show_num = absint($_GET['page']);
        } elseif (isset($limit)) {
            $show_num = $limit;
        } else {
            $show_num = get_option('posts_per_page');
        }
        if (!isset($limit) || empty($limit)) {
            $limit = 10;
        }
        if (empty($show_num) || !isset($show_num)) {
            $show_num = 10;
        }


        if (isset($_REQUEST['number']) && intval($_REQUEST['number']) && $_REQUEST['number'] > 1 && $trash_count_result > 0) :
            $page = intval($_REQUEST['number']);
            if ($page < 1) {
                $page = 1;
            } elseif (( $page - 1 ) * $show_num >= $trash_count_result) {
                $page = ceil($trash_count_result / $show_num);
            }
            $offset = ( $page - 1 ) * $show_num;
        else :
            $offset = 0;
            $page = 1;
        endif;


        $pages_all = ceil($trash_count_result / $show_num);
        // get the duplicates from the Database

        $trash_duplicates_query = "SELECT * FROM $tbl_nm AS first INNER JOIN ( SELECT post_title,post_type,post_author FROM $tbl_nm WHERE $trash_post_type $main_search_query AND $show_drafts_query GROUP BY post_title,post_type,post_author HAVING COUNT(*)>1 LIMIT $offset, $show_num ) AS second ON first.post_title = second.post_title AND first.post_type = second.post_type AND first.post_author = second.post_author
            WHERE " . str_replace($tbl_nm, 'first', $trash_post_type) . " AND " . str_replace($tbl_nm, 'first', $show_drafts_query) . $main_search_query2 . "
            ORDER BY first.post_title, first.post_type, first.post_author DESC";
        $trash_final_result = $wpdb->get_results($trash_duplicates_query, ARRAY_A);

        $count_individual_query = "SELECT COUNT(*) FROM $tbl_nm AS first INNER JOIN ( SELECT post_title,post_type,post_author FROM $tbl_nm WHERE $trash_post_type $main_search_query AND $show_drafts_query GROUP BY post_title,post_type,post_author HAVING COUNT(*)>1 ) AS second ON first.post_title = second.post_title AND first.post_type = second.post_type AND first.post_author = second.post_author
            WHERE " . str_replace($tbl_nm, 'first', $trash_post_type) . " AND " . str_replace($tbl_nm, 'first', $show_drafts_query) . $main_search_query2 . "
            ORDER BY first.post_title, first.post_type, first.post_author DESC";

        $trash_individual_result_count = $wpdb->get_var($count_individual_query);
        // if no duplicates.
        if ($trash_count_result == 0 && empty($trash_duplicates_search_query)) :
            ?>
            <div class="updated notice is-dismissible below-h1" id="message">
                <p><?php _e('Congratulations! you have no duplicate posts found.', 'trash-duplicate-and-301-redirect') ?>
                    <button class="notice-dismiss" type="button">
                        <span class="screen-reader-text"><?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?></span>
                    </button>
            </div><?php endif;
        ?>
        <div class="trash-duplicates-wrap wrap">
            <div class="trash-board-header">
                <h1>
                    <?php _e('Trash Duplicate', 'trash-duplicate-and-301-redirect') ?>
                </h1>
                <?php if (isset($_SESSION['success_msg'])) { ?>
                    <div class="updated is-dismissible notice settings-error"><?php
                        echo '<p>' . $_SESSION['success_msg'] . '</p>';
                        unset($_SESSION['success_msg']);
                        ?></div>
                <?php } ?>
            </div>
            <div class="trash-duplicates-inner">
                <form id="sol-trash-duplicates-form" class="sol-trash-duplicates-form" method="POST" action="#">
                    <div class="trash-inner-top">
                        <div class="trash-duplicates-result">
                            <ul class="subsubsub">
                                <li class="all">
                                    <?php _e(' Total Duplicate Posts:', 'trash-duplicate-and-301-redirect'); ?>
                                    <span class="count">(<?php echo $trash_count_result; ?>)</span> |
                                </li>
                                <li class="mine"><?php _e(' Total Individual Posts: ', 'trash-duplicate-and-301-redirect'); ?>
                                    <span class="count">(<?php echo $trash_individual_result_count; ?>)</span>
                                </li>
                            </ul>
                        </div>
                        <div class="trash-duplicates-search">
                            <p class="search-box">
                                <input id="tra-search-input" type="search" name="tra-search-input" placeholder="<?php esc_attr_e('Post title', 'trash-duplicate-and-301-redirect') ?>" value="<?php echo $trash_duplicates_search_query; ?>" />
                                <input id="tra-search-submit" class="button" name="tra-search-submit" type="submit" value="<?php esc_attr_e('Search Duplicates', 'trash-duplicate-and-301-redirect') ?>" />
                            </p>
                        </div>
                    </div>
                    <div class="sol-action-options">
                        <select name="duplicates-action-top">
                            <option selected="selected" value="none"><?php _e('Bulk Actions', 'trash-duplicate-and-301-redirect') ?></option>
                            <option value="trash"><?php _e('Move to Trash', 'trash-duplicate-and-301-redirect') ?></option>
                            <option value="delete_pr"><?php _e('Delete Permanently', 'trash-duplicate-and-301-redirect') ?></option>
                        </select>
                        <label>
                            <input id="show-draft" type="checkbox" value="1" <?php if ($show_drafts) echo 'checked="checked" '; ?> name="show-draft"><?php _e('Show Drafts', 'trash-duplicate-and-301-redirect') ?>
                        </label>
                        <input id="take_action" class="button action" name="take_action" type="submit" value="<?php esc_attr_e('Apply', 'trash-duplicate-and-301-redirect') ?>" >
                        <select name="trash-post-types">
                            <option <?php if ($post_type == 0) echo 'selected="selected" '; ?>value="0"><?php _e('All post types', 'trash-duplicate-and-301-redirect') ?></option>
                            <?php
                            // add custom post types to the options.
                            foreach ($trash_duplicates_post_types as $key => $value) :
                                if($key != 'wp_block') {
                                    echo '<option value="' . $key . '"';
                                    if ($post_type === $key)
                                        echo ' selected="selected"';echo '>' . $value . '</option>' . "\n";
                                }
                               
                            endforeach;
                            ?>
                        </select>
                        <input id="submit-post-type" class="button action" name="filter-post" type="submit" value="<?php esc_attr_e('Filter Posts', 'trash-duplicate-and-301-redirect') ?>">
                    </div>
                    <?php if ($pages_all > 0) { ?>
                        <div class="tablenav">
                            <div class="trash-duplicates-top-field nav-top">
                                <fieldset class="field-one">
                                    <strong><?php _e('Trash:', 'trash-duplicate-and-301-redirect') ?></strong>
                                    <label class="trash-duplicate1">
                                        <input type="checkbox" value="0" name="delete-all-duplicates" id="delete-all-duplicate">
                                        <?php _e('All', 'trash-duplicate-and-301-redirect') ?>
                                    </label>
                                </fieldset>
                                <fieldset class="field-two">
                                    <strong class="float_left"><?php _e('And Keep:', 'trash-duplicate-and-301-redirect') ?></strong>
                                    <label class="trash-duplicate1">
                                        <input type="radio" checked="checked" value="0" name="keep-all-duplicates">
                                        <?php _e('Oldest', 'trash-duplicate-and-301-redirect') ?>
                                    </label>
                                    <label class="trash-duplicate2">
                                        <input type="radio" value="1" name="keep-all-duplicates">
                                        <?php _e('Newest', 'trash-duplicate-and-301-redirect') ?>
                                    </label>
                                </fieldset>
                                <label class="margin_left_80">
                                    <input type="submit" value="<?php esc_attr_e('Apply', 'trash-duplicate-and-301-redirect') ?>" class="button action" name="duplicate-items-apply" id="duplicate-items-apply">
                                </label>
                            </div>
                            <div class="pagination-wrapper">
                                <div class="tablenav-pages float_right">
                                    <span class="pagination-links">
                                        <?php if ($page == '1') { ?>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>
                                        <?php } else { ?>
                                            <a class="first-page" href="<?php echo admin_url('?page=trash_duplicates&number=1&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>" title="<?php esc_attr_e('Go to the first page', 'trash-duplicate-and-301-redirect'); ?>">&laquo;</a>
                                            <a class="prev-page" href="<?php echo admin_url('?page=trash_duplicates&number=' . ($page - 1) . '&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>" title="<?php esc_attr_e('Go to the previous page', 'trash-duplicate-and-301-redirect'); ?>">&lsaquo;</a>
                                        <?php } ?>
                                        <span class="paging-input">
                                            <input class="current-page" type="text" size="1" value="<?php echo $page; ?>" name="number" title="<?php _e("Current page", 'trash-duplicate-and-301-redirect'); ?>">
                                            <?php _e('of', 'trash-duplicate-and-301-redirect'); ?>
                                            <span class="total-pages"><?php echo $pages_all; ?></span>
                                        </span>
                                        <?php if ($page == $pages_all) { ?>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>
                                        <?php } else {
                                            ?>
                                            <a class="next-page " href="<?php echo admin_url('?page=trash_duplicates&number=' . ($page + 1) . '&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>">&rsaquo;</a>
                                            <a class="last-page " href="<?php echo admin_url('?page=trash_duplicates&number=' . $pages_all . '&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>" title="<?php esc_attr_e('Go to the last page', 'trash-duplicate-and-301-redirect'); ?>">&raquo;</a>
                                        <?php } ?>
                                    </span>
                                </div>
                                <?php if (isset($trash_count_result) && $trash_count_result > 0) { ?>
                                    <div class="total_duplicate_post_count top">
                                        <?php
                                        echo $trash_count_result . ' ' . __('Items', 'trash-duplicate-and-301-redirect');
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php } ?>
                    <div class="trash-inner-table">
                        <table cellspacing="0" class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th class="check-col" id="chk-col"><input type="checkbox" name="chk_remove_all" id="chk_remove_all"></th>
                                    <th class="col-title" id="title"><?php _e('Title', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-post-id" id="post-id"><?php _e('ID', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-author" id="author"><?php _e('Author', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-categories" id="categories"><?php _e('Categories', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-comment num" id="comments"><span class="vers comment-grey-bubble"></span></th>
                                    <th class="col-date" id="date"><?php _e('Date', 'trash-duplicate-and-301-redirect') ?></th>
                                </tr>
                            </thead>
                            <tbody class="trash_group">
                                <?php
                                $key_z = $key_y = $cntkey = $var_key = '';
                                foreach ($trash_final_result as $key => $value) :
                                    if ($key != 0) {
                                        $key_z = trim(strtolower($trash_final_result[$key - 1]['post_title'])) . '_' . trim(strtolower($trash_final_result[$key - 1]['post_type'])). '_' . trim(strtolower($trash_final_result[$key - 1]['post_author']));
                                    }
                                    if ($key_z != trim(strtolower($value['post_title'])) . '_' . trim(strtolower($value['post_type'])). '_' .trim(strtolower($value['post_author'])) ) {    
                                        ?>
                                        <tr id="trash_group_ind_<?php echo $group + 1; ?>" data-groupid="<?php echo $group + 1; ?>" class="trash-duplicates-row trash_post_expand">
                                            <th id="chk-ind" class="check-column">
                                                <input type="hidden" class="select_posts_hidden" value="" name="select_posts_hidden[]" />
                                            </th>
                                            <th>
                                                <strong><?php echo $value['post_title']; ?></strong>
                                                <?php
                                                // Looping through the results.
                                                $count_ind = 0;
                                                $trash_duplicates_ary = array();
                                                foreach ($trash_final_result as $key_ind => $value_ind) {
                                                    $str1 = trim(strtolower($trash_final_result[$key_ind]['post_title'])) . '_' . trim(strtolower($trash_final_result[$key_ind]['post_type']));
                                                    $str2 = trim(strtolower($value['post_title'])) . '_' . trim(strtolower($value['post_type']));
                                                    if ($str1 == $str2) {
                                                        $trash_duplicates_ary[] = $trash_final_result[$key_ind]['ID'];
                                                        $count_ind++;
                                                    }
                                                }
                                                $trash_duplicates_str = implode(",", $trash_duplicates_ary);
                                                echo '<span class="trash_post_total">[ ' . $count_ind . ' posts ]</span>';
                                                ?>
                                                <input type="hidden" name="get_group_id_<?php echo $group; ?>" value="<?php echo $trash_duplicates_str; ?>" />
                                            </th>
                                            <th>
                                            </th>
                                            <th>
                                                <strong><?php _e('Keep:', 'trash-duplicate-and-301-redirect') ?></strong>
                                                <label class="trash-duplicate1">
                                                    <input type="radio" id="duplicate_old_keep_<?php echo $group; ?>" name="duplicate_keep[<?php echo $group; ?>]" class="margin_left_0" value="0" checked="checked" />
                                                    <?php _e('Oldest', 'trash-duplicate-and-301-redirect') ?>
                                                </label>
                                            </th>
                                            <th>
                                                <strong><?php _e('Keep:', 'trash-duplicate-and-301-redirect'); ?></strong>
                                                <label class="trash-duplicate2">
                                                    <input type="radio" id="duplicate_new_keep_<?php echo $group; ?>" name="duplicate_keep[<?php echo $group; ?>]" class="margin_left_0" value="1" />
                                                    <?php _e('Newest', 'trash-duplicate-and-301-redirect') ?>
                                                </label>
                                            </th>
                                            <th>
                                                <label>
                                                    <input type="submit" id="duplicate_apply_<?php echo $group; ?>" name="duplicate_apply_<?php echo $group; ?>" class="button-secondary action" value="<?php esc_attr_e('Apply', 'trash-duplicate-and-301-redirect') ?>"/>
                                                </label>
                                            </th>
                                            <th class="down-icon-wrap">
                                                <a>
                                                    <img src="<?php echo plugins_url('/images/down.png', __FILE__) ?>" alt="Down" />
                                                </a>
                                            </th>
                                        </tr>
                                        <?php
                                        $group++;
                                    };
                                    ?>
                                    <tr class="trash_ind_group_<?php echo $group; ?> tdrd_post_group" data-rowid="<?php echo $group; ?>">
                                        <th class="check-column padding_top_20" id="chk-ind">
                                            <input type="checkbox" class="chk_box" name="chk_remove_sel[]" value="<?php echo $value['ID']; ?>">
                                        </th>
                                        <td>
                                            <strong class="post-title">
                                                <a href="post.php?post=<?php echo $value['ID']; ?>&action=edit" >
                                                    <?php echo $value['post_title']; ?>
                                                </a>
                                                <?php
                                                $st = get_post_status($value['ID']);
                                                ?>
                                                <?php echo $st == 'draft' ? '<span class="post-state"> (' . __('Draft', 'trash-duplicate-and-301-redirect') . ')</span>' : '' ?>

                                            </strong>
                                            <div class="row-actions">
                                                <span class="view">
                                                    <a class="view" href="<?php echo get_permalink(absint($value['ID'])); ?>" rel="permalinks">
                                                        <?php _e('View', 'trash-duplicate-and-301-redirect') ?> |
                                                    </a>
                                                </span>
                                                <span class="trash">
                                                    <a class="trash_red" href="<?php echo esc_url(add_query_arg(array('action' => 'trashing', 'trash-id' => absint($value['ID'])))); ?>">
                                                        <?php _e('Trash', 'trash-duplicate-and-301-redirect') ?>
                                                    </a>
                                                </span>
                                            </div>
                                        </td>
                                        <td><?php echo $value['ID']; ?></td>
                                        <td><?php the_author_meta('display_name', $value['post_author']); ?></td>
                                        <td>
                                            <?php
                                            if($value['post_type'] != 'page' && $value['post_type'] != 'attachment'){
                                                if (get_post_type($value['ID']) == 'product') {
                                                    $category = get_the_terms($value['ID'], 'product_cat');
                                                    if ($category) {
                                                        foreach ($category as $cat) {
                                                            echo '<a href="' . esc_url('edit.php?product_cat=' . $cat->slug) . '&post_type=product">' . esc_html($cat->name) . '</a><strong class="td_rd_cat_sep">,&nbsp;</strong>';
                                                        }
                                                    } else {
                                                        echo '<a href="edit.php?product_cat=uncategorized&post_type=product">' . __('Uncategorized', 'trash-duplicate-and-301-redirect') . '</a><strong class="td_rd_cat_sep">,&nbsp;</strong>';
                                                    }
                                                } else {
                                                    $trash_duplicates_category = wp_get_post_categories($value['ID']);
                                                    if ($trash_duplicates_category) :
                                                        $cats = array();
                                                        foreach ($trash_duplicates_category as $c) :
                                                            $cat = get_category($c);
                                                            $cats[] = array('name' => $cat->name, 'slug' => $cat->slug);
                                                        endforeach;
                                                        foreach ($cats as $c1) :
                                                            echo '<a href="' . esc_url('edit.php?category_name=' . $c1['slug']) . '">' . esc_html($c1['name']) . '</a><strong class="td_rd_cat_sep">,&nbsp;</strong>';
                                                        endforeach;
                                                    else :
                                                        echo '<a href="edit.php?category_name=uncategorized">' . __('Uncategorized', 'trash-duplicate-and-301-redirect') . '</a><strong class="td_rd_cat_sep">,&nbsp;</strong>';
                                                    endif;
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            if ($value['comment_count'] > 0)
                                                echo $value['comment_count'];
                                            else
                                                echo "—";
                                            ?>
                                        </td>
                                        <td><?php
                                            $st = get_post_status($value['ID']);
                                            $st_string = '';
                                            if ($st == 'draft') {
                                                $publish_date = get_the_modified_date('U', $value['ID']);
                                                $st_string = __('Last Modified', 'trash-duplicate-and-301-redirect');
                                            } else {
                                                $publish_date = get_post_time('U', true, $value['ID'], false);
                                                $st_string = __('Published', 'trash-duplicate-and-301-redirect');
                                            }
                                            if (!empty($publish_date)) {
                                                ?>
                                                <span class="tdrd_published_label"><?php echo $st_string; ?></span>
                                                <span class="tdrd_published_dt">
                                                    <?php
                                                    $date_format = get_option('date_format');
                                                    $date = date($date_format, $publish_date);
                                                    $time_format = get_option('time_format');
                                                    $time = date($time_format, $publish_date);
                                                    echo $date . ' ' . $time;
                                                    ?> </span> <?php
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php
                                    $cntkey = $key + 1;
                                    if ($cntkey < count($trash_final_result)) {
                                        $var_key = trim(strtolower($trash_final_result[$key + 1]['post_title']));
                                    }
                                endforeach;
                                ?>
                                <?php if (isset($trash_count_result) && $trash_count_result == 0) { ?>
                                    <tr>
                                        <td colspan="7"><?php _e('Nothing Found.', 'trash-duplicate-and-301-redirect'); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="check-col" id="chk-col"><input type="checkbox" name="chk_remove_all" id="chk_remove_all2"></th>
                                    <th class="col-title" id="title"><?php _e('Title', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-post-id" id="post-id"><?php _e('ID', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-author" id="author"><?php _e('Author', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-categories" id="categories"><?php _e('Categories', 'trash-duplicate-and-301-redirect') ?></th>
                                    <th class="col-comment num" id="comments"><span class="vers comment-grey-bubble"></span></th>
                                    <th class="col-date" id="date"><?php _e('Date', 'trash-duplicate-and-301-redirect') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <?php if ($pages_all > 0) { ?>
                        <div class="trash-inner-bottom">
                            <div class="tablenav">
                                <select name="duplicates-action-top2">
                                    <option selected="selected" value="none">
                                        <?php _e('Bulk Actions', 'trash-duplicate-and-301-redirect') ?>
                                    </option>
                                    <option value="trash"><?php _e('Move To Trash', 'trash-duplicate-and-301-redirect') ?></option>
                                    <option value="delete_pr">
                                        <?php _e('Delete Permanently', 'trash-duplicate-and-301-redirect') ?>
                                    </option>
                                </select>
                                <input id="take_action2" class="button action" name="take_action2" type="submit" value="<?php esc_attr_e('Apply', 'trash-duplicate-and-301-redirect') ?>" >
                                <div class="tablenav-pages float_right">
                                    <span class="pagination-links">
                                        <?php if ($page == '1') { ?>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>
                                        <?php } else { ?>
                                            <a class="first-page" href="<?php echo admin_url('?page=trash_duplicates&number=1&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>" title="<?php esc_attr_e('Go to the first page', 'trash-duplicate-and-301-redirect'); ?>">&laquo;</a>
                                            <a class="prev-page" href="<?php echo admin_url('?page=trash_duplicates&number=' . ($page - 1) . '&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>" title="<?php esc_attr_e('Go to the previous page', 'trash-duplicate-and-301-redirect'); ?>">&lsaquo;</a>
                                        <?php } ?>
                                        <span class="paging-input">
                                            <span class="bottom-current-page"><?php echo $page; ?></span>
                                            <?php _e('of', 'trash-duplicate-and-301-redirect'); ?>
                                            <span class="total-pages"><?php echo $pages_all; ?></span>
                                        </span>
                                        <?php if ($page == $pages_all) { ?>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>
                                            <span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>
                                        <?php } else {
                                            ?>
                                            <a class="next-page " href="<?php echo admin_url('?page=trash_duplicates&number=' . ($page + 1) . '&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>">&rsaquo;</a>
                                            <a class="last-page " href="<?php echo admin_url('?page=trash_duplicates&number=' . $pages_all . '&trash-post-types=' . $post_type . '&show-draft=' . $show_drafts . '&tra-search-input=' . $trash_duplicates_search_query); ?>" title="<?php esc_attr_e('Go to the last page', 'trash-duplicate-and-301-redirect'); ?>">&raquo;</a>
                                        <?php } ?>
                                    </span>
                                </div>
                                <?php if (isset($trash_count_result) && $trash_count_result > 0) { ?>
                                    <div class="total_duplicate_post_count">
                                        <?php
                                        echo $trash_count_result . ' ' . __('Items', 'trash-duplicate-and-301-redirect');
                                        ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div><?php
                    }
                    wp_nonce_field('trash_duplicates_main_form_nonce', plugin_basename(__FILE__), true);
                    ?>
                </form>
            </div>
            <?php tdrd_advertisement_sidebar(); ?>
        </div><?php
    }

}

if(!function_exists('tdrd_admin_welcome_func')) {
    function tdrd_admin_welcome_func() {
        global $wpdb;
        $tdrd_admin_email = get_option('admin_email');
        ?>
        <div class='tdrd_header_wizard'>
            <p><?php echo esc_attr(__('Hi there!', 'trash-duplicate-and-301-redirect')); ?></p>
            <p><?php echo esc_attr(__("Don't ever miss an opportunity to opt in for Email Notifications / Announcements about exciting New Features and Update Releases.", 'trash-duplicate-and-301-redirect')); ?></p>
            <p><?php echo esc_attr(__('Contribute in helping us making our plugin compatible with most plugins and themes by allowing to share non-sensitive information about your website.', 'trash-duplicate-and-301-redirect')); ?></p>
            <p><b><?php echo esc_attr(__('Email Address for Notifications', 'trash-duplicate-and-301-redirect')); ?> :</b></p>
            <p><input type='email' value='<?php echo $tdrd_admin_email; ?>' id='tdrd_admin_email' /></p>
            <p><?php echo esc_attr(__("If you're not ready to Opt-In, that's ok too!", 'trash-duplicate-and-301-redirect')); ?></p>
            <p><b><?php echo esc_attr(__('Trash Duplicates and 301 redirects will still work fine.', 'trash-duplicate-and-301-redirect')); ?> :</b></p>
            <p onclick="tdrd_show_hide_permission()" class='tdrd_permission'><b><?php echo esc_attr(__('What permissions are being granted?', 'trash-duplicate-and-301-redirect')); ?></b></p>
            <div class='tdrd_permission_cover' style='display:none'>
                <div class='tdrd_permission_row'>
                    <div class='tdrd_50'>
                        <i class='dashicons dashicons-admin-users gb-dashicons-admin-users'></i>
                        <div class='tdrd_50_inner'>
                            <label><?php echo esc_attr(__('User Details', 'trash-duplicate-and-301-redirect')); ?></label>
                            <label><?php echo esc_attr(__('Name and Email Address', 'trash-duplicate-and-301-redirect')); ?></label>
                        </div>
                    </div>
                    <div class='tdrd_50'>
                        <i class='dashicons dashicons-admin-plugins gb-dashicons-admin-plugins'></i>
                        <div class='tdrd_50_inner'>
                            <label><?php echo esc_attr(__('Current Plugin Status', 'trash-duplicate-and-301-redirect')); ?></label>
                            <label><?php echo esc_attr(__('Activation, Deactivation and Uninstall', 'trash-duplicate-and-301-redirect')); ?></label>
                        </div>
                    </div>
                </div>
                <div class='tdrd_permission_row'>
                    <div class='tdrd_50'>
                        <i class='dashicons dashicons-testimonial gb-dashicons-testimonial'></i>
                        <div class='tdrd_50_inner'>
                            <label><?php echo esc_attr(__('Notifications', 'trash-duplicate-and-301-redirect')); ?></label>
                            <label><?php echo esc_attr(__('Updates & Announcements', 'trash-duplicate-and-301-redirect')); ?></label>
                        </div>
                    </div>
                    <div class='tdrd_50'>
                        <i class='dashicons dashicons-welcome-view-site gb-dashicons-welcome-view-site'></i>
                        <div class='tdrd_50_inner'>
                            <label><?php echo esc_attr(__('Website Overview', 'trash-duplicate-and-301-redirect')); ?></label>
                            <label><?php echo esc_attr(__('Site URL, WP Version, PHP Info, Plugins & Themes Info', 'trash-duplicate-and-301-redirect')); ?></label>
                        </div>
                    </div>
                </div>
            </div>
            <p>
                <input type='checkbox' class='tdrd_agree' id='tdrd_agree_gdpr' value='1' />
                <label for='tdrd_agree_gdpr' class='tdrd_agree_gdpr_lbl'><?php echo esc_attr(__('By clicking this button, you agree with the storage and handling of your data as mentioned above by this website. (GDPR Compliance)', 'trash-duplicate-and-301-redirect')); ?></label>
            </p>
            <p class='tdrd_buttons'>
                <a href="javascript:void(0)" class='button button-secondary' onclick="tdrd_submit_optin('cancel')"><?php echo esc_attr(__('Skip &amp; Continue', 'trash-duplicate-and-301-redirect')); ?></a>
                <a href="javascript:void(0)" class='button button-primary' onclick="tdrd_submit_optin('submit')"><?php echo esc_attr(__('Opt-In &amp; Continue', 'trash-duplicate-and-301-redirect')); ?></a>
            </p>
        </div>
        <?php
    }
}

/**
 * @param type $trash_ind_id
 * @return html messages for duplicate entries
 */
if (!function_exists('tdrd_individual')) {

    function tdrd_individual($trash_ind_id) {
        if (wp_trash_post($trash_ind_id) === FALSE) :
            $location = admin_url('admin.php?page=trash_duplicates');
            wp_safe_redirect($location);
            ?>
            <div class="error notice is-dismissible below-h1" id="message">
                <p>
                    <strong>
                        <?php _e('Error:', 'trash-duplicate-and-301-redirect') ?>
                    </strong>
                    <?php _e('While moving following post to the trash.', 'trash-duplicate-and-301-redirect') ?>(<?php echo $trash_ind_id; ?>)
                    <button class="notice-dismiss" type="button">
                        <span class="screen-reader-text">
                            <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?>
                        </span>
                    </button>
            </div><?php
        else : 
            $location = admin_url('admin.php?page=trash_duplicates');
            wp_safe_redirect($location);    ?>
            <div class="updated notice is-dismissible below-h1" id="message">
                <p>
                    <strong><?php _e('Success:', 'trash-duplicate-and-301-redirect') ?></strong>
                    <?php _e(' Following post moved to the trash.', 'trash-duplicate-and-301-redirect') ?>(<?php echo $trash_ind_id; ?>)
                    <button class="notice-dismiss" type="button">
                        <span class="screen-reader-text">
                            <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?>
                        </span>
                    </button>
            </div><?php
        endif;
    }

}

/**
 *
 * @global type $wpdb
 * @param type $trash_sel_id selected duplicate entries
 */
if (!function_exists('tdrd_selected')) {

    function tdrd_selected($trash_sel_id) {
        $trash_del_action = '';
        global $wpdb;
        $tbl_nm = $wpdb->prefix . 'posts';
        if (isset($_REQUEST['duplicates-action-top']) && esc_html($_REQUEST['duplicates-action-top']) != 'none') {
            $trash_del_action = esc_html($_REQUEST['duplicates-action-top']);
        } else {
            if (isset($_REQUEST['duplicates-action-top2'])) {
                $trash_del_action = esc_html($_REQUEST['duplicates-action-top2']);
            }
        }
        if ($trash_sel_id) :
            $remove_id = (array) $_REQUEST['chk_remove_sel'];
            $count = count($remove_id);
            $removed_items = array();
            if ($trash_del_action == 'trash') {
                for ($i = 0; $i < $count; $i++) :

                    wp_trash_post($remove_id[$i]);
                    $removed_items[] = $remove_id[$i];
                endfor;

                if ($removed_items) :
                    $trashed_items = implode(",", $removed_items);
                    ?>
                    <div class="updated notice is-dismissible below-h1" id="message">
                        <p>
                            <strong><?php _e('Success:', 'trash-duplicate-and-301-redirect') ?></strong>
                            <?php _e(' Following post moved to the trash.', 'trash-duplicate-and-301-redirect') ?>(<?php echo $trashed_items; ?>)
                            <button class="notice-dismiss" type="button">
                                <span class="screen-reader-text">
                                    <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?>
                                </span>
                            </button>
                    </div><?php
                endif;
            }
            else if ($trash_del_action == 'delete_pr') {
                for ($i = 0; $i < $count; $i++) :
                    $del_permanent = $remove_id[$i];

                    $wpdb->delete($tbl_nm, array('ID' => $del_permanent));
                    $removed_items[] = $remove_id[$i];
                endfor;

                if ($removed_items) :
                    $trashed_items = implode(",", $removed_items);
                    ?>
                    <div class="updated notice is-dismissible below-h1" id="message">
                        <p>
                            <strong><?php _e('Success:', 'trash-duplicate-and-301-redirect') ?></strong>
                            <?php _e(' Following posts deleted.', 'trash-duplicate-and-301-redirect') ?>(<?php echo $trashed_items; ?>)
                            <button class="notice-dismiss" type="button">
                                <span class="screen-reader-text">
                                    <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?>
                                </span>
                            </button>
                    </div><?php
                endif;
            }
        endif;
    }

}

/**
 *
 * @global type $wpdb
 * Use for move all duplicate post in trash.
 */
if (!function_exists('tdrd_trash_all')) {

    function tdrd_trash_all() {
        global $wpdb;
        $success = $error = 0;
        $in_tbl = $wpdb->prefix . 'tdrd_redirection';
        $old_url = $new_url = $keep_uri = "";
        $post_type_string = "";
        if (isset($_REQUEST['duplicate-items-apply']) && isset($_REQUEST['delete-all-duplicates'])) {

            $var = absint($_REQUEST['keep-all-duplicates']);
            $min_max = '';
            if ($var == 1) {
                $min_max = 'trash_oldest';
            } else {
                $min_max = 'trash_newest';
            }

            $postIds = array();
            $postIds = (array) $_REQUEST['select_posts_hidden'];

            $groupArr = array();
            $postIds = array_filter($postIds);
            foreach ($postIds as $postid) {
                $groupArr[] = array_filter(explode(',', $postid));
            }
            foreach ($groupArr as $k => $v) {
                if (count($v) < 2) {
                    unset($groupArr[$k]);
                }
            }
            switch ($min_max) {
                case "trash_oldest":
                    foreach ($groupArr as $val) {
                        $oldest = $newest = array();
                        $publishDt = array();
                        foreach ($val as $postid) {
                            $st = get_post_status($postid);
                            if ($st == 'draft') {
                                $publishDt[$postid] = get_the_modified_date('U', $postid);
                            } else {
                                $publishDt[$postid] = get_post_time('U', true, $postid, false);
                            }
                        }
                        asort($publishDt);
                        $n = 0;
                        $array_keys = array_keys($publishDt);
                        $newest = end($array_keys);
                        foreach ($publishDt as $k => $v) {
                            if ($k != $newest) {
                                $oldest[$n] = $k;
                            }
                            $n++;
                        }
                        foreach ($oldest as $pid) {
                            $st = get_post_status($pid);
                            if ($st == 'draft') {
                                $ids[] = $pid;
                                $success = 1;
                                wp_trash_post($pid);
                            } else {
                                $title = get_the_title($pid);
                                $post_type = get_post_type($pid);
                                $post_permalink = get_permalink($pid);
                                $new_uri = get_permalink($newest);
                                $home = home_url();
                                $old_url = str_replace($home, '', $post_permalink);
                                $ids[] = $pid;
                                if($st == 'publish'){
                                    if ($wpdb->insert(
                                        $in_tbl, array(
                                    'old_url' => sanitize_text_field($old_url),
                                    'new_url' => sanitize_text_field($new_uri),
                                    'date_time' => current_time('mysql', 1)
                                        )
                                            ) === FALSE) {
                                        $error = 1;
                                    } else {
                                        $success = 1;
                                        wp_trash_post($pid);
                                    }
                                }
                                
                            }
                        }
                        
                    }
                    break;
                case "trash_newest":
                    foreach ($groupArr as $val) {
                        $oldest = $newest = array();
                        $publishDt = array();
                        foreach ($val as $postid) {
                            $st = get_post_status($postid);
                            if ($st == 'draft') {
                                $publishDt[$postid] = get_the_modified_date('U', $postid);
                            } else {
                                $publishDt[$postid] = get_post_time('U', true, $postid, false);
                            }
                        }
                        asort($publishDt);
                        $n = 0;
                        $array_keys = array_keys($publishDt);
                        $oldest = $array_keys[0];
                        foreach ($publishDt as $k => $v) {
                            if ($k != $oldest) {
                                $newest[$n] = $k;
                                $n++;
                            }
                        }
                        foreach ($newest as $pid) {
                            $st = get_post_status($pid);
                            if ($st == 'draft') {
                                $ids[] = $pid;
                                $success = 1;
                                wp_trash_post($pid);
                            } else {
                                $title = get_the_title($pid);
                                $post_type = get_post_type($pid);
                                $post_permalink = get_permalink($pid);
                                $new_uri = get_permalink($oldest);
                                $home = home_url();
                                $old_url = str_replace($home, '', $post_permalink);
                                $ids[] = $pid;
                                if($st == 'publish'){
                                    if ($wpdb->insert(
                                        $in_tbl, array(
                                    'old_url' => sanitize_text_field($old_url),
                                    'new_url' => sanitize_text_field($new_uri),
                                    'date_time' => current_time('mysql', 1)
                                        )
                                            ) === FALSE) {
                                        $error = 1;
                                    } else {
                                        $success = 1;
                                        wp_trash_post($pid);
                                    }
                                }
                            }
                        }
                    }
                    break;
                default:
                    echo "";
            }
            if ($success == 1) {
                $success = 0;
                ?>
                <div class="updated notice is-dismissible below-h1" id="message">
                    <p>
                        <?php
                        _e('Following duplicate posts successfully moved to the trash', 'trash-duplicate-and-301-redirect');
                        echo ' : ' . implode(',', $ids);
                        ?>
                        <button class="notice-dismiss" type="button">
                            <span class="screen-reader-text">
                                <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?>
                            </span>
                        </button>
                </div><?php
            } else {
                $error = 0;
                ?>
                <div class="error notice is-dismissible below-h1" id="message">
                    <p>
                        <strong><?php _e('Error:', 'trash-duplicate-and-301-redirect') ?></strong>
                        <?php _e(" Can't move to the trash.", "trash-duplicates"); ?>
                        <button class="notice-dismiss" type="button">
                            <span class="screen-reader-text">
                                <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect'); ?>
                            </span>
                        </button>
                </div><?php
            }
            // delete all duplicates from the db
        } else {
            global $wpdb;
            $in_tbl = $wpdb->prefix . 'tdrd_redirection';
            foreach ($_POST as $key => $value) {
                if (strspn($key, 'duplicate_apply_') == 16) {
                    $group_key = $key;
                    $int_num = filter_var($group_key, FILTER_SANITIZE_NUMBER_INT);
                    $keep_type = (array) $_REQUEST['duplicate_keep'];
                    $keep_type_val = $keep_type[$int_num];
                    $group_str = "get_group_id_" . $int_num . "";

                    $groupArr = array();
                    $groupArr = explode(",", $_REQUEST[$group_str]);

                    if ($keep_type_val == 1) { // newest
                        $oldest = $newest = array();
                        $publishDt = array();
                        foreach ($groupArr as $postid) {
                            $st = get_post_status($postid);
                            if ($st == 'draft') {
                                $publishDt[$postid] = get_the_modified_date('U', $postid);
                            } else {
                                $publishDt[$postid] = get_post_time('U', true, $postid, false);
                            }
                        }
                        asort($publishDt);
                        $n = 0;
                        $array_keys = array_keys($publishDt);
                        $newest = end($array_keys);
                        foreach ($publishDt as $k => $v) {
                            if ($k != $newest) {
                                $oldest[$n] = $k;
                            }
                            $n++;
                        }
                        foreach ($oldest as $pid) {
                            $st = get_post_status($pid);
                            if ($st == 'draft') {
                                $ids[] = $pid;
                                $success = 1;
                                wp_trash_post($pid);
                            } else {
                                $title = get_the_title($pid);
                                $post_type = get_post_type($pid);
                                $post_permalink = get_permalink($pid);
                                $new_uri = get_permalink($newest);
                                $home = home_url();
                                $old_url = str_replace($home, '', $post_permalink);
                                $ids[] = $pid;
                                if ($wpdb->insert(
                                                $in_tbl, array(
                                            'old_url' => sanitize_text_field($old_url),
                                            'new_url' => sanitize_text_field($new_uri),
                                            'date_time' => current_time('mysql', 1)
                                                )
                                        ) === FALSE) {
                                    $error = 1;
                                } else {
                                    $success = 1;
                                    wp_trash_post($pid);
                                }
                            }
                        }
                    } else {
                        $oldest = $newest = array();
                        $publishDt = array();
                        foreach ($groupArr as $postid) {
                            $st = get_post_status($postid);
                            if ($st == 'draft') {
                                $publishDt[$postid] = get_the_modified_date('U', $postid);
                            } else {
                                $publishDt[$postid] = get_post_time('U', true, $postid, false);
                            }
                        }
                        asort($publishDt);
                        $n = 0;
                        $array_keys = array_keys($publishDt);
                        $oldest = $array_keys[0];
                        foreach ($publishDt as $k => $v) {
                            if ($k != $oldest) {
                                $newest[$n] = $k;
                            }
                            $n++;
                        }

                        foreach ($newest as $pid) {
                            $st = get_post_status($pid);
                            if ($st == 'draft') {
                                $ids[] = $pid;
                                $success = 1;
                                wp_trash_post($pid);
                            } else {
                                $title = get_the_title($pid);
                                $post_type = get_post_type($pid);
                                $post_permalink = get_permalink($pid);
                                $new_uri = get_permalink($oldest);
                                $home = home_url();
                                $old_url = str_replace($home, '', $post_permalink);
                                $ids[] = $pid;

                                if ($wpdb->insert(
                                                $in_tbl, array(
                                            'old_url' => sanitize_text_field($old_url),
                                            'new_url' => sanitize_text_field($new_uri),
                                            'date_time' => current_time('mysql', 1)
                                                )
                                        ) === FALSE) {
                                    $error = 1;
                                } else {
                                    $success = 1;
                                    wp_trash_post($pid);
                                }
                            }
                        }
                    }
                    if ($success == 1) {
                        $success = 0;
                        ?>
                        <div class="updated notice is-dismissible below-h1" id="message">
                            <p>
                                <?php
                                _e('Following duplicate posts successfully moved to the trash', 'trash-duplicate-and-301-redirect');
                                echo ' : ' . implode(',', $ids);
                                ?>
                                <button class="notice-dismiss" type="button">
                                    <span class="screen-reader-text">
                                        <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect') ?>
                                    </span>
                                </button>
                        </div><?php
                    } else {
                        $error = 0;
                        ?>
                        <div class="error notice is-dismissible below-h1" id="message">
                            <p>
                                <strong><?php _e('Error:', 'trash-duplicate-and-301-redirect') ?></strong>
                                <?php _e(" Can't move to the trash.", "trash-duplicates"); ?>
                                <button class="notice-dismiss" type="button">
                                    <span class="screen-reader-text">
                                        <?php _e('Dismiss this notice.', 'trash-duplicate-and-301-redirect'); ?>
                                    </span>
                                </button>
                        </div><?php
                    }
                }
            }
        }
    }

}

add_action('admin_init','tdrd_trash_pages');
if(!function_exists('tdrd_trash_pages')){
    function tdrd_trash_pages(){
        // To trash multiple posts from DB
        if (( isset($_REQUEST['take_action']) && esc_html($_REQUEST['duplicates-action-top']) != 'none') || (isset($_REQUEST['take_action2']) && esc_html($_REQUEST['duplicates-action-top2']) != 'none')) {
            if (isset($_REQUEST['chk_remove_sel'])) {
                tdrd_selected($_REQUEST['chk_remove_sel']);
            }
        }
        // Trashing individual items.
        if (isset($_REQUEST['trash-id']) && esc_html($_REQUEST['action']) == 'trashing') {
            tdrd_individual(absint($_REQUEST['trash-id']));
        }
        // Trashing all items.
        if (count($_REQUEST)) {
            tdrd_trash_all();
        }
    }
}