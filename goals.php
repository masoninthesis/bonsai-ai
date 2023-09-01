<?php
// This file handles functionality related to goals

// Goal post deletion
function handle_delete_post() {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_post' && isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        wp_trash_post($post_id); // Move the post to the trash
    }
}
add_action('admin_post_delete_post', 'handle_delete_post');
add_action('admin_post_nopriv_delete_post', 'handle_delete_post');

// // Private vs. Public toggle
// Remove Private from appending to title
add_filter('the_title', 'remove_private_prefix_from_title');

function remove_private_prefix_from_title($title) {
    $title = str_replace('Private: ', '', $title);
    return $title;
}

// Enqueue Scripts
function bonsai_ai_enqueue_scripts() {
    wp_enqueue_script(
        'sensei-js',  // Handle
        plugin_dir_url(__FILE__) . 'js/sensei.js',  // Path to file
        array('jquery'),  // Dependencies
        '1.0.0',  // Version
        true  // In footer
    );

    // Localize script for AJAX
    wp_localize_script(
        'sensei-js',
        'myLocalizedVars',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('toggle_post_status_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'bonsai_ai_enqueue_scripts');

// Function to handle AJAX request
function toggle_post_status() {
    // Security check
    check_ajax_referer('toggle_post_status_nonce', 'security');

    // Get the post ID and new status from the AJAX request
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $new_status = isset($_POST['new_status']) ? sanitize_text_field($_POST['new_status']) : 'publish';

    // Update the post status
    $updated = wp_update_post(array(
        'ID' => $post_id,
        'post_status' => $new_status,
    ));

    // Send a JSON response back to the AJAX request
    wp_send_json_success(array('status' => $new_status, 'updated' => $updated));
}
add_action('wp_ajax_toggle_post_status', 'toggle_post_status');

// Add acountability partner email to goal post's metadata
add_action('gform_after_submission_38', 'save_accountability_partner', 10, 2);
function save_accountability_partner($entry, $form) {
    // Get the post ID from the form entry
    $post_id = rgar($entry, '4');

    // Get the accountability partner email and optional first name
    $accountability_partner_email = rgar($entry, '1');
    $accountability_partner_name = rgar($entry, '3');

    // Update the post metadata for the accountability partner email
    update_post_meta($post_id, 'accountability_partner_email', $accountability_partner_email);

    // Check if the optional first name is provided and update the post metadata
    if (!empty($accountability_partner_name)) {
        update_post_meta($post_id, 'accountability_partner_name', $accountability_partner_name);
    }
}
