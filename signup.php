<?php
// Hook to run after form submission
add_action('gform_after_submission_3', 'register_and_login_user', 10, 2);

function register_and_login_user($entry, $form) {
    // Fetch form data
    $user_email = rgar($entry, '1');
    $user_password = rgar($entry, '6');

    // Check if email and password are available
    if (!$user_email || !$user_password) {
        return;
    }

    // Check if user already exists
    if (email_exists($user_email)) {
        return;
    }

    // Create new user
    $user_id = wp_create_user($user_email, $user_password, $user_email);
    if (is_wp_error($user_id)) {
        return;
    }

    // Automatically login the user
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);

    // Optionally, you can redirect user to a different page, if needed
    // wp_redirect(home_url()); // Redirect to home page
    // exit;
}
