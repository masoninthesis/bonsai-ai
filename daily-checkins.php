<?php
// Daily Check-in Form creates a post
add_action('gform_after_submission_29', function($entry, $form) {
    // Get the value of the field with the ID of 6
    $field_6_value = rgar($entry, '6');

    // Prepare the title and content for the post
    $title = rgar($entry, '3');  // replace 3 with the field ID of the title
    $content = rgar($entry, '5');  // This assumes that the field ID of the checklist is 5
    $content .= "\n\n" . $field_6_value;  // Append the value of field 6 to the content

    // Get categories by slug
    $cat_journal_entries = get_category_by_slug('journal-entries');
    $cat_checkin_journal_entries = get_category_by_slug('checkin-journal-entries');

    // Create the post
    $post_id = wp_insert_post(array(
        'post_title'    => $title,
        'post_content'  => $content,
        'post_status'   => 'publish',
        'post_author'   => get_current_user_id(),
        'post_category' => array( $cat_journal_entries->term_id, $cat_checkin_journal_entries->term_id ),
        'comment_status' => 'open',  // enable comments
    ));
}, 10, 2);
