<?php
// Include Parsedown class
require_once 'vendor/autoload.php';

// This file handles functionality related to daily checkin
// Post Creation
add_action('gform_after_submission_40', 'create_daily_checkin', 10, 2);

function create_daily_checkin($entry, $form) {
    // Initialize Parsedown
    $parsedown = new Parsedown();

    // Get the current post ID and categories
    $current_post_id = get_the_ID();
    $cat_journal_entries = get_category_by_slug('journal-entries');
    $cat_checkin_journal_entries = get_category_by_slug('checkin-journal-entries');

    // Check if the Deshi has filled out field 6
    if (!empty(rgar($entry, '6'))) {
        $post_title = rgar($entry, '3');
        $checkin = rgar($entry, '6');
        $response_raw = GFCommon::replace_variables('{openai_feed_89}', $form, $entry);
        $response = $parsedown->text($response_raw);
        $post_content = '<div class="alert alert-info">'.$checkin.'</div><div class="alert alert-success my-3 ml-5">'.$response.'</div>';

        $post_data = array(
            'post_title'    => wp_strip_all_tags($post_title),
            'post_content'  => $post_content,
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_author'   => get_current_user_id(),
            'comment_status'=> 'open',
            'post_category' => array($cat_journal_entries->term_id, $cat_checkin_journal_entries->term_id),
        );

        $new_post_id = wp_insert_post($post_data);

        if ($new_post_id) {
            wp_redirect(get_permalink($new_post_id));
            exit;
        }
    }

    // Handle Field 7: Comment and Sensei's reply as a comment (using openai_feed_90)
    if (!empty(rgar($entry, '7'))) {
        $deshi_comment = rgar($entry, '7');
        $sensei_comment_raw = GFCommon::replace_variables('{openai_feed_90}', $form, $entry);
        $sensei_comment = $parsedown->text($sensei_comment_raw);

        $comment_data = array(
            'comment_post_ID' => $current_post_id,
            'comment_author' => 'Deshi',
            'comment_content' => $deshi_comment,
            'comment_approved' => 1,
        );

        $comment_id = wp_insert_comment($comment_data);

        $sensei_comment_data = array(
            'comment_post_ID' => $current_post_id,
            'comment_author' => 'Sensei',
            'comment_content' => $sensei_comment,
            'comment_approved' => 1,
            'comment_parent' => $comment_id,
        );

        wp_insert_comment($sensei_comment_data);
    }

    // Handle Field 8: Comment and Sensei's reply as a comment (using openai_feed_91)
    if (!empty(rgar($entry, '8'))) {
        $deshi_comment = rgar($entry, '8');
        $sensei_comment_raw = GFCommon::replace_variables('{openai_feed_91}', $form, $entry);
        $sensei_comment = $parsedown->text($sensei_comment_raw);

        $comment_data = array(
            'comment_post_ID' => $current_post_id,
            'comment_author' => 'Deshi',
            'comment_content' => $deshi_comment,
            'comment_approved' => 1,
        );

        $comment_id = wp_insert_comment($comment_data);

        $sensei_comment_data = array(
            'comment_post_ID' => $current_post_id,
            'comment_author' => 'Sensei',
            'comment_content' => $sensei_comment,
            'comment_approved' => 1,
            'comment_parent' => $comment_id,
        );

        wp_insert_comment($sensei_comment_data);
    }

    // Handle Field 9: Comment and Sensei's reply as a comment (using openai_feed_92)
    if (!empty(rgar($entry, '9'))) {
        $deshi_comment_9 = rgar($entry, '9');
        $sensei_comment_9_raw = GFCommon::replace_variables('{openai_feed_92}', $form, $entry);
        $sensei_comment_9 = $parsedown->text($sensei_comment_9_raw);

        $comment_data_9 = array(
            'comment_post_ID' => $current_post_id,
            'comment_author' => 'Deshi',
            'comment_content' => $deshi_comment_9,
            'comment_approved' => 1,
        );

        $comment_id_9 = wp_insert_comment($comment_data_9);

        $sensei_comment_data_9 = array(
            'comment_post_ID' => $current_post_id,
            'comment_author' => 'Sensei',
            'comment_content' => $sensei_comment_9,
            'comment_approved' => 1,
            'comment_parent' => $comment_id_9,
        );

        wp_insert_comment($sensei_comment_data_9);
    }
}

// Redirect to reload the form instead of showing the confirmation message
add_filter('gform_confirmation_40', 'daily_checkin_confirmation', 10, 4);
function daily_checkin_confirmation($confirmation, $form, $entry, $ajax) {
    if (!empty(rgar($entry, '7')) || !empty(rgar($entry, '8')) || !empty(rgar($entry, '9'))) {
        $redirect_url = rgar($entry, 'source_url');
        $confirmation = array('redirect' => $redirect_url);
    }
    return $confirmation;
}
