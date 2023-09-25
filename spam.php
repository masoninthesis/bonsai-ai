<?php
// Redirect the default WordPress registration page to homepage
add_action('wp_loaded', 'redirect_default_wp_signup_page');

function redirect_default_wp_signup_page() {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    if ($action == 'register' && strpos($_SERVER['SCRIPT_NAME'], 'wp-login.php') !== false) {
        wp_redirect(home_url()); // Redirect to homepage
        exit();
    }
}

// Deny access to the default WordPress registration page
add_action('login_init', 'deny_default_wp_signup_page');

function deny_default_wp_signup_page() {
    $action = isset($_GET['action']) ? $_GET['action'] : '';
    if ($action == 'register') {
        wp_die('Registration is not allowed.', 'Registration Disabled');
        exit();
    }
}
