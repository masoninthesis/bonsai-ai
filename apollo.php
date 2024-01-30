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
add_filter( 'gform_confirmation_4', 'note_confirmation', 10, 4 );
function note_confirmation( $confirmation, $form, $entry, $ajax ) {
    // Get the source URL and append a query parameter
    $redirect_url = rgar( $entry, 'source_url' ) . '?form_submitted=true';

    // Set up the redirection
    $confirmation = array( 'redirect' => $redirect_url );

    // Log the URL to which we're trying to redirect
    error_log( 'GravityForms Confirmation: Redirecting to ' . $redirect_url );

    return $confirmation;
}
