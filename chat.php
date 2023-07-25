<?php
// This file handles functionality related to chat system

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
        'comment_author_url' => 'https://',
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

    if ($comment_id instanceof WP_Error) {
        error_log('GravityForms Comment Creation: Error inserting Deshi comment: ' . $comment_id->get_error_message());
    } elseif ($comment_id) {
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

    if ($sensei_comment_id instanceof WP_Error) {
        error_log('GravityForms Comment Creation: Error inserting Sensei comment: ' . $sensei_comment_id->get_error_message());
    } elseif ($sensei_comment_id) {
        error_log('GravityForms Comment Creation: Sensei comment inserted successfully.');
    } else {
        error_log('GravityForms Comment Creation: Failed to insert Sensei comment.');
    }
}

// Redirect loads form again instead of confirmation message
add_filter( 'gform_confirmation_23', 'chat_confirmation', 10, 4 );
function chat_confirmation( $confirmation, $form, $entry, $ajax ) {
    $redirect_url = rgar( $entry, 'source_url' );
    $confirmation = array( 'redirect' => $redirect_url );

    // Log the URL to which we're trying to redirect
    error_log( 'GravityForms Confirmation: Redirecting to ' . $redirect_url );

    return $confirmation;
}
