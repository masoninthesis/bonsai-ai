<?php
// This file handles sign in redirects for user logins and sign ups

// User logins
function redirect_to_current_page_after_login($redirect_to, $request_redirect_to, $user) {
    // Check if the user login was successful
    if (!is_wp_error($user)) {
        // Get the current URL
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        // Redirect to the current URL
        return $current_url;
    }
    return $redirect_to; // If login failed, redirect to the default URL
}

add_filter('login_redirect', 'redirect_to_current_page_after_login', 10, 3);


// Deshi and Sensei user sign ups
function bonsai_redirect_on_login($user_login, $user) {
    $is_sensei = in_array('sensei', (array) $user->roles);
    $is_deshi = in_array('deshi', (array) $user->roles);

    // Set the default redirection URL to the Deshi profile page
    $redirect_url = home_url("/deshi/{$user_login}");

    // If the user is a Sensei, override the redirection URL to the Sensei profile page
    if ($is_sensei) {
        $redirect_url = home_url("/sensei/{$user_login}");
    }
    // If the user is both, or if the user is just a Sensei, they will be redirected to the Sensei profile page
    // If the user is just a Deshi, they will be redirected to the Deshi profile page

    wp_redirect($redirect_url);
    exit;  // This prevents the rest of the script from executing
}
add_action('wp_login', 'bonsai_redirect_on_login', 10, 2);
