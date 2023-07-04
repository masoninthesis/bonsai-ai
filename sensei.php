<?php
// Post creation and redirect for Sensei Signup

add_action( 'gform_after_submission_25', 'create_post_and_redirect', 10, 2 );
function create_post_and_redirect( $entry, $form ) {
    // User data
    $user_data = array(
        'user_login'    => rgar( $entry, '1' ), // replace '1' with the ID of your Username field
        'user_pass'     => rgar( $entry, '3' ), // replace '3' with the ID of your Password field
        'user_email'    => rgar( $entry, '2' ), // replace '2' with the ID of your Email field
        'role'          => 'sensei' // or whatever role the user should have
    );

    // Insert the user and get the ID
    $user_id = wp_insert_user( $user_data );

    // Check if user was created successfully
    if ( ! is_wp_error( $user_id ) ) {
        // Post data
        $post_data = array(
            'post_title'    => rgar( $entry, '1' ), // replace '1' with the ID of your Post Title field
            'post_content'  => rgar( $entry, '1' ), // replace '1' with the ID of your Post Content field
            'post_status'   => 'publish',
            'post_author'   => $user_id,
            'post_type'     => 'post'
        );

        // Insert the post and get the ID
        $post_id = wp_insert_post( $post_data );

        // Check if post was created successfully
        if ( ! is_wp_error( $post_id ) ) {
            // Get the URL of the new post
            $url = get_permalink( $post_id );

            // Log in the user
            $creds = array(
                'user_login'    => $user_data['user_login'],
                'user_password' => $user_data['user_pass'],
                'remember'      => true
            );

            $user = wp_signon( $creds, false );

            if ( is_wp_error( $user ) ) {
                error_log( "Failed to log in user. Error: " . $user->get_error_message() );
            } else {
                // Log the redirection for debugging
                error_log( "Redirecting to post with ID: " . $post_id );

                // Redirect to the new post
                wp_redirect( $url );
                exit;
            }
        } else {
            error_log( "Failed to create post. Error: " . $post_id->get_error_message() );
        }
    } else {
        error_log( "Failed to create user. Error: " . $user_id->get_error_message() );
    }
}

// Prepopulate Username and Email for Logged-In Users

add_filter( 'gform_field_value_username', 'populate_username' );
function populate_username( $value ) {
    $current_user = wp_get_current_user();
    return $current_user->user_login; // Return the username of the logged in user
}

add_filter( 'gform_field_value_email', 'populate_email' );
function populate_email( $value ) {
    $current_user = wp_get_current_user();
    return $current_user->user_email; // Return the email of the logged in user
}
