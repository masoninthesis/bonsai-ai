<?php
// This file handles functionality related to chat system
// Ask Sensei Form
// Post Creation
add_action('gform_after_submission_22', 'create_ask_sensei_post', 10, 2);

function create_ask_sensei_post($entry, $form) {
    // extract data from the form
    $post_title = rgar($entry, '3');
    $question = rgar($entry, '1');
    $response = GFCommon::replace_variables('{openai_feed_34}', $form, $entry);

    // construct the post content using the question and response
    $post_content = '<div class="alert alert-info">'.$question.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

    // get the category object by slug
    $category = get_term_by('slug', 'ask-sensei', 'category');

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

// This hook is triggered after Form #23 is submitted to create a comment for the post
add_action('gform_after_submission_23', 'post_to_third_party', 10, 2);
function post_to_third_party($entry, $form)
{
    $post_id = $entry[4];
    $username = $entry[5];
    $deshi_comment = $entry[1];
    $sensei_comment = $entry[3];

    gf_openai_comments($post_id, $username, $deshi_comment, $sensei_comment);
}

function gf_openai_comments($post_id, $username, $deshi_comment, $sensei_comment)
{
    if (empty($post_id) || empty($username) || empty($deshi_comment) || empty($sensei_comment)) {
        error_log('GravityForms Comment Creation: Missing required data.');
        return;
    }

    $time = current_time('mysql');

    $data = array(
        'comment_post_ID' => $post_id,
        'comment_author' => $username,
        'comment_author_email' => 'email@example.com',
        'comment_author_url' => 'http://',
        'comment_content' => $deshi_comment,
        'comment_type' => '',
        'comment_parent' => 0,
        'user_id' => 1,
        'comment_author_IP' => '127.0.0.1',
        'comment_agent' => 'Mozilla/5.0 (Mac; Intel Mac OS X 10.15; rv:77.0) Gecko/20100101 Firefox/77.0',
        'comment_date' => $time,
        'comment_approved' => 1,
    );

    $comment_id = wp_insert_comment($data);

    if ($comment_id) {
        error_log('GravityForms Comment Creation: Deshi comment inserted successfully.');
    } else {
        error_log('GravityForms Comment Creation: Failed to insert Deshi comment.');
    }

    // Define a new data array for the Sensei comment.
    $sensei_data = $data;
    $sensei_data['comment_author'] = 'Sensei';
    $sensei_data['comment_content'] = $sensei_comment;
    $sensei_data['comment_parent'] = $comment_id;

    $sensei_comment_id = wp_insert_comment($sensei_data);

    if ($sensei_comment_id) {
        error_log('GravityForms Comment Creation: Sensei comment inserted successfully.');
    } else {
        error_log('GravityForms Comment Creation: Failed to insert Sensei comment.');
    }
}
