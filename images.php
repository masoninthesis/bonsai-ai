<?php
// Add custom image size
function bonsai_add_custom_image_size() {
    // Define new image size (name, width, height, crop)
    add_image_size('bonsai_thumbnail', 76, 76, true);
}

// Hook into the 'after_setup_theme' action
add_action('after_setup_theme', 'bonsai_add_custom_image_size');

// Uploaded image populates field with image ID
add_action('gform_after_submission_28', function($entry, $form) {
    // Get the temporary file path from Gravity Forms
    $temp_file_path = GFFormsModel::get_physical_file_path($entry['7']);

    // Generate the WordPress upload file data
    $upload = wp_upload_bits(basename($temp_file_path), null, file_get_contents($temp_file_path));

    // Insert the upload as a WordPress attachment
    $wp_filetype = wp_check_filetype(basename($upload['file']), null);
    $attachment = array(
        'post_mime_type' => $wp_filetype['type'],
        'post_title' => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
        'post_content' => '',
        'post_status' => 'inherit'
    );
    $attach_id = wp_insert_attachment($attachment, $upload['file']);

    // Update the entry field with the attachment ID
    GFAPI::update_entry_field($entry['id'], '6', $attach_id);
}, 10, 2);
