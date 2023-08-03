<?php
// This file handles sign in redirects for deshi and sensei users
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
