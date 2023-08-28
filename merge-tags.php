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

// Grab post excerpts by category and create a merge tag to list them in GF
add_filter('gform_replace_merge_tags', 'replace_all_posts_excerpt_merge_tag', 10, 7);

function replace_all_posts_excerpt_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{all_post_excerpts:journal-entries}';

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
            $post_details[] = get_the_title() . "\n\n" . get_the_excerpt();
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

// New Journal Entry Post Slug Merge tag
add_filter( 'gform_custom_merge_tags', 'custom_merge_tags', 10, 4 );
function custom_merge_tags( $merge_tags, $form_id, $fields, $element_id ) {
    if ( $form_id != 21 ) return $merge_tags;

    $merge_tags[] = array(
        'label' => 'Post Slug',
        'tag' => '{post_slug}'
    );

    return $merge_tags;
}

add_filter( 'gform_replace_merge_tags', 'replace_post_slug_merge_tag', 10, 7 );
function replace_post_slug_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    if ( strpos( $text, '{post_slug}' ) === false ) return $text;

    $post_id = rgar( $entry, 'post_id' );
    $post_slug = get_post_field( 'post_name', $post_id );

    return str_replace( '{post_slug}', $post_slug, $text );
}

// SenseiOS Merge Tag
add_filter( 'gform_replace_merge_tags', 'replace_senseios_fields_merge_tag', 10, 7 );
function replace_senseios_fields_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    $custom_merge_tag = '{senseios_fields}';

    if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }

    $senseios_fields_content = display_senseios_fields();

    return str_replace( $custom_merge_tag, $senseios_fields_content, $text );
}

function display_senseios_fields() {
    $output = '';

    for($i = 1; $i <= 100; $i++) {
        $field_group = 'senseios_' . $i;

        if( have_rows($field_group) ) {
            while( have_rows($field_group) ) {
                the_row();

                $title = get_sub_field('title');
                $prompt = get_sub_field('prompt');
                $entry = get_sub_field('entry');
                $weight = get_sub_field('weight');

                $output .= "Title: $title\n";
                $output .= "Prompt: $prompt\n";
                $output .= "Entry: $entry\n";
                $output .= "Weight: $weight\n";
            }
        }
    }

    return $output;
}

// Sensei and Deshi username merge tags
add_filter( 'gform_replace_merge_tags', 'username_replace_merge_tags', 10, 7 );
function username_replace_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    $post = get_post();

    if ( is_object( $post ) ) {
        $author_name = get_the_author_meta( 'display_name', $post->post_author );
        $text = str_replace( '{current_post_author}', $author_name, $text );
    }

    $current_user = wp_get_current_user();
    if ( $current_user->exists() ) {
        $current_username = $current_user->user_login;
        $text = str_replace( '{current_user}', $current_username, $text );
    }

    return $text;
}

// Current post author username merge tag
// Register custom merge tags
add_filter('gform_custom_merge_tags', 'register_author_username_merge_tag', 10, 4);
function register_author_username_merge_tag($merge_tags, $form_id, $fields, $element_id) {
    $merge_tags[] = array(
        'label' => 'Author Username',
        'tag'   => '{author_username}'
    );

    return $merge_tags;
}

// Replace custom merge tags
add_filter('gform_replace_merge_tags', 'replace_author_username_merge_tag', 10, 7);
function replace_author_username_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $post_author_username = '';

    if ( is_singular() ) {
        $author_id = get_post_field('post_author', get_the_ID());
        $post_author_username = get_the_author_meta('user_login', $author_id);
    }

    return str_replace('{author_username}', $post_author_username, $text);
}

// User role merge tag
add_filter( 'gform_replace_merge_tags', 'replace_user_roles_merge_tag', 10, 7 );

function replace_user_roles_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    // Define the custom merge tag to look for
    $custom_merge_tag = '{user_roles}';

    // Check if our custom merge tag is used in the text
    if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }

    // Get the current user's data
    $current_user = wp_get_current_user();
    if ( empty( $current_user->roles ) ) {
        return str_replace( $custom_merge_tag, 'No roles found', $text );
    }

    // Convert the roles array to a comma-separated string
    $roles_str = implode( ', ', $current_user->roles );

    // Replace the custom merge tag with the roles string
    return str_replace( $custom_merge_tag, $roles_str, $text );
}
