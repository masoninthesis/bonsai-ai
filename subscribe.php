<?php
// Deshi Subscription Form: ID #26

// Create a sensei_id parameter for Gravity Forms to populate with
add_filter('gform_field_value_sensei_id', function($value) {
    global $post;
    if (is_object($post)) {
        return $post->post_author;
    }
    return ''; // return an empty string if $post is not an object
});

// Store the Sensei ID in the user metadata
add_action('gform_after_submission_26', 'subscribe_sensei', 10, 2);
add_filter('gform_field_value_is_user_logged_in', 'gform_field_value_is_user_logged_in');

function gform_field_value_is_user_logged_in($value) {
    return is_user_logged_in() ? 'yes' : 'no';
}

function subscribe_sensei($entry, $form) {
    $user_id = get_current_user_id();
    $new_user = false;

    if(!$user_id) {
        $username = rgar($entry, '1'); //Assuming field 1 is for username
        $password = rgar($entry, '3'); //Assuming field 3 is for password
        $email = rgar($entry, '2'); //Assuming field 2 is for email

        $userdata = array(
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
            'role' => 'deshi',
        );

        $user_id = wp_insert_user($userdata);
        $new_user = true;

        // log the user in after registration
        $creds = array(
            'user_login'    => $username,
            'user_password' => $password,
            'remember'      => false,
        );

        $user = wp_signon( $creds, false );
        if ( is_wp_error($user) ) {
            error_log($user->get_error_message());
        } else {
            error_log('Login successful');
        }
    }

    //Assign role
    if(!$new_user) {
        $user = new WP_User($user_id);
        $user->add_role('deshi');
    }

    //Get Sensei ID from field ID 7
    $sensei_id = rgar($entry, '7'); //Assuming field 7 is for sensei ID

    // Update the user meta
    if($user_id && $sensei_id) {
        $current_sensei_ids = get_user_meta($user_id, 'sensei_ids', true);
        $current_sensei_ids = is_array($current_sensei_ids) ? $current_sensei_ids : array(); // Explicit array check

        if(!in_array($sensei_id, $current_sensei_ids)) {
            $current_sensei_ids[] = $sensei_id;
            update_user_meta($user_id, 'sensei_ids', $current_sensei_ids);
        }
    }
}

// Log in and redirect user to Sensei Profile page
add_filter('gform_confirmation_26', 'custom_confirmation', 10, 4); // make sure to replace '26' with your form id
function custom_confirmation($confirmation, $form, $entry, $ajax) {
    $redirect_url = get_permalink(); // Get the URL of the current page
    $confirmation = array('redirect' => $redirect_url); // Set the confirmation to redirect to the current page
    return $confirmation;
}

// Invite code
add_filter('gform_validation', 'validate_invite_code');
function validate_invite_code($validation_result) {

    $target_form_id = 26; // Your form ID
    $target_field_id = 9; // Your field ID

    // Skip if it's not the target form
    if (rgar($validation_result['form'], 'id') != $target_form_id) {
        return $validation_result;
    }

    // Get the form object from the validation result
    $form = $validation_result['form'];

    // Loop through the form fields
    foreach ($form['fields'] as &$field) {

        // If this is not the invite code field, skip it
        if ($field->id != $target_field_id) {
            continue;
        }

        // Get the submitted value from the $_POST
        $submitted_value = rgpost("input_{$field->id}");

        // Get the correct invite code from WordPress options
        $correct_invite_code = get_option('my_correct_invite_code', '');

        // If the submitted value does not equal our invite code, fail validation
        if ($submitted_value !== $correct_invite_code) {
            $validation_result['is_valid'] = false;
            $field->failed_validation = true;
            $field->validation_message = 'Invalid invite code';
        }
    }

    // Assign modified $form object back to the validation result
    $validation_result['form'] = $form;

    return $validation_result;
}
