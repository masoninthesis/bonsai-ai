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
    $capabilities = array(
        'edit_post'          => 'edit_note',
        'read_post'          => 'read_note',
        'delete_post'        => 'delete_note',
        'edit_posts'         => 'edit_notes',
        'edit_others_posts'  => 'edit_others_notes',
        'publish_posts'      => 'publish_notes',
        'read_private_posts' => 'read_private_notes',
    );

    $args = array(
        'public'        => true,
        'label'         => 'Notes',
        'supports'      => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        'capability_type'    => 'note',
        'capabilities'  => $capabilities,
        'map_meta_cap'  => true, // Important for capability mapping
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
        // $redirect_url = get_permalink($post_id) . '?transcribe=true';
        // wp_redirect($redirect_url);
        // exit;
    }
}

// add_filter('gform_confirmation_4', 'gform4_custom_confirmation', 10, 4);
// function gform4_custom_confirmation($confirmation, $form, $entry, $ajax) {
//     // Fetch the post ID stored in the entry meta.
//     $post_id = gform_get_meta($entry['id'], 'my_custom_post_id');
//     // Construct the note URL with the transcribe query parameter.
//     $note_url = get_permalink($post_id) . '?transcribe=true';
//
//     // Embed the URL in the confirmation message, e.g., within a hidden div.
//     $confirmation = sprintf('<div>Your submission was successful. <div id="note-url" data-url="%s" style="display:none;"></div></div>', esc_url($note_url));
//
//     return $confirmation;
// }

add_filter('gform_confirmation_4', 'custom_confirmation_redirect', 10, 4);
function custom_confirmation_redirect($confirmation, $form, $entry, $ajax) {
    // Assuming $post_id holds the ID of the newly created note
    $post_id = gform_get_meta($entry['id'], 'my_custom_post_id');
    $note_url = get_permalink($post_id) . '?form_submitted=true';

    return array('redirect' => $note_url);
}

// schedule note generation custom event
add_action('gform_after_submission_4', 'schedule_transcription_and_submission', 10, 2);
function schedule_transcription_and_submission($entry, $form) {
    // Get the created post ID from the entry if available
    $post_id = rgar($entry, 'post_id'); // Ensure 'post_id' is the correct entry meta key

    if (!empty($post_id)) {
        // Schedule the custom event to run 1 minute after form submission
        wp_schedule_single_event(time() + 60, 'process_transcription_and_submission', [$post_id]);
    }
}

// Define note generation Scheduled Event Action
add_action('process_transcription_and_submission', 'execute_transcription_and_form_submission');
function execute_transcription_and_form_submission($post_id) {
    // Run the transcription process
    if (handle_transcription_for_cli($post_id)) {
        // Assuming transcription text is saved, proceed to submit Form 5
        // Retrieve the transcription text from post meta
        $transcription_text = get_post_meta($post_id, 'transcription_text', true);

        // Prepare and submit Form 5 entry with the transcription text
        $entry_data = [
            'input_1' => $transcription_text, // Adjust according to your form field IDs
            // Include any other necessary form fields here
        ];

        // Submit the form using GFAPI or custom submission logic
        GFAPI::submit_form(5, $entry_data);

        // Log success or handle submission result accordingly
        error_log("Form 5 submitted for post ID {$post_id} with transcription.");
    } else {
        error_log("Failed to transcribe or submit Form 5 for post ID {$post_id}.");
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

    // Update the post with the new OpenAI response, replacing the original content
    wp_update_post(array(
        'ID'           => $post_id,
        'post_content' => $openai_response // Set the content to the OpenAI response directly
    ));

    // Construct the redirect URL without the ?transcribe=true parameter
    $redirect_url = get_permalink($post_id);

    // Redirect to the updated post without the transcribe parameter
    wp_redirect($redirect_url);
    exit;
}

// Delete Notes
function custom_delete_post() {
    // Check if we're in the right context (action set to 'custom_delete_post') and necessary parameters are present
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'custom_delete_post' && isset($_REQUEST['_wpnonce']) && isset($_REQUEST['post_id'])) {
        // Verify nonce for security
        if (wp_verify_nonce($_REQUEST['_wpnonce'], 'delete_post_' . $_REQUEST['post_id'])) {
            $post_id = $_REQUEST['post_id'];

            // Check if the current user can delete this post
            if (current_user_can('delete_post', $post_id)) {
                // Set to false to send to trash instead of permanently deleting
                wp_delete_post($post_id, false);

                // Redirect back to the referring page, effectively refreshing the page
                $redirect_url = wp_get_referer() ? wp_get_referer() : home_url();
                wp_redirect($redirect_url);
                exit;
            }
        }

        // Redirect or show error message if nonce verification fails or user doesn't have permission
        wp_redirect(home_url());
        exit;
    }
    // If the function is triggered outside its intended context, do nothing
}
add_action('init', 'custom_delete_post');
