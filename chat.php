<?php
// This file handles functionality related to chat system

// Redirecting to the newly created post
add_action('template_redirect', 'redirect_to_created_post');

function redirect_to_created_post() {
    if(isset($_GET['post_id'])) {
        $post_id = intval($_GET['post_id']); // ensuring the received ID is an integer
        $post_url = get_permalink($post_id);

        // If get_permalink() returns false, let's try another method:
        if (!$post_url) {
            $post_url = site_url() . "/?p=" . $post_id;
        }

        // If we still don't have a URL, redirect to the home page:
        if (!$post_url) {
            $post_url = site_url();
        }

        wp_redirect($post_url);
        exit;
    }
}

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

// Don't hide form 23 upon submission
// add_filter('gform_get_form_filter', 'show_form_after_submission', 10, 2);
// function show_form_after_submission($form_string, $form) {
//     if ($form->id == 23) {
//         remove_action('gform_after_submission', 'maybe_redirect_confirmation', 10, 2);
//     }
//     return $form_string;
// }
