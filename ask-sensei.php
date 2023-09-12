<?php
// Include Parsedown class
require_once 'vendor/autoload.php';

// This file handles functionality related to chat system
// Ask Sensei Form
// Post Creation
add_action('gform_after_submission_22', 'create_ask_sensei_post', 10, 2);

function create_ask_sensei_post($entry, $form) {
    // Initialize Parsedown
    $parsedown = new Parsedown();

    // extract data from the form
    $post_title = rgar($entry, '3');
    $question = rgar($entry, '1');
    $response_raw = GFCommon::replace_variables('{openai_feed_34}', $form, $entry);
    $response = $parsedown->text($response_raw);
    $post_content = '<div class="alert alert-info">'.$question.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

    // Identify the Sensei user ID
    $current_post_id = get_the_ID();
    $sensei_user_id = get_post_field('post_author', $current_post_id);

    // get the category object by slug
    $category = get_term_by('slug', 'ask-sensei', 'category');

    // prepare the post data
    $post_data = array(
        'post_title'    => wp_strip_all_tags($post_title),
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_type'     => 'post',
        'post_author'   => $sensei_user_id,  // Set the Sensei as the post author
        'comment_status'=> 'open',
        'post_category' => array( $category->term_id ),
    );

    // insert the post
    $post_id = wp_insert_post($post_data);

    // Fetch the senseios_fields using the merge tag
    $senseios_fields = GFCommon::replace_variables('{senseios_fields}', $form, $entry);

    // Check the data type
    if (is_array($senseios_fields)) {
        $senseios_fields = json_encode($senseios_fields);
    }

    // Add the senseios_fields as a meta field to the post
    add_post_meta($post_id, 'senseios_fields', $senseios_fields);

    // Add the Deshi's user ID as a meta field to the post
    add_post_meta($post_id, 'deshi_op', get_current_user_id());

    // redirect to the newly created post
    if ($post_id) {
        wp_redirect(get_permalink($post_id));
        exit;
    }
}



// Redirect to newly created ask-sensei post
// add_filter( 'gform_confirmation_22', 'custom_confirmation_22', 10, 4 );
//
// function custom_confirmation_22( $confirmation, $form, $entry, $ajax ) {
//     if ( $form['id'] != 22 ) {
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
