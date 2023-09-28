<?php
// Include Parsedown class
require dirname(__DIR__) . '/vendor/autoload.php';

// This file handles functionality related to chat system
// Ask Sensei Form
// Post Creation
add_action('gform_after_submission_22', 'create_ask_sensei_post', 10, 2);

function create_ask_sensei_post($entry, $form) {
    // Initialize Parsedown
    $parsedown = new Parsedown();

    // Extract data from the form
    $post_title = rgar($entry, '3');
    $question = rgar($entry, '1');
    $response_raw = GFCommon::replace_variables('{openai_feed_34}', $form, $entry);
    $response = $parsedown->text($response_raw);

    // Identify the Sensei user ID
    $current_post_id = get_the_ID();
    $sensei_author_id = get_post_field('post_author', $current_post_id);
    $sensei_author = get_userdata($sensei_author_id);

    // Fetch the current user ID
    $current_user_id = get_current_user_id();

    $post_content = '<div class="badge badge-secondary">' . esc_html($sensei_author->display_name) . '</div></br>' . '<div class="alert alert-info">'.$question.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

    // Get the category object by slug
    $category = get_term_by('slug', 'ask-sensei', 'category');

    // Prepare the post data
    $post_data = array(
        'post_title'    => wp_strip_all_tags($post_title),
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_type'     => 'post',
        'post_author'   => $current_user_id,
        'comment_status'=> 'open',
        'post_category' => array($category->term_id),
    );

    // Insert the post
    $post_id = wp_insert_post($post_data);

    // Add or update the new meta field for the Sensei user ID
    update_post_meta($post_id, 'sensei_author', $sensei_author_id);

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
