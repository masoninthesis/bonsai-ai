<?php
// This file handles functionality related to Gravity Forms merge tags
// Apollo Merge Tags
add_filter('gform_custom_merge_tags', 'register_custom_merge_tag', 10, 4);
function register_custom_merge_tag($merge_tags, $form_id, $fields, $element_id) {
    // Only add for form ID 4; adjust as needed
    if ($form_id == 4) {
        $merge_tags[] = array(
            'label' => 'New Note Link', // Label for your custom merge tag
            'tag' => '{new_note_link}', // The merge tag itself
        );
    }
    return $merge_tags;
}

add_filter('gform_replace_merge_tags', 'replace_custom_note_link_merge_tag', 10, 7);
function replace_custom_note_link_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    // Before accessing $form['id'], ensure $form is an array and has the 'id' key
    if (!is_array($form) || !isset($form['id']) || $form['id'] != 4) {
        return $text; // It's not the correct form or $form is not as expected, so just return $text
    }

    // Assuming you've stored the new note's details somewhere accessible
    $post_id = get_post_meta($entry['id'], 'gf_new_note_post_id', true);
    if($post_id) {
        $post_url = get_permalink($post_id);
        $post_title = get_the_title($post_id);
        $new_note_link = "<a href='" . esc_url($post_url) . "'>" . esc_html($post_title) . "</a>";

        // Replace the custom merge tag with the actual link
        $text = str_replace('{new_note_link}', $new_note_link, $text);
    }

    return $text;
}

// Bonsai Merge Tags
// Current Post ID
add_filter('gform_custom_merge_tags', 'add_custom_merge_tag', 10, 4);
function add_custom_merge_tag($merge_tags, $form_id, $fields, $element_id) {
    // Only add for form ID 5; remove the conditional to add to all forms
    if ($form_id == 5) {
        $merge_tags[] = array(
            'label' => 'Current Post ID',
            'tag'   => '{current_post_id}'
        );
    }
    return $merge_tags;
}

add_filter('gform_replace_merge_tags', 'replace_custom_merge_tag', 10, 7);
function replace_custom_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $current_post_id = get_the_ID();
    return str_replace('{current_post_id}', $current_post_id, $text);
}


// Grab post titles by category and create a merge tag to list them in GF
add_filter('gform_replace_merge_tags', 'replace_all_posts_merge_tag', 10, 7);

function replace_all_posts_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{all_posts:journal-entries}';

    if (strpos($text, $custom_merge_tag) === false) {
        return $text;
    }

    // Fetch the post author's ID for the current page
    global $post;

    if ( is_a( $post, 'WP_Post' ) ) {
        $author_id = isset($post->post_author) ? $post->post_author : null;
    } else {
        $queried_object = get_queried_object();
        if ( is_a( $queried_object, 'WP_Post' ) ) {
            $author_id = isset($queried_object->post_author) ? $queried_object->post_author : null;
        } else {
            // Debugging line
            error_log('Neither $post nor $queried_object is a WP_Post object');
            // Fallback or exit if no author can be determined
            return $text;
        }
    }

    if ( $author_id === null ) {
        // Debugging line
        error_log('No author ID could be determined');
        // Fallback or exit if no author can be determined
        return $text;
    }

    $args = array(
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'category_name' => 'journal-entries',
        'author' => $author_id,
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

    // Fetch the post author's ID for the current page
    global $post;

    if ( is_a( $post, 'WP_Post' ) ) {
        $author_id = isset($post->post_author) ? $post->post_author : null;
    } else {
        $queried_object = get_queried_object();
        if ( is_a( $queried_object, 'WP_Post' ) ) {
            $author_id = isset($queried_object->post_author) ? $queried_object->post_author : null;
        } else {
            // Debugging line
            error_log('Neither $post nor $queried_object is a WP_Post object');
            // Fallback or exit if no author can be determined
            return $text;
        }
    }

    if ( $author_id === null ) {
        // Debugging line
        error_log('No author ID could be determined');
        // Fallback or exit if no author can be determined
        return $text;
    }

    $args = array(
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'category_name' => 'journal-entries',
        'author' => $author_id,
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
        $post_content = wp_strip_all_tags($post->post_content);

        // Get comments for this post in chronological order
        $comments = get_comments(array(
            'post_id' => $post->ID,
            'order' => 'ASC',
        ));

        // Update the array_map function to include comment author and remove HTML tags from comments
        $comments_content = array_map(function($comment) {
            $author_label = $comment->comment_author === 'Sensei' ? $comment->comment_author : 'The deshi ' . $comment->comment_author;
            return $author_label . ' says: ' . wp_strip_all_tags($comment->comment_content);
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

    // Get the current post ID
    global $post;

    // Check if $post is an object before trying to access its properties
    if ( is_object($post) ) {
        $post_id = $post->ID;

        // Fetch the stored Sensei OS fields from the post metadata
        $senseios_fields = get_post_meta($post_id, 'senseios_fields', true);

        if ($senseios_fields) {
            // If the metadata exists, use it
            return $senseios_fields;
        }
    }

    // Otherwise, fallback to your existing logic
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

// Custom SenseiOS Merge Tag
add_filter( 'gform_replace_merge_tags', 'replace_senseios_custom_merge_tag', 10, 7 );
function replace_senseios_custom_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
    $custom_merge_tag = '{senseios_custom}';

    if ( strpos( $text, $custom_merge_tag ) === false ) {
        return $text;
    }

    $senseios_custom_content = display_senseios_custom();

    return str_replace( $custom_merge_tag, $senseios_custom_content, $text );
}

function display_senseios_custom() {
    $output = '';

    // Get the current post ID
    global $post;

    // Check if $post is an object before trying to access its properties
    if ( is_object($post) ) {
        $post_id = $post->ID;

        // Fetch the specific Sensei OS custom field from the post metadata
        $senseios_custom = get_post_meta($post_id, 'senseios_custom', true);

        if ($senseios_custom) {
            // If the metadata exists, use it
            return $senseios_custom;
        }
    }

    // Otherwise, fetch the custom field directly
    if( have_rows('senseios_custom') ) {  // Replace 'senseios_custom' with the correct field name
        while( have_rows('senseios_custom') ) {
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

// Sensei post metadata merge tag
add_filter('gform_replace_merge_tags', 'replace_sensei_merge_tag', 10, 7);

function replace_sensei_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{sensei}';

    // Check if our custom merge tag is used in the text
    if (strpos($text, $custom_merge_tag) === false) {
        return $text;
    }

    // Fetch the current post ID
    $current_post_id = get_the_ID();

    // Get the 'sensei' meta value from the current post
    $sensei_value = get_post_meta($current_post_id, 'sensei', true);

    // Replace the merge tag
    return str_replace($custom_merge_tag, $sensei_value, $text);
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

// Latest goal post URL merge tag
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

// Deshi Profile URL merge tag
// Register custom merge tag for Deshi Profile URL
add_filter('gform_custom_merge_tags', 'add_deshi_profile_url_merge_tag', 10, 4);
function add_deshi_profile_url_merge_tag($merge_tags, $form_id, $fields, $element_id) {
    $merge_tags[] = array(
        'label' => 'Deshi Profile URL',
        'tag' => '{deshi_profile_url}'
    );
    return $merge_tags;
}

// Replace merge tag value for Deshi Profile URL
add_filter('gform_replace_merge_tags', 'replace_deshi_profile_url_merge_tag', 10, 7);
function replace_deshi_profile_url_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    if (strpos($text, '{deshi_profile_url}') !== false) {
        $user_id = rgar($entry, 'created_by');
        $user_info = get_userdata($user_id);
        $username = $user_info->user_login;

        // Construct the profile URL
        $profile_url = site_url() . "/deshi/" . $username;

        // Remove /wp/ from the URL
        $profile_url = str_replace('/wp/', '/', $profile_url);

        $text = str_replace('{deshi_profile_url}', $profile_url, $text);
    }
    return $text;
}

// Goal Accountability Partner Email and Name Merge Tags
add_filter('gform_replace_merge_tags', function($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {

    // The post ID is stored in field 4 in the form #33 entry
    $post_id = rgar($entry, '4');

    // Fetch the stored metadata values for the accountability partner
    $email = get_post_meta($post_id, 'accountability_partner_email', true);
    $name = get_post_meta($post_id, 'accountability_partner_name', true);

    // Replace the merge tags in the notification text
    $text = str_replace('{accountability_partner_email}', $email, $text);
    $text = str_replace('{accountability_partner_name}', $name, $text);

    return $text;

}, 10, 7);

// Goal checkin author email
add_filter('gform_replace_merge_tags', function($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {

    // The post ID is stored in field 4 in the form #33 entry
    $post_id = rgar($entry, '4');

    // Fetch the post author ID
    $post_author_id = get_post_field('post_author', $post_id);

    // Get the author's email
    $author_email = get_the_author_meta('user_email', $post_author_id);

    // Replace the merge tag in the notification text
    $text = str_replace('{author_email}', $author_email, $text);

    return $text;

}, 10, 7);

// SenseiModules: My Pitch Merge Tags
add_filter('gform_replace_merge_tags', 'replace_acf_merge_tags', 10, 7);
function replace_acf_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    for ($i = 1; $i <= 11; $i++) {
        $merge_tag = "{my_pitch_{$i}}";
        if (strpos($text, $merge_tag) !== false) {
            $current_user_id = get_current_user_id();
            $acf_value = get_field("my_pitch_{$i}", 'user_' . $current_user_id);

            // Debugging lines
            // error_log("Current User ID: " . $current_user_id);
            // error_log("ACF Field Value for my_pitch_{$i}: " . print_r($acf_value, true));

            $text = str_replace($merge_tag, $acf_value, $text);
        }
    }
    return $text;
}

// SenseiModules: Daily Checkin Merge Tags
add_filter('gform_replace_merge_tags', 'replace_daily_checkin_merge_tags', 10, 7);
function replace_daily_checkin_merge_tags($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    for ($i = 1; $i <= 4; $i++) {
        $merge_tag = "{checkin_{$i}}";
        if (strpos($text, $merge_tag) !== false) {
            // Updated Line: Get the current post ID
            $current_post_id = get_the_ID();

            // Updated Line: Fetch ACF field from the current post
            $acf_value = get_field("checkin_{$i}", $current_post_id);

            $text = str_replace($merge_tag, $acf_value, $text);
        }
    }
    return $text;
}

// Fetch current post category
add_filter('gform_replace_merge_tags', 'replace_category_slug_merge_tag', 10, 7);
function replace_category_slug_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
    $custom_merge_tag = '{category_slug}';

    if (strpos($text, $custom_merge_tag) === false) {
        return $text;
    }

    global $post;
    $category_slug = '';

    if (is_object($post) && isset($post->ID)) {
        $categories = get_the_category($post->ID);
        if (!empty($categories)) {
            $category_slug = $categories[0]->slug;
        }
    }

    return str_replace($custom_merge_tag, $category_slug, $text);
}

// Add a filter to replace {sensei_username} merge tag
function sensei_username_shortcode() {
    global $post;

    // Get the sensei_author user ID from the post metadata
    $user_id = get_post_meta($post->ID, 'sensei_author', true);

    if ($user_id) {
        $user = get_user_by('ID', $user_id);

        if ($user) {
            return $user->user_login;
        }
    }

    // If for some reason the username cannot be retrieved, return an empty string
    return '';
}
add_shortcode('sensei_username', 'sensei_username_shortcode');


// Abandon Goal URL Merge Tag
// add_filter('gform_custom_merge_tags', 'add_abandon_goal_merge_tag', 10, 4);
//
// function add_abandon_goal_merge_tag($merge_tags, $form_id, $fields, $element_id) {
//     if ($form_id == 33) {
//         $merge_tags[] = array(
//             'label' => 'Abandon Goal URL',
//             'tag'   => '{abandon_goal_url}'
//         );
//     }
//
//     return $merge_tags;
// }
//
// add_filter('gform_replace_merge_tags', 'replace_abandon_goal_merge_tag', 10, 7);
//
// function replace_abandon_goal_merge_tag($text, $form, $entry, $url_encode, $esc_html, $nl2br, $format) {
//     $post_id = rgar($entry, '4');  // Replace '4' with the field ID that stores the post ID
//     $user_id = rgar($entry, 'created_by'); // The user who created this entry
//     $secret_key = 'your_secret_key_here';
//
//     $token = hash_hmac('sha256', $user_id . '|' . $post_id, $secret_key);
//     $abandon_url = home_url("/abandon-goal?token=$token&post_id=$post_id");
//
//     return str_replace('{abandon_goal_url}', $abandon_url, $text);
// }
