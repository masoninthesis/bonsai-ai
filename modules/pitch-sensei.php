<?php
// Include Parsedown class
require dirname(__DIR__) . '/vendor/autoload.php';

// This file handles functionality related to chat system

// Post Creation and Update
add_action('gform_after_submission_40', 'create_or_update_pitch_sensei_post', 10, 2);

// Redirect to reload the form after submission
add_filter('gform_confirmation_40', 'pitch_sensei_confirmation', 10, 4);

// Function to handle confirmation redirect
function pitch_sensei_confirmation($confirmation, $form, $entry, $ajax) {
    $redirect_url = rgar($entry, 'source_url');
    $confirmation = array('redirect' => $redirect_url);
    return $confirmation;
}

function create_or_update_pitch_sensei_post($entry, $form) {
    // Initialize Parsedown
    $parsedown = new Parsedown();

    // Extract data from the form
    $post_title = rgar($entry, '3');
    $custom_1 = rgar($entry, '6');
    $custom_2 = rgar($entry, '7');
    $custom_3 = rgar($entry, '8');
    $custom_4 = rgar($entry, '9');
    $custom_5 = rgar($entry, '10');
    $custom_6 = rgar($entry, '11');
    $custom_7 = rgar($entry, '12');
    $custom_8 = rgar($entry, '13');
    $custom_9 = rgar($entry, '14');
    $custom_10 = rgar($entry, '15');
    $sensei_data = rgar($entry, '16');
    $response_raw = GFCommon::replace_variables('{openai_feed_93}', $form, $entry);
    $response = $parsedown->text($response_raw);
    $post_content = '<div class="alert alert-info"><strong>Intro: </strong>' . $custom_1 . '</div>' .
                '<div class="alert alert-info"><strong>Problem: </strong>' . $custom_2 . '</div>' .
                '<div class="alert alert-info"><strong>Product demo: </strong>' . $custom_3 . '</div>' .
                '<div class="alert alert-info"><strong>Business model: </strong>' . $custom_4 . '</div>' .
                '<div class="alert alert-info"><strong>Traction:</strong>' . $custom_5 . '</div>' .
                '<div class="alert alert-info"><strong>Customers:</strong>' . $custom_6 . '</div>' .
                '<div class="alert alert-info"><strong>Competition: </strong>' . $custom_7 . '</div>' .
                '<div class="alert alert-info"><strong>Go-to Market: </strong>' . $custom_8 . '</div>' .
                '<div class="alert alert-info"><strong>Roadmap: </strong>' . $custom_9 . '</div>' .
                '<div class="alert alert-info"><strong>Team: </strong>' . $custom_10 . '</div>' .
                '<div class="alert alert-success my-3 ml-5">' . $response . '</div>';

    // Fetch the current user ID
    $current_user_id = get_current_user_id();

    // Fetch the current post ID
    $current_post_id = get_the_ID();

    // Update the ACF fields for both scenarios (new post or update)
    $acf_fields = array('my_pitch_1', 'my_pitch_2', 'my_pitch_3', 'my_pitch_4', 'my_pitch_5', 'my_pitch_6', 'my_pitch_7', 'my_pitch_8', 'my_pitch_9', 'my_pitch_10');
    $custom_fields = array($custom_1, $custom_2, $custom_3, $custom_4, $custom_5, $custom_6, $custom_7, $custom_8, $custom_9, $custom_10);

    for ($i = 0; $i < count($acf_fields); $i++) {
        update_field($acf_fields[$i], $custom_fields[$i], 'user_' . $current_user_id);
    }

    // Check if the form is in the 'sensei-pitch' category
    if (has_term('pitch-sensei', 'category', $current_post_id)) {
        // Update the current post's content with the generated response
        $updated_post = array(
            'ID'           => $current_post_id,
            'post_content' => $post_content,
        );
        wp_update_post($updated_post);

        // Fetch the senseios_fields using the merge tag
        $senseios_fields = GFCommon::replace_variables('{senseios_fields}', $form, $entry);

        // Check the data type
        if (is_array($senseios_fields)) {
            $senseios_fields = json_encode($senseios_fields);
        }

        // Add or update the senseios_fields as a meta field to the post
        update_post_meta($current_post_id, 'senseios_fields', $senseios_fields);

        // Add or update the Deshi's user ID as a meta field to the post
        update_post_meta($current_post_id, 'deshi_op', get_current_user_id());
    } else {
        // Identify the Sensei user ID
        $sensei_user_id = get_post_field('post_author', $current_post_id);

        // get the category object by slug
        $category = get_term_by('slug', 'pitch-sensei', 'category');

        // prepare the post data
        $post_data = array(
            'post_title'    => wp_strip_all_tags($post_title),
            'post_content'  => $post_content,
            'post_status'   => 'publish',
            'post_type'     => 'post',
            'post_author'   => get_current_user_id(),
            'comment_status'=> 'open',
            'post_category' => array( $category->term_id ),
        );

        // insert the post
        $post_id = wp_insert_post($post_data);

        // Fetch the senseios_fields using the merge tag
        $senseios_fields = GFCommon::replace_variables('{senseios_fields}', $form, $entry);

        // Check the data type
        if (is_array($senseios_fields)) {
            $senseios_fields = json_encode($senseios_fields);
        }

        // Add the 'sensei' as a meta field to the post
        add_post_meta($post_id, 'sensei', $sensei_data);

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
// add_filter('gform_confirmation_29', 'pitch_sensei_confirmation', 10, 4);
// function pitch_sensei_confirmation($confirmation, $form, $entry, $ajax) {
//     if (!empty(rgar($entry, '7')) || !empty(rgar($entry, '8')) || !empty(rgar($entry, '9'))) {
//         $redirect_url = rgar($entry, 'source_url');
//         $confirmation = array('redirect' => $redirect_url);
//     }
//     return $confirmation;
// }

// Custom function to add accordion style to Gravity Form fields.
function custom_accordion_for_gf_fields($content, $field, $value, $lead_id, $form_id) {
    // Check for a specific form by ID.
    if ($form_id == 40) {
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
add_filter('gform_field_content', 'custom_accordion_for_gf_fields', 10, 5);
