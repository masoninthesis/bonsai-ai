<?php
// Include Parsedown class
require dirname(__DIR__) . '/vendor/autoload.php';

// This file handles functionality related to chat system

// Post Creation and Update
add_action('gform_after_submission_29', 'create_or_update_daily_checkin_post', 10, 2);

// Redirect to reload the form after submission
add_filter('gform_confirmation_29', 'daily_checkin_confirmation', 10, 4);

// Function to handle confirmation redirect
function daily_checkin_confirmation($confirmation, $form, $entry, $ajax) {
    $redirect_url = rgar($entry, 'source_url');
    $confirmation = array('redirect' => $redirect_url);
    return $confirmation;
}

function create_or_update_daily_checkin_post($entry, $form) {
    // Initialize Parsedown
    $parsedown = new Parsedown();

    // Extract data from the form
    $post_title = rgar($entry, '3');
    $custom_1 = rgar($entry, '6');
    $custom_2 = rgar($entry, '7');
    $custom_3 = rgar($entry, '8');
    $custom_4 = rgar($entry, '9');
    $response_raw_1 = GFCommon::replace_variables('{openai_feed_87}', $form, $entry);
    $response_1 = $parsedown->text($response_raw_1);
    $response_raw_2 = GFCommon::replace_variables('{openai_feed_88}', $form, $entry);
    $response_2 = $parsedown->text($response_raw_2);
    $response_raw_3 = GFCommon::replace_variables('{openai_feed_89}', $form, $entry);
    $response_3 = $parsedown->text($response_raw_3);
    $response_raw_4 = GFCommon::replace_variables('{openai_feed_89}', $form, $entry);
    $response_4 = $parsedown->text($response_raw_4);

    // Identify the Sensei user ID
    $current_post_id = get_the_ID();
    $sensei_author_id = get_post_field('post_author', $current_post_id);
    $sensei_author = get_userdata($sensei_author_id);

    // Add Sensei author to the post content
    $post_content = '<div class="badge badge-secondary">' . esc_html($sensei_author->display_name) . '</div></br>';

    // Append the rest of the post content
    $post_content .= '<div class="alert alert-info"><p><strong>What is on your mind?</strong></p>' . $custom_1 . '</div>' .
                     '<div id="sensei_response" class="alert alert-success my-3 ml-5">' . $response_1 . '</div>' .
                     '<div class="alert alert-info"><p><strong>And what else?</strong></p>' . $custom_2 . '</div>' .
                     '<div id="sensei_response" class="alert alert-success my-3 ml-5">' . $response_2 . '</div>' .
                     '<div class="alert alert-info"><p><strong>What is the real challenge here for you?</strong></p>' . $custom_3 . '</div>' .
                     '<div id="sensei_response" class="alert alert-success my-3 ml-5">' . $response_3 . '</div>' .
                     '<div class="alert alert-info"><p><strong>What do you want?</strong></p>' . $custom_4 . '</div>' .
                     '<div id="sensei_response" class="alert alert-success my-3 ml-5">' . $response_4 . '</div>';

    // Fetch the current user ID
    $current_user_id = get_current_user_id();

    // Fetch the post type of the current post
    $current_post_type = get_post_type($current_post_id);

    // Insert or update the post
    if (has_term('checkin-journal-entries', 'category', $current_post_id)) {
        $updated_post = array(
            'ID'           => $current_post_id,
            'post_content' => $post_content,
        );
        wp_update_post($updated_post);
        update_post_meta($current_post_id, 'sensei_author', $sensei_author_id);
    } else {
        $cat_journal_entries = get_category_by_slug('journal-entries');
        $cat_checkin_journal_entries = get_category_by_slug('checkin-journal-entries');
        $post_data = array(
            'post_title'    => wp_strip_all_tags($post_title),
            'post_content'  => $post_content,
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_author'   => get_current_user_id(),
            'comment_status'=> 'open',
            'post_category' => array($cat_journal_entries->term_id, $cat_checkin_journal_entries->term_id),
        );
        $post_id = wp_insert_post($post_data);
        $current_post_id = $post_id;  // Update the current_post_id to the newly created post ID
        add_post_meta($post_id, 'sensei_author', $sensei_author_id);
        // Fetch the senseios_fields using the merge tag
        $senseios_fields = GFCommon::replace_variables('{senseios_fields}', $form, $entry);

        // Check the data type
        if (is_array($senseios_fields)) {
            $senseios_fields = json_encode($senseios_fields);
        }

        $acf_fields = array('checkin_1', 'checkin_2', 'checkin_3', 'checkin_4');
        $custom_fields = array($custom_1, $custom_2, $custom_3, $custom_4);
        for ($i = 0; $i < count($acf_fields); $i++) {
            update_field($acf_fields[$i], $custom_fields[$i], $current_post_id);
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
}

// Redirect to reload the form instead of showing the confirmation message
// add_filter('gform_confirmation_29', 'daily_checkin_confirmation', 10, 4);
// function daily_checkin_confirmation($confirmation, $form, $entry, $ajax) {
//     if (!empty(rgar($entry, '7')) || !empty(rgar($entry, '8')) || !empty(rgar($entry, '9'))) {
//         $redirect_url = rgar($entry, 'source_url');
//         $confirmation = array('redirect' => $redirect_url);
//     }
//     return $confirmation;
// }

// Custom function to add accordion style to Gravity Form fields.
function custom_accordion_for_daily_checkin($content, $field, $value, $lead_id, $form_id) {
    // Check for a specific form by ID.
    if ($form_id == 29) {
        $field_label = $field->label;

        // Check if the field is the first one (e.g., the field with input ID 3)
        if ($field->id == 3) {
            $accordion_content = "
                <li>
                    <a class='toggle card' href='javascript:void(0);'>
                        <span class='mr-3'>{$field_label}</span>
                    </a>
                    <div class='inner show' style='display: block;'>
                        <div id='tab1' role='tabpanel'>
                            {$content}";
            return $accordion_content;
        }
        // Check if the field is the second one (e.g., the field with input ID 6)
        elseif ($field->id == 6) {
            $accordion_content = "
                            {$content}
                        </div>
                    </div>
                </li>";
            return $accordion_content;
        }
        // For all other fields
        else {
            $accordion_content = "
                <li>
                    <a class='toggle card' href='javascript:void(0);'>
                        <span class='mr-3'>{$field_label}</span>
                    </a>
                    <div class='inner'>
                        <div id='tab1' role='tabpanel'>
                            {$content}
                        </div>
                    </div>
                </li>";
            return $accordion_content;
        }
    }

    // Return original content for other forms or fields.
    return $content;
}
// Apply the function to alter Gravity Forms field content.
add_filter('gform_field_content', 'custom_accordion_for_daily_checkin', 10, 5);
