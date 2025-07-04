<?php

function sis_register_orders_cpt() {
    $labels = [
        'name'               => 'Orders',
        'singular_name'      => 'Order',
        'menu_name'          => 'Orders',
        'name_admin_bar'     => 'Order',
        'add_new'            => 'Create Order',
        'add_new_item'       => 'Create New Order',
        'edit_item'          => 'Edit Order',
        'new_item'           => 'New Order',
        'view_item'          => 'View Order',
        'all_items'          => 'All Orders',
        'search_items'       => 'Search Orders',
        'not_found'          => 'No orders found',
        'not_found_in_trash' => 'No orders found in Trash',
    ];

    $args = [
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'capability_type'    => 'post',
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-cart',
        'supports'           => ['title'],
        'exclude_from_search'=> true,
        'has_archive'        => false,
    ];

    register_post_type('sis_order', $args);
}

add_action('admin_enqueue_scripts', function($hook) {
    global $post;

    // Only on Order edit screen
    if ($hook === 'post-new.php' || $hook === 'post.php') {
        if (isset($post) && $post->post_type === 'sis_order') {
            wp_enqueue_style('sis-order-admin', plugin_dir_url(__FILE__) . '../assets/css/orders.css');
        }
    }
});


add_action('add_meta_boxes', 'sis_add_order_metaboxes');

function sis_add_order_metaboxes() {
    add_meta_box(
        'sis_order_details',
        'Order Details',
        'sis_order_details_callback',
        'sis_order',
        'normal',
        'default'
    );
};

function sis_order_details_callback($post) {
    $name   = get_post_meta($post->ID, '_sis_customer_name', true);
    $email  = get_post_meta($post->ID, '_sis_customer_email', true);
    $total  = get_post_meta($post->ID, '_sis_order_total', true);
    $message  = get_post_meta($post->ID, '_sis_order_message', true);
    $status = get_post_meta($post->ID, '_sis_order_status', true);

    wp_nonce_field('sis_save_order_meta', 'sis_order_meta_nonce');
    ?>

    <div class="sis-order-fields">
        <div class="form-control" id="sis_test">
            <label for="sis_customer_name">Customer Name:</label>
            <input type="text" id="sis_customer_name" name="sis_customer_name" value="<?php echo esc_attr($name); ?>">
        </div>

        <div class="form-control">
            <label for="sis_customer_email">Customer Email:</label>
            <input type="email" id="sis_customer_email" name="sis_customer_email" value="<?php echo esc_attr($email); ?>">
        </div>

        <div class="form-control">
            <label for="sis_order_total">Order Total:</label>
            <input type="number" id="sis_order_total" name="sis_order_total" value="<?php echo esc_attr($total); ?>" step="0.01">
        </div>

        <div class="form-control">
            <label for="sis_order_message">Message:</label>
            <input type="text" id="sis_order_message" name="sis_order_message" value="<?php echo esc_attr($message); ?>">
        </div>

        <div class="form-control">
            <label for="sis_order_status">Status:</label>
            <select id="sis_order_status" name="sis_order_status">
                <option value="pending" <?php selected($status, 'pending'); ?>>Pending</option>
                <option value="paid" <?php selected($status, 'paid'); ?>>Paid</option>
                <option value="shipped" <?php selected($status, 'shipped'); ?>>Shipped</option>
            </select>
        </div>
    </div>

    <?php
}


add_action('save_post', 'sis_save_order_meta');

function sis_save_order_meta($post_id) {
    if (!isset($_POST['sis_order_meta_nonce']) || !wp_verify_nonce($_POST['sis_order_meta_nonce'], 'sis_save_order_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Sanitize + save
    update_post_meta($post_id, '_sis_customer_name', sanitize_text_field($_POST['sis_customer_name']));
    update_post_meta($post_id, '_sis_customer_email', sanitize_email($_POST['sis_customer_email']));
    update_post_meta($post_id, '_sis_order_total', floatval($_POST['sis_order_total']));
    update_post_meta($post_id, '_sis_order_message', sanitize_text_field($_POST['sis_order_message']));
    update_post_meta($post_id, '_sis_order_status', sanitize_text_field($_POST['sis_order_status']));
}


add_action('init', 'sis_register_orders_cpt');

// Add custom columns
add_filter('manage_sis_order_posts_columns', function($columns) {
    // Insert new columns after the title column
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['customer_name'] = 'Customer Name';
            $new_columns['customer_email'] = 'Customer Email';
            $new_columns['order_total'] = 'Order Total';
            $new_columns['order_status'] = 'Status';
        }
    }
    return $new_columns;
});

// Populate custom columns
add_action('manage_sis_order_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'customer_name':
            echo esc_html(get_post_meta($post_id, '_sis_customer_name', true));
            break;
        case 'customer_email':
            echo esc_html(get_post_meta($post_id, '_sis_customer_email', true));
            break;
        case 'order_total':
            $total = get_post_meta($post_id, '_sis_order_total', true);
            echo $total !== '' ? '$' . number_format((float)$total, 2) : '';
            break;
        case 'order_status':
            echo esc_html(ucfirst(get_post_meta($post_id, '_sis_order_status', true)));
            break;
    }
}, 10, 2);

// Make columns sortable
add_filter('manage_edit-sis_order_sortable_columns', function($columns) {
    $columns['customer_name'] = 'customer_name';
    $columns['customer_email'] = 'customer_email';
    $columns['order_total'] = 'order_total';
    $columns['order_status'] = 'order_status';
    return $columns;
});
