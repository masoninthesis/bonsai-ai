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
            // Generate automatic response and submit it
            $response = '[timeout]';

            // Submit the form
            $form_id = 33;

            $user_id = 1;
            wp_set_current_user($user_id);
            $current_user = wp_get_current_user();

            // Get the user's email
            $user_email = $current_user->user_email;

            error_log('Current user login: ' . $current_user->user_login);

            $input_values = array(
                'input_1' => $response,
                'input_4' => $post->ID,
                'input_5' => $current_user->user_login,
                'input_6' => $user_email,
            );

            $form = GFAPI::get_form($form_id);
            $result = GFAPI::submit_form($form_id, $input_values);

            if (is_wp_error($result)) {
                error_log('GravityForms Form Submission: ' . $result->get_error_message());
            } else {
                error_log('GravityForms Form Submission: Submitted form ' . $form_id . ', result: ' . print_r($result, true));
            }
        }
    }
}

function followup_activation() {
    if (!wp_next_scheduled('check_for_responses')) {
        wp_schedule_event(time(), 'daily', 'check_for_responses');
    }
}

function followup_deactivation() {
    wp_clear_scheduled_hook('check_for_responses');
}

function setup_followup_hooks($main_file) {
    register_activation_hook($main_file, 'followup_activation');
    register_deactivation_hook($main_file, 'followup_deactivation');
}

add_action('check_for_responses', 'check_for_responses');
