<?php

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

add_action('admin_enqueue_scripts', function ($hook) {
    // $hook looks like 'toplevel_page_site-info-settings'
    if($hook !== 'toplevel_page_site-info-settings') return; // Not your page, bail out

    // Import CSS and JS files
    wp_enqueue_style('site-info-settings-css', plugin_dir_url(__DIR__) . 'assets/css/admin.css', [], '1.0');
    wp_enqueue_script('site-info-settings-js', plugin_dir_url(__DIR__) . 'assets/js/admin.js', ['jquery'], '1.0', true);
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