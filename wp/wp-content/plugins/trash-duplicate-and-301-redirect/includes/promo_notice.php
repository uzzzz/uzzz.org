<?php

add_action('plugins_loaded', 'tdrd_load_plugin');

if (!function_exists('tdrd_load_plugin')) {

    function tdrd_load_plugin() {

        $tdrd['promo_time'] = get_option('tdrd_promo_time');
        if (empty($tdrd['promo_time'])) {
            $tdrd['promo_time'] = time();
            update_option('tdrd_promo_time', $tdrd['promo_time']);
        }

        if (!empty($tdrd['promo_time']) && $tdrd['promo_time'] > 0 && $tdrd['promo_time'] < (time() - (60 * 60 * 24 * 3))) {
            add_action('admin_notices', 'tdrd_promo');
        }

        if (isset($_GET['tdrd_promo']) && (int) $_GET['tdrd_promo'] == 0) {
            update_option('tdrd_promo_time', (0 - time()));
            die('DONE');
        }
    }

}

if (!function_exists('tdrd_promo')) {

    // Show the promo
    function tdrd_promo() {
        echo '
            <script>
            jQuery(document).ready( function() {
                    (function($) {
                            $("#tdrd_promo .tdrd_promo-close").click(function(){
                                    var data;
                                    // Hide it
                                    $("#tdrd_promo").hide();

                                    // Save this preference
                                    $.post("' . admin_url('?tdrd_promo=0') . '", data, function(response) {
                                    });
                            });
                    })(jQuery);
            });
            </script>
            <style>/* Promotional notice css*/
                .tdrd_button {
                    background-color: #4CAF50; /* Green */
                    border: none;
                    color: white;
                    padding: 8px 16px;
                    text-align: center;
                    text-decoration: none;
                    display: inline-block;
                    font-size: 16px;
                    margin: 4px 2px;
                    -webkit-transition-duration: 0.4s; /* Safari */
                    transition-duration: 0.4s;
                    cursor: pointer;
                }
                .tdrd_button:focus{
                    border: none;
                    color: white;
                }
                .tdrd_button1 {
                    color: white;
                    background-color: #4CAF50;
                    border:3px solid #4CAF50;
                }
                .tdrd_button1:hover {
                    box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
                    color: white;
                    border:3px solid #4CAF50;
                }
                .tdrd_button2 {
                    color: white;
                    background-color: #0085ba;
                }
                .tdrd_button2:hover {
                    box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
                    color: white;
                }
                .tdrd_button3 {
                    color: white;
                    background-color: #365899;
                }
                .tdrd_button3:hover {
                    box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
                    color: white;
                }
                .tdrd_button4 {
                    color: white;
                    background-color: rgb(66, 184, 221);
                }
                .tdrd_button4:hover {
                    box-shadow: 0 6px 8px 0 rgba(0,0,0,0.24), 0 9px 25px 0 rgba(0,0,0,0.19);
                    color: white;
                }
                .tdrd_promo-close {
                    float:right;
                    text-decoration:none;
                    margin: 5px 10px 0px 0px;
                }
                .tdrd_promo-close:hover {
                    color: red;
                }
                </style>
                <div class="notice notice-success" id="tdrd_promo" style="min-height:120px">
                        <a class="tdrd_promo-close" href="javascript:" aria-label="Dismiss this Notice">
                                <span class="dashicons dashicons-dismiss"></span> Dismiss
                        </a>
                        <img src="' . TDRD_PLUGIN_URL . 'images/logo-200.png" style="float:left; margin:10px 20px 10px 10px" width="100" />
                        <p style="font-size:16px">' . __("We are glad you like", "trash-duplicates")." <strong>".__('Trash Duplicate and 301 Redirect','trash-duplicates')." </strong> ".__("plugin and have been using it since the past few days. It is time to take the next step.", "trash-duplicates") . '</p>
                        <p>
                            <a class="tdrd_button tdrd_button1" target="_blank" href="https://codecanyon.net/item/trash-duplicate-and-301-redirect-pro-for-wordpress/20885697?ref=solwin">' . __("Upgrade to Pro", "trash-duplicates") . '</a>
                            <a class="tdrd_button tdrd_button2" target="_blank" href="https://wordpress.org/support/plugin/trash-duplicate-and-301-redirect/reviews/?filter=5">' . __("Rate it", "trash-duplicates") ." 5★'s". '</a>
                            <a class="tdrd_button tdrd_button3" target="_blank" href="https://www.facebook.com/SolwinInfotech/">' . __("Like Us on Facebook", "trash-duplicates") . '</a>
                            <a class="tdrd_button tdrd_button4" target="_blank" href="https://twitter.com/home?status=' . rawurlencode('I am using #trashduplicateand301redirect to clean my #WordPress site with duplicate entries.') . '">' . __("Tweet about Trash Duplicate and 301 Redirect", "trash-duplicates") . '</a>
                        </p>
                </div>';
    }

}