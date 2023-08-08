<?php
// Runs autoresponse check daily
add_action('init', 'schedule_deshi_autoresponse_cron');
function schedule_deshi_autoresponse_cron() {
    // Check if the event is already scheduled
    if (!wp_next_scheduled('deshi_autoresponse_event')) {
        // Schedule the event to run daily
        wp_schedule_event(time(), 'daily', 'deshi_autoresponse_event');
    }
}

add_action('deshi_autoresponse_event', 'execute_deshi_autoresponse_command');
function execute_deshi_autoresponse_command() {
    // Execute the WP-CLI command
    $output = shell_exec('/usr/bin/wp bonsai deshi autoresponse');
    // Log the output for debugging
    error_log("Output of the WP-CLI command: " . print_r($output, true));
}

function deshi_autoresponse_activation() {
    if (! wp_next_scheduled('deshi_autoresponse_event')) {
        wp_schedule_event(time(), 'daily', 'deshi_autoresponse_event');
    }
}

function deshi_autoresponse_deactivation() {
    wp_clear_scheduled_hook('deshi_autoresponse_event');
}

function setup_deshi_autoresponse_hooks($main_file) {
    register_activation_hook($main_file, 'deshi_autoresponse_activation');
    register_deactivation_hook($main_file, 'deshi_autoresponse_deactivation');
}
