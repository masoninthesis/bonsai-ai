<?php
// Assuming you have other initializations and includes here

// Get the comments for the post with ID 4175
$args = array(
    'post_id' => 4175,
);
$comments = get_comments($args);

// Get the post author ID
$post_author_id = get_post_field('post_author', 4175);

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
    error_log("Recent Deshi check-in found");
    error_log("Time of the most recent Deshi comment: " . $most_recent_deshi_comment_time);
} else {
    error_log("Recent Deshi check-in not found");
    if ($most_recent_deshi_comment_time) {
        error_log("Time of the most recent Deshi comment: " . $most_recent_deshi_comment_time);
    } else {
        error_log("No comments found from the Deshi");
    }
}
?>
