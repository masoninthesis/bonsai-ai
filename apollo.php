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
