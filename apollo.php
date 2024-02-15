<?php
// Custom Mime Type
function my_custom_mime_types( $mimes ) {
    // Add webm to the list of allowed file types
    $mimes['webm'] = 'video/webm';
    return $mimes;
}
add_filter( 'upload_mimes', 'my_custom_mime_types', 1, 1 );


// Custom Post Type: Notes
function register_notes_cpt() {
    $args = array(
        'public' => true,
        'label'  => 'Notes',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments')
    );
    register_post_type('notes', $args);
}
add_action('init', 'register_notes_cpt');

// GF Form ID #4 reloads after submission
// Redirect loads form again instead of confirmation message
// add_filter( 'gform_confirmation_4', 'note_confirmation', 10, 4 );
// function note_confirmation( $confirmation, $form, $entry, $ajax ) {
//     // Get the source URL and append a query parameter
//     $redirect_url = rgar( $entry, 'source_url' ) . '?form_submitted=true';
//
//     // Set up the redirection
//     $confirmation = array( 'redirect' => $redirect_url );
//
//     // Log the URL to which we're trying to redirect
//     error_log( 'GravityForms Confirmation: Redirecting to ' . $redirect_url );
//
//     return $confirmation;
// }

// Handle the File Upload and Save URL to Post Meta
add_action('gform_after_submission_4', 'create_custom_note_page', 10, 2);
function create_custom_note_page($entry, $form) {
    $fileupload_field_id = '3';  // ID of the File Upload field
    $title_field_id = '1';  // ID of the Field for the Post Title

    // Get the title from the form entry
    $post_title = rgar($entry, $title_field_id);

    // Create a new post with the title from the form
    $new_post = array(
        'post_title'    => sanitize_text_field($post_title), // Sanitize the title
        'post_content'  => '', // Default content, can be modified as needed
        'post_status'   => 'publish', // Or 'draft', 'pending', etc.
        'post_type'     => 'notes' // Your custom post type
    );

    // Insert the post and get the new post ID
    $post_id = wp_insert_post($new_post);

    // Check for file upload and update post meta
    if (!empty($entry[$fileupload_field_id])) {
        $file_url = $entry[$fileupload_field_id];
        update_post_meta($post_id, 'uploaded_file_url', $file_url);
    } else {
        error_log('No file URL found for entry in Gravity Forms form ID 4.');
    }

    // redirect to new post
    if ($post_id) {
        gform_update_meta($entry['id'], 'my_custom_post_id', $post_id);
        error_log('Custom Stored post ID: ' . $post_id);

        // Immediate redirection to the newly created post
        $redirect_url = get_permalink($post_id) . '?transcribe=true';
        wp_redirect($redirect_url);
        exit;
    }
}

// add_action('gform_after_submission', function ($entry, $form) {
//     if ($form['id'] != 4) return;
//
//     $post_id = wp_insert_post([
//         'post_title'    => rgar($entry, '1'), // Assuming field ID 1 is the title
//         'post_content'  => 'SOAP Note here',
//         'post_status'   => 'publish',
//         'post_type'     => 'notes', // Replace with your custom post type
//     ]);
//
//     if ($post_id) {
//         gform_update_meta($entry['id'], 'my_custom_post_id', $post_id);
//         error_log('Custom Stored post ID: ' . $post_id);
//
//         // Immediate redirection to the newly created post
//         $redirect_url = get_permalink($post_id);
//         error_log('Redirecting to: ' . $redirect_url);
//         wp_redirect($redirect_url);
//         exit;
//     }
// }, 10, 2);

// add_filter('gform_confirmation', 'redirect_to_new_post', 10, 4);
// function redirect_to_new_post($confirmation, $form, $entry, $ajax) {
//     error_log('Attempting to retrieve post ID for redirection.');
//     if ($form['id'] == '4') {
//         $post_id = gform_get_meta($entry['id'], 'my_custom_post_id');
//         error_log('Retrieved post ID for redirection: ' . $post_id); // This will log regardless of whether $post_id is empty.
//         if ($post_id) {
//             $url = get_permalink($post_id);
//             error_log('Redirect URL: ' . $url);
//             $confirmation = array('redirect' => $url);
//         }
//     }
//     return $confirmation;
// }

// Update Note page with OpenAI response
// Dynamically Populate the Hidden Field with the Current Post ID
add_filter('gform_field_value_current_post_id', 'populate_current_post_id');
function populate_current_post_id($value) {
    global $post;
    return isset($post->ID) ? $post->ID : '';
}

// Handle Form Submission and Update Post Content
add_action('gform_after_submission_5', 'handle_openai_response', 10, 2);
function handle_openai_response($entry, $form) {
    // Retrieve the dynamically populated post ID from the hidden field
    $post_id = rgar($entry, '2'); // Assuming '2' is the ID of your hidden field

    // Retrieve the OpenAI response using the merge tag
    $openai_response = GFCommon::replace_variables('{openai_feed_4}', $form, $entry);

    // Get the current post content
    $post = get_post($post_id);
    $current_content = $post->post_content;

    // Append the OpenAI response to the existing content
    $updated_content = $current_content . "\n\n" . $openai_response;

    // Update the post with the new content
    wp_update_post(array(
        'ID'           => $post_id,
        'post_content' => $updated_content
    ));

    // Redirect to the updated post
    wp_redirect(get_permalink($post_id));
    exit;
}
