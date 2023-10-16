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

    // Check if the file is an image and create a thumbnail
    if (wp_check_filetype($upload['file'])['ext']) {
        $image_editor = wp_get_image_editor($upload['file']);
        if (!is_wp_error($image_editor)) {
            $image_editor->resize(76, 76, true);
            $image_editor->save($upload['file']);
        }
    }

    // Update the entry field with the attachment ID
    if ($attach_id) {
        GFAPI::update_entry_field($entry['id'], '6', $attach_id);
    } else {
        error_log('Attachment could not be created');
    }
}, 10, 2);
