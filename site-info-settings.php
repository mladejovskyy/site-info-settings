<?php

/*
 * Plugin Name: Site info settings
 * Description: Phase 1 Challenge - Adds custom settings for your site (company name, support email, maintenance mode).
 * Version: 1.0
 * Author: Tomas Mladejovsky
 */

if(!defined('ABSPATH')) exit;

add_action('admin_init', function () {
    register_setting('sis_settings_group', 'sis_company_name', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    register_setting('sis_settings_group', 'sis_support_email', [
        'sanitize_callback' => 'sanitize_email'
    ]);

    register_setting('sis_settings_group', 'sis_maintenance_mode'); // We'll handle sanitization manually if needed
});

add_action('admin_menu', function () {
    add_menu_page(
        'Site info settings',
        'Site info settings',
        'manage_options',
        'site-info-settings',
        'sis_settings_page_html',
        'dashicons-admin-generic'
    );
});

function sis_settings_page_html() {
    $company = get_option('sis_company_name');
    $email = get_option('sis_support_email');
    $maintenance = get_option('sis_maintenance_mode');
    ?>
    <div class="site_info_settings">
        <h1>Site Info Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('sis_settings_group'); ?>

            <label>Company Name:</label><br>
            <input type="text" name="sis_company_name" value="<?php echo esc_attr($company); ?>"><br><br>

            <label>Support Email:</label><br>
            <input type="email" name="sis_support_email" value="<?php echo esc_attr($email); ?>"><br><br>

            <label>
                <input type="checkbox" name="sis_maintenance_mode" value="1" <?php checked($maintenance, 1); ?>>
                Enable Maintenance Mode
            </label><br><br>

            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}


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