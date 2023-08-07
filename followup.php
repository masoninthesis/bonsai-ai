<?php

require_once '/srv/www/bonsai.so/current/web/app/plugins/gravityforms-openai/class-gwiz-gf-openai.php';
require_once '/srv/www/bonsai.so/current/web/app/plugins/bonsai-ai/goal-checkin.php';  // Include the goal-checkin.php file

// Get all posts in the 'goals-journal-entries' category from the last 7 days
$args = array(
    'category_name' => 'goals-journal-entries',
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1,
    'date_query' => array(
        'after' => date('Y-m-d', strtotime('-7 days'))
    )
);
$posts = get_posts($args);

error_log('Total posts fetched from the last 7 days: ' . count($posts));

$user_id = 1;  // Set the user ID
wp_set_current_user($user_id);
$current_user = wp_get_current_user();  // Get the current user object
$user_email = $current_user->user_email;  // Get the user's email
$form_id = 33;  // Set the form ID

$openai_instance = GWiz_GF_OpenAI::get_instance();

foreach ($posts as $post) {
    error_log('Processing post ID: ' . $post->ID . ' | Post date: ' . $post->post_date);

    // Get the comments for the current post
    $comments = get_comments(array('post_id' => $post->ID));

    // Get the post author ID
    $post_author_id = $post->post_author;

    $recent_comment_by_author = false;
    $most_recent_deshi_comment_time = null;

    // Iterate through the comments
    foreach ($comments as $comment) {
        // Check if the comment is by the post's author
        if ($comment->user_id == $post_author_id) {
            // Update the most recent deshi comment time
            $most_recent_deshi_comment_time = $comment->comment_date;

            // Check if the comment is less than 24 hours old
            $time_difference = current_time('timestamp') - strtotime($comment->comment_date);
            if ($time_difference <= 86400) { // 86400 seconds = 24 hours
                $recent_comment_by_author = true;
                break;
            }
        }
    }

    if ($recent_comment_by_author) {
        error_log("Recent Deshi check-in found for post ID: " . $post->ID);
        error_log("Time of the most recent Deshi comment: " . $most_recent_deshi_comment_time);
    } else {
        if (!$most_recent_deshi_comment_time) {
            error_log("No comments found from the Deshi for post ID: " . $post->ID);

            // Generate automatic response and submit it
            $response = '[timeout]';

            $entry = array(
                'form_id' => $form_id,
                'date_created' => current_time('mysql', 0),
                'is_read' => 0,
                'is_starred' => 0,
                'ip' => '127.0.0.1',
                'source_url' => site_url(),
                'user_agent' => 'Bonsai AI Plugin',
                'status' => 'active',
                '1' => $response,
                '4' => strval($post->ID),
                '5' => $current_user->user_login,
                '6' => $user_email,
            );

            $entry_id = GFAPI::add_entry($entry);

            if (is_wp_error($entry_id)) {
                error_log('Error creating form entry: ' . $entry_id->get_error_message());
            } else {
                error_log('Form entry created with ID: ' . $entry_id);

                // Fetch the form and process feeds for the new entry
                $form = GFAPI::get_form($form_id);
                $entry = GFAPI::get_entry($entry_id);
                post_checkin_comments($entry, $form);  // Manually trigger post_checkin_comments function
                $feeds = GFAPI::get_feeds(null, $form_id);
                foreach ($feeds as $feed) {
                    $openai_instance->process_feed($feed, $entry, $form);
                }
            }
        } else {
            error_log("Recent Deshi check-in not found for post ID: " . $post->ID);
            error_log("Time of the most recent Deshi comment: " . $most_recent_deshi_comment_time);
        }
    }
}
?>
