<?php

add_action('admin_init', function () {
    register_setting('sis_settings_group', 'sis_company_name', [
        'sanitize_callback' => 'sanitize_text_field'
    ]);

    register_setting('sis_settings_group', 'sis_support_email', [
        'sanitize_callback' => 'sanitize_email'
    ]);

    register_setting('sis_settings_group', 'sis_maintenance_mode'); // We'll handle sanitization manually if needed
});