<?php
// This file handles functionality related to chat system
// Ask Sensei Form
// Post Creation
add_action('gform_after_submission_32', 'create_smart_goal', 10, 2);

function create_smart_goal($entry, $form) {
    // extract data from the form
    $post_title = rgar($entry, '1');
    $goal = rgar($entry, '1');
    $response = GFCommon::replace_variables('{openai_feed_60}', $form, $entry);

    // construct the post content using the question and response
    $post_content = '<div class="alert alert-info">'.$goal.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

    // get the category object by slug
    $category = get_term_by('slug', 'goals-journal-entries', 'category');

    // prepare the post data
    $post_data = array(
        'post_title'   => wp_strip_all_tags($post_title),
        'post_content' => $post_content,
        'post_status'  => 'publish',
        'post_type'    => 'post',
        'post_author'  => get_current_user_id(),
        'comment_status' => 'open',
        'post_category' => array( $category->term_id ),
    );

    // insert the post
    $post_id = wp_insert_post($post_data);

    // redirect to the newly created post
    if($post_id){
        wp_redirect(get_permalink($post_id));
        exit;
    }
}

// Redirect to newly created ask-sensei post
// add_filter( 'gform_confirmation_32', 'custom_confirmation_32', 10, 4 );
//
// function custom_confirmation_32( $confirmation, $form, $entry, $ajax ) {
//     if ( $form['id'] != 32 ) {
//         return $confirmation;
//     }
//
//     $post_id = gform_get_meta($entry['id'], 'new_post_id');
//
//     if ( ! $post_id ) {
//         return $confirmation;
//     }
//
//     $redirect_url = get_permalink( $post_id );
//     if ( $redirect_url ) {
//         $confirmation = array( 'redirect' => $redirect_url );
//     }
//
//     return $confirmation;
// }
