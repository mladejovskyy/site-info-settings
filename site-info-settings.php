<?php

/*
 * Plugin Name: Site info settings
 * Description: Adds custom settings for your site (company name, support email, maintenance mode).
 * Version: 1.0
 * Author: Tomas Mladejovsky
 */

if(!defined('ABSPATH')) exit;

// Include separate files
require_once plugin_dir_path(__FILE__) . 'includes/settings-register.php';;
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';;
require_once plugin_dir_path(__FILE__) . 'includes/orders-cpt.php';;

add_action('wp_footer', function () {
    if (is_admin()) return; // Don’t show this in admin

    if (get_option('sis_maintenance_mode')) {
        echo '<div style="position:fixed;bottom:0;left:0;right:0;background:red;color:white;text-align:center;padding:10px;z-index:9999;">
            Maintenance Mode is ON
            <p style="font-size:14px;margin-top:5px;">
                For support please contact <a href="mailto:' . get_option('sis_support_email') . '">' . get_option('sis_support_email') . '</a>
            </p>
        </div>';
    }
});