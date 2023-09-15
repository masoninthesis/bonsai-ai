<?php
// Include Parsedown class
require_once 'vendor/autoload.php';

// This file handles functionality related to daily checkin
// Ask Sensei Form
// Post Creation
add_action('gform_after_submission_29', 'create_daily_checkin', 10, 2);

function create_daily_checkin($entry, $form) {
    // Initialize Parsedown
    $parsedown = new Parsedown();

    // extract data from the form
    $post_title = rgar($entry, '3');
    $checkin = rgar($entry, '6');
    $response_raw = GFCommon::replace_variables('{openai_feed_89}', $form, $entry);
    $response = $parsedown->text($response_raw);
    $post_content = '<div class="alert alert-info">'.$checkin.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

    // Identify the Sensei user ID
    $current_post_id = get_the_ID();
    $sensei_user_id = get_post_field('post_author', $current_post_id);

    // Get categories by slug
    $cat_journal_entries = get_category_by_slug('journal-entries');
    $cat_checkin_journal_entries = get_category_by_slug('checkin-journal-entries');

    // prepare the post data
    $post_data = array(
        'post_title'    => wp_strip_all_tags($post_title),
        'post_content'  => $post_content,
        'post_status'   => 'publish',
        'post_type'     => 'post',
        'post_author'   => get_current_user_id(),
        'comment_status'=> 'open',
        'post_category' => array( $cat_journal_entries->term_id, $cat_checkin_journal_entries->term_id ),
    );

    // insert the post
    $post_id = wp_insert_post($post_data);

    // Fetch the senseios_fields using the merge tag
    $senseios_fields = GFCommon::replace_variables('{senseios_fields}', $form, $entry);

    // Check the data type
    if (is_array($senseios_fields)) {
        $senseios_fields = json_encode($senseios_fields);
    }

    // Add the senseios_fields as a meta field to the post
    add_post_meta($post_id, 'senseios_fields', $senseios_fields);

    // Add the Deshi's user ID as a meta field to the post
    add_post_meta($post_id, 'deshi_op', get_current_user_id());

    // redirect to the newly created post
    if ($post_id) {
        wp_redirect(get_permalink($post_id));
        exit;
    }
}
