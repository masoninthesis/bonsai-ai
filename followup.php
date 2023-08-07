<?php
// Assuming you have other initializations and includes here

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
        error_log("Recent Deshi check-in not found for post ID: " . $post->ID);
        if ($most_recent_deshi_comment_time) {
            error_log("Time of the most recent Deshi comment: " . $most_recent_deshi_comment_time);
        } else {
            error_log("No comments found from the Deshi for post ID: " . $post->ID);
        }
    }
}
