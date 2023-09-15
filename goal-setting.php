<?php
// Include Parsedown class if not already included
require_once 'vendor/autoload.php';

// This file handles functionality related to chat system

// Register the function for multiple forms
add_action('gform_after_submission_32', 'create_smart_goal', 10, 2);
add_action('gform_after_submission_40', 'create_smart_goal', 10, 2);

function create_smart_goal($entry, $form) {
    // Check form ID to ensure it's one of the forms we're interested in
    if ($form['id'] != 32 && $form['id'] != 40) {
        return;
    }

    // Initialize Parsedown
    $parsedown = new Parsedown();

    // Extract data from the form
    $post_title = rgar($entry, '1');
    $goal = rgar($entry, '1');
    $response_raw = GFCommon::replace_variables('{openai_feed_60}', $form, $entry);
    $response = $parsedown->text($response_raw);  // Markdown parsing

    // Construct the post content using the question and response
    $post_content = '<div class="alert alert-info">' . $goal . '</div><div class="alert alert-success my-3 ml-5">' . $response . '</div>';

    // Get the category object by slug
    $category = get_term_by('slug', 'goals-journal-entries', 'category');

    // Prepare the post data
    $post_data = array(
        'post_title'    => wp_strip_all_tags($post_title),
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_type'     => 'post',
        'post_author'   => get_current_user_id(),
        'comment_status'=> 'open',
        'post_category' => array($category->term_id),
    );

    // Insert the post
    $post_id = wp_insert_post($post_data);

    // Redirect to the newly created post
    if ($post_id) {
        wp_redirect(get_permalink($post_id));
        exit;
    }
}
