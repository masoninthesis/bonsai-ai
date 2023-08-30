<?php
// This file handles functionality related to chat system

// Register custom merge tag for latest goal URL
add_filter('gform_custom_merge_tags', 'add_latest_goal_url_merge_tag', 10, 4);
function add_latest_goal_url_merge_tag($merge_tags, $form_id, $fields, $element_id) {
    $merge_tags[] = array(
        'label' => 'Latest Goal URL',
        'tag' => '{latest_goal_url}'
    );
    return $merge_tags;
}

// Replace merge tag value for latest goal URL
add_filter('gform_replace_merge_tags', 'replace_latest_goal_url_merge_tag', 10, 7);
function replace_latest_goal_url_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    if (strpos($text, '{latest_goal_url}') !== false) {
        $args = array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'category_name' => 'goals-journal-entries',
            'author' => get_current_user_id(),
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        );
        $latest_post = get_posts($args);
        if ($latest_post) {
            $post_url = get_permalink($latest_post[0]->ID);
            $text = str_replace('{latest_goal_url}', $post_url, $text);
        }
    }
    return $text;
}

// Post Creation & Manually Trigger Notification
add_action('gform_after_submission_32', 'create_smart_goal_and_send_notification', 10, 2);
function create_smart_goal_and_send_notification($entry, $form) {
    // extract data from the form
    $post_title = rgar($entry, '1');
    $goal = rgar($entry, '1');
    $response = GFCommon::replace_variables('{openai_feed_60}', $form, $entry);

    // construct the post content
    $post_content = '<div class="alert alert-info">' . $goal . '</div><div class="alert alert-success my-3 ml-5">' . $response . '</div>';

    // get the category object by slug
    $category = get_term_by('slug', 'goals-journal-entries', 'category');

    // prepare the post data
    $post_data = array(
        'post_title'    => wp_strip_all_tags($post_title),
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_type'     => 'post',
        'post_author'   => get_current_user_id(),
        'comment_status' => 'open',
        'post_category' => array($category->term_id),
    );

    // insert the post
    $post_id = wp_insert_post($post_data);

    // Trigger the notification manually
    if ($post_id) {
        GFAPI::send_notifications($form, $entry);
        wp_redirect(get_permalink($post_id));
        exit;
    }
}
