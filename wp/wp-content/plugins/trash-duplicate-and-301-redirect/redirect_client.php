<?php

/*
 * Exit if accessed directly
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @return url of current page
 */
if (!function_exists('tdrd_get_address')) {

    function tdrd_get_address() {
        return tdrd_get_protocol() . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

}

/**
 * @return protocol of current url.
 */
if (!function_exists('tdrd_get_protocol')) {

    function tdrd_get_protocol() {
        $protocol = 'http';
        if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on") {
            $protocol .= "s";
        }
        return $protocol;
    }

}

/**
 * @return redirection.
 */
add_action('init', 'tdrd_redirect_me', 1);
if (!function_exists('tdrd_redirect_me')) {

    function tdrd_redirect_me() {
        $user_request = str_ireplace(get_option('home'), '', tdrd_get_address());
        $user_request = rtrim($user_request, '/');
        $do_redirect = '';
        global $wpdb;
        $tabel_name = $wpdb->prefix . 'tdrd_redirection';
        $select_url_data = "SELECT * FROM $tabel_name";
        $stored_url_resultset = $wpdb->get_results($select_url_data, ARRAY_A);
        foreach ($stored_url_resultset as $redirect) {
            $stored = urldecode($redirect['old_url']);
            $destination = $redirect['new_url'];
            if (urldecode($user_request) == rtrim($stored, '/')) {
                // simple comparison redirect
                $do_redirect = $destination;
            }
            if ($do_redirect !== '' && trim($do_redirect, '/') !== trim($user_request, '/')) {
                if (strpos($do_redirect, '/') === 0) {
                    $do_redirect = home_url() . $do_redirect;
                }
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $do_redirect);
                exit();
            }
        }
    }

}