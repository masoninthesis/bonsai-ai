<?php
// Runs autoresponse check daily
function schedule_deshi_autoresponse_cron() {
    // Check if the environment is production
    if (defined('WP_ENV') && WP_ENV === 'production') {
        // Check if the event is already scheduled
        if (!wp_next_scheduled('deshi_autoresponse_event')) {
            // Schedule the event to run daily
            wp_schedule_event(time(), 'daily', 'deshi_autoresponse_event');
        }
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

// Apollo
// add_action('gform_after_submission_4', 'enqueue_my_custom_gf_process', 10, 2);
// function enqueue_my_custom_gf_process($entry, $form) {
//     // Pick a unique identifier for the job.
//     $job_id = wp_generate_uuid4();
//
//     // Store necessary data temporarily.
//     set_transient('gf4_auto_job_' . $job_id, json_encode([
//         'time_initiated' => current_time('timestamp'),
//         'entry_id' => $entry['id']
//     ]), HOUR_IN_SECONDS); // Keeping for 1 hour for debugging, adjust as needed.
//
//     // Schedule the event immediately.
//     if (!wp_next_scheduled('process_gf4_submission_job', [$job_id])) {
//         wp_schedule_single_event(time(), 'process_gf4_submission_job', [$job_id]);
//     }
// }
//
// add_action('process_gf4_submission_job', 'my_custom_submission_handler', 10, 1);
// function my_custom_submission_handler($job_id) {
//     $job_payload = get_transient('gf4_auto_job_' . $job_id);
//     if ($job_payload) {
//         $job_payload = json_decode($job_payload, true);
//         $entry_id = $job_payload['entry_id'];
//
//         // Ensure full path to WP CLI and proper command syntax
//         $transcribe_output = shell_exec('/usr/bin/wp gf-auto-transcribe process');
//         // Log the output for debugging
//         error_log("Transcribe Output: " . print_r($transcribe_output, true));
//
//         $submit_output = shell_exec('/usr/bin/wp gf-auto-submit process');
//         // Log the output for debugging
//         error_log("Submit Output: " . print_r($submit_output, true));
//
//         // Cleanup after completion
//         delete_transient('gf4_auto_job_' . $job_id);
//     }
// }
