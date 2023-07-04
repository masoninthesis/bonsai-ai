<?php
// This file handles functionality related to Gravity Forms merge tags

// Grab post titles by category and create a merge tag to list them in GF
add_filter('gform_replace_merge_tags', 'replace_all_posts_merge_tag', 10, 7);

function replace_all_posts_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{all_posts:journal-entries}';

    if (strpos($text, $custom_merge_tag) === false) {
        return $text;
    }

    $sensei_id = get_user_meta(get_current_user_id(), 'sensei_id', true);

    $args = array(
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'category_name' => 'journal-entries',
        'author' => $sensei_id,
    );

    $query = new WP_Query($args);

    $post_details = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_details[] = get_the_title() . "\n\n" . get_the_content();
        }
    }

    wp_reset_postdata();

    $replace_text = implode("\n\n------------------\n\n", $post_details);

    return str_replace($custom_merge_tag, $replace_text, $text);
}

// Populate posts merge tag
function populate_posts_merge_tag($value, $field, $name, $lead, $form) {
    if ($name != 'all_posts') {
        return $value;
    }

    $current_user_id = get_current_user_id();
    $sensei_id = get_user_meta($current_user_id, 'sensei_id', true); // make sure 'sensei_id' is the correct meta key

    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $sensei_id,
        'category_name' => 'journal-entries',
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $posts = array();
        while ($query->have_posts()) {
            $query->the_post();
            $posts[] = get_the_title() . "\n\n" . get_the_content();
        }

        wp_reset_postdata();
        return implode("\n\n------------------\n\n", $posts);
    }
}

add_filter( 'gform_field_value_all_posts', 'populate_posts_merge_tag', 10, 5 );

// Grab current post and comments
add_filter('gform_replace_merge_tags', 'replace_current_post_content_and_comments', 10, 7);

function replace_current_post_content_and_comments($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{current_post_content_and_comments}';

    if (strpos($text, $custom_merge_tag) === false) {
        return $text;
    }

    $post_id = rgar($entry, 'post_id');
    $post = get_post($post_id);
    if ($post) {
        $post_content = $post->post_content;

        // Get comments for this post in chronological order
        $comments = get_comments(array(
            'post_id' => $post->ID,
            'order' => 'ASC',
        ));

        // Update the array_map function to include comment author
        $comments_content = array_map(function($comment) {
            $author_label = $comment->comment_author === 'Sensei' ? $comment->comment_author : 'The deshi ' . $comment->comment_author;
            return $author_label . ' says: ' . $comment->comment_content;
        }, $comments);

        // Combine post content and comments
        $post_content .= "\n\nComments:\n" . implode("\n", $comments_content);

        return str_replace($custom_merge_tag, $post_content, $text);
    }
    return $text;
}
