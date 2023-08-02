<?php
/**
 * This file handles functionality related to the Sensei AI's follow-up system
 */

// Sets user email
add_filter('gform_field_value_user_email', 'populate_user_email');
function populate_user_email($value){
    $current_user = wp_get_current_user();
    return $current_user->user_email;
}

// This function checks for responses to Sensei comments every 24 hours
function check_for_responses() {
    // Get all posts in the 'goals-journal-entries' category
    $args = array(
        'category_name' => 'goals-journal-entries',
        'post_type' => 'post',
        'post_status' => 'publish',
        'numberposts' => -1
    );
    $posts = get_posts($args);

    error_log('Checking for responses. Found ' . count($posts) . ' posts.');

    foreach ($posts as $post) {
        // Get the comments of the post
        $datetime = new DateTime();
        $datetime->modify('-1 day');
        $date = $datetime->format('Y-m-d H:i:s');

        $comments = get_comments(array(
            'post_id' => $post->ID,
            'date_query' => array(
                'after' => $date,
            ),
        ));

        error_log('Checking for comments after date: ' . $date);
        error_log('Found ' . count($comments) . ' comments for post ID ' . $post->ID);

        if (!empty($comments)) {
            $last_comment = end($comments);

            // If the last comment is from Sensei, skip this post
            if ($last_comment->comment_author == 'Sensei') {
                continue;
            }

            error_log('Last comment ID ' . $last_comment->comment_ID . ' is by ' . $last_comment->comment_author);

            // Generate automatic response and submit it
            $response = '[timeout]'; // Replace this with code to generate response

            // Submit the form
            $form_id = 33;

            $user_id = 1; // Change this to the ID of the user you want to log in as
            wp_set_current_user($user_id);
            $current_user = wp_get_current_user();

            // Get the user's email
            $user_email = $current_user->user_email;

            // Log the current user's login
            error_log('Current user login: ' . $current_user->user_login);

            $input_values = array(
                'input_1' => $response,
                'input_4' => $post->ID,
                'input_5' => $current_user->user_login,  // Assuming input_5 is the username field
                'input_6' => $user_email,  // Assuming input_6 is the email field
            );

            $form = GFAPI::get_form($form_id);
            $result = GFAPI::submit_form($form_id, $input_values);

            if (is_wp_error($result)) {
                // Log the error message
                error_log('GravityForms Form Submission: ' . $result->get_error_message());
            } else {
                error_log('GravityForms Form Submission: Submitted form ' . $form_id . ', result: ' . print_r($result, true));

                // Check if the form submission was successful
                if ($result['is_valid']) {
                    // No need to manually trigger the gform_after_submission_33 action here.
                }
            }
        }
    }
}

// Register activation hook
function followup_activation() {
    // Schedule the event to run the function every 24 hours
    if (!wp_next_scheduled('check_for_responses')) {
        wp_schedule_event(time(), 'daily', 'check_for_responses');
    }
}
register_activation_hook(__FILE__, 'followup_activation');

// Register deactivation hook
function followup_deactivation() {
    // Remove the scheduled event
    wp_clear_scheduled_hook('check_for_responses');
}
register_deactivation_hook(__FILE__, 'followup_deactivation');

// The action hook that will trigger the function
add_action('check_for_responses', 'check_for_responses');
