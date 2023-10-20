<?php
// Uploaded image populates field with image ID
add_action('gform_after_submission_28', function($entry, $form) {
    // Get the temporary file path from Gravity Forms
    $temp_file_path = GFFormsModel::get_physical_file_path($entry['7']);

    if (empty($temp_file_path)) {
        error_log('Temp file path is empty');
        return;
    }

    // Generate the WordPress upload file data
    $file_content = file_get_contents($temp_file_path);
    if (!$file_content) {
        error_log('File content could not be retrieved');
        return;
    }
    $upload = wp_upload_bits(basename($temp_file_path), null, $file_content);

    if (!isset($upload['file'])) {
        error_log('Upload failed');
        return;
    }

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
    if ($attach_id) {
        GFAPI::update_entry_field($entry['id'], '6', $attach_id);

        $current_user_id = get_current_user_id();

        if($current_user_id > 0) { // Only proceed if a user is logged in
            update_user_meta($current_user_id, 'profile_img', $attach_id);
        } else {
            error_log('No user is logged in. Profile image not saved to user meta.');
        }
    } else {
        // Debugging: Check for attachment ID
        if (!$attach_id && wp_last_error()) {
            error_log('WordPress Error: ' . wp_last_error());
        }
        error_log('Attachment could not be created');
    }
}, 10, 2);
