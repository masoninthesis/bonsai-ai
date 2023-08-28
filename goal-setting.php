<?php
// This file handles functionality related to chat system

// Post Creation
add_action('gform_after_submission_32', 'create_smart_goal', 10, 2);

function create_smart_goal($entry, $form) {
    // extract data from the form
    $post_title = rgar($entry, '1');
    $goal = rgar($entry, '1');
    $response = GFCommon::replace_variables('{openai_feed_60}', $form, $entry);

    // construct the post content using the question and response
    $post_content = '<div class="alert alert-info">'.$goal.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

    // get the category object by slug
    $category = get_term_by('slug', 'goals-journal-entries', 'category');

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
