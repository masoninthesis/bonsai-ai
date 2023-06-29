<?php
// This file handles functionality related to Gravity Forms merge tags
//
// // Grab post titles by category and create a merge tag to list them in GF

add_filter('gform_replace_merge_tags', 'replace_all_posts_merge_tag', 10, 7);

function replace_all_posts_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{all_posts}';

    if (strpos($text, $custom_merge_tag) === false) {
        return $text;
    }

    $args = array(
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);

    $post_titles = [];

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $post_titles[] = get_the_title();
        }
    }

    wp_reset_postdata();

    $replace_text = implode(', ', $post_titles);

    return str_replace($custom_merge_tag, $replace_text, $text);
}

// // Allows us to dynamically populate using the posts merge tag
function populate_posts_merge_tag( $value, $field, $name, $lead, $form ) {
    if ( $name != 'all_posts' ) {
        return $value;
    }

    $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );

    $query = new WP_Query($args);

    if ( $query->have_posts() ) {
        $posts = array();
        while ( $query->have_posts() ) {
            $query->the_post();
            $posts[] = get_the_title();
        }
        // Reset post data after the loop
        wp_reset_postdata();
        return implode(", ", $posts);
    }
}
add_filter( 'gform_field_value_all_posts', 'populate_posts_merge_tag', 10, 5 );
