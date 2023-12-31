<?php
// This file handles functionality related to Sensei AI's goal check-in system

// This hook is triggered after Form #33 is submitted to create a comment for the post
add_action('gform_after_submission_33', 'post_checkin_comments', 10, 2);
function post_checkin_comments($entry, $form) {
    error_log('gform_after_submission_33 hook triggered.');

    // Check for both possible key structures
    $post_id = isset($entry[4]) ? $entry[4] : (isset($entry['4_0']) ? $entry['4_0'] : null);
    $username = isset($entry[5]) ? $entry[5] : (isset($entry['5_0']) ? $entry['5_0'] : null);
    $deshi_comment = isset($entry[1]) ? $entry[1] : (isset($entry['1_0']) ? $entry['1_0'] : null);
    $sensei_comment = GFCommon::replace_variables('{openai_feed_63}', $form, $entry);

    error_log('Entry Data: ' . print_r($entry, true));

    gf_openai_checkins($post_id, $username, $deshi_comment, $sensei_comment);
}

function gf_openai_checkins($post_id, $username, $deshi_comment, $sensei_comment) {
    $post = get_post($post_id);

    // Check if $post is a valid object
    if (!$post) {
        error_log('Error: Invalid post ID or post not found: ' . $post_id);
        return; // Exit the function if no valid post object is found
    }

    $author_email = get_the_author_meta('user_email', $post->post_author);
    $time = current_time('mysql');

    // Deshi comment data
    $data = array(
        'comment_post_ID' => $post_id,
        'comment_author' => $username,
        'comment_author_email' => $author_email,
        'comment_author_url' => 'https://',
        'comment_content' => $deshi_comment,
        'comment_type' => '',
        'comment_parent' => 0,
        'user_id' => $post->post_author, // Set the Deshi comment user_id to the post's author ID
        'comment_author_IP' => '127.0.0.1',
        'comment_agent' => 'Mozilla/5.0 (Mac; Intel Mac OS X 10.15; rv:77.0) Gecko/20100101 Firefox/77.0',
        'comment_date' => $time,
        'comment_approved' => 1,
    );

    // Insert the Deshi comment
    if ($deshi_comment !== '[timeout]') {
        $comment_id = wp_insert_comment($data);
    } else {
        $comment_id = 0;
    }

    // Sensei comment data (overwrite necessary fields)
    $sensei_data = $data;
    $sensei_data['comment_author'] = 'Sensei';
    $sensei_data['comment_content'] = $sensei_comment;
    $sensei_data['comment_parent'] = $comment_id;
    $sensei_data['user_id'] = 1; // Set the Sensei comment user_id to 1

    // Insert the Sensei comment
    $sensei_comment_id = wp_insert_comment($sensei_data);
}

// Update the goal status
function update_goal_status($post_id, $status) {
    if (update_post_meta($post_id, 'goal_status', $status)) {
        error_log("Successfully updated the goal status to " . $status);
    } else {
        error_log("Failed to update the goal status.");
    }
}

// Add this new function near the end of the file
function update_goal_status_api($request) {
  $post_id = $request['id'];
  $status = $request['status'];
  $responseMessage = '';

  if ($status === 'Abandon') {
    update_post_meta($post_id, 'goal_status', 'Abandoned');
    $responseMessage = 'Goal status updated to Abandoned';
  } elseif ($status === 'Reactivate') {
    update_post_meta($post_id, 'goal_status', 'Active');
    $responseMessage = 'Goal status updated to Reactivated';
  }

  if (!empty($responseMessage)) {
    return new WP_REST_Response(array('message' => $responseMessage), 200);
  } else {
    return new WP_REST_Response(array('message' => 'Invalid status'), 400);
  }
}

// Register the REST API route
add_action('rest_api_init', function () {
    register_rest_route('bonsai/v1', '/update_goal_status', array(
        'methods' => 'POST',
        'callback' => 'update_goal_status_api',
        'permission_callback' => '__return_true'
    ));
});

// New function to handle the API request for getting the goal status
function get_goal_status_api($request) {
    $post_id = $request['id'];
    $status = get_post_meta($post_id, 'goal_status', true);

    if (!empty($status)) {
        return new WP_REST_Response(array('status' => $status), 200);
    } else {
        return new WP_REST_Response(array('status' => 'Not set'), 200);
    }
}

// Register the new route
add_action('rest_api_init', function () {
    register_rest_route('bonsai/v1', '/get_goal_status', array(
        'methods' => 'GET',
        'callback' => 'get_goal_status_api',
        'permission_callback' => '__return_true'
    ));
});

// comment fomatting
add_filter('gform_save_field_value', 'preserve_line_breaks_in_multiline_text', 10, 5);
function preserve_line_breaks_in_multiline_text($value, $lead, $field, $form) {
    if ($field->get_input_type() == 'textarea') {  // Check if the field type is textarea
        $value = str_replace("\n", "<br/>", $value);  // Replace newline characters with <br/>
    }
    return $value;
}

// Redirect loads form again instead of confirmation message
add_filter( 'gform_confirmation_33', 'checkin_confirmation', 10, 4 );
function checkin_confirmation( $confirmation, $form, $entry, $ajax ) {
    $redirect_url = rgar( $entry, 'source_url' );
    $confirmation = array( 'redirect' => $redirect_url );

    // Log the URL to which we're trying to redirect
    error_log( 'GravityForms Confirmation: Redirecting to ' . $redirect_url );

    return $confirmation;
}
