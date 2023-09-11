<?php
// Deshi Subscription Form: ID #26

// Create a sensei_id parameter for Gravity Forms to populate with
add_filter('gform_field_value_sensei_id', function($value) {
    global $post;
    return $post->post_author; // This assumes that the Sensei is the author of the post
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
