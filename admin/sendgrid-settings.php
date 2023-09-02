<?php

// Include the Composer autoload file
require dirname(__DIR__) . '/vendor/autoload.php';

// Include the SendGrid PHP Library
use SendGrid\Mail\Mail;

// Sendgrid settings page content
function bonsai_ai_sendgrid_page() {
    if (!current_user_can('manage_options')) {
        error_log('You do not have sufficient permissions to access this page.');
    }
    ?>
    <div class="wrap">
        <h1>Sendgrid Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('bonsai_ai_sendgrid_options_group');
            do_settings_sections('bonsai_ai_sendgrid');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
function bonsai_ai_register_sendgrid_settings() {
    register_setting('bonsai_ai_sendgrid_options_group', 'bonsai_ai_sendgrid_api_key');

    add_settings_section(
        'bonsai_ai_sendgrid_section', // ID
        'API Settings',               // Title
        null,                         // Callback
        'bonsai_ai_sendgrid'          // Page
    );

    add_settings_field(
        'bonsai_ai_sendgrid_api_key',     // ID
        'Sendgrid API Key',               // Title
        'bonsai_ai_sendgrid_api_key_cb',  // Callback
        'bonsai_ai_sendgrid',             // Page
        'bonsai_ai_sendgrid_section'      // Section
    );
}
add_action('admin_init', 'bonsai_ai_register_sendgrid_settings');

// API Key callback
function bonsai_ai_sendgrid_api_key_cb() {
    $api_key = get_option('bonsai_ai_sendgrid_api_key');
    echo "<input type='text' name='bonsai_ai_sendgrid_api_key' value='$api_key'>";
}

// Function to handle the common logic between creating and updating a user
function handle_sendgrid_wp($user_id, $role) {
    $user_info = get_userdata($user_id);
    $email = $user_info->user_email;
    $username = $user_info->user_login;

    // Initialize SendGrid API key
    $sendgridAPIKey = get_option('bonsai_ai_sendgrid_api_key');

    // Using cURL to send the API request
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode([
            'list_ids' => [],
            'contacts' => [
                [
                    'email' => $email,
                    'custom_fields' => [
                        'w1_T' => $role,
                        'w2_T' => $username
                    ]
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer $sendgridAPIKey",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        error_log("cURL Error #:" . $err);
    } else {
        error_log("SendGrid Response: $response");
    }
}

// Add new users to Sendgrid contacts
add_action('user_register', function($user_id) {
    $user_info = get_userdata($user_id);
    $user_roles = $user_info->roles;
    $role = $user_roles[0];
    handle_sendgrid_wp($user_id, $role);
});

// Update user role in Sendgrid contacts
add_action('set_user_role', function($user_id, $role, $old_roles) {
    handle_sendgrid_wp($user_id, $role);
}, 10, 3);

// Add waitlist signup to Sendgrid contacts and waitlist list
add_action('gform_after_submission', 'add_sendgrid_contact_via_gform', 10, 2);
function add_sendgrid_contact_via_gform($entry, $form) {
    // Only proceed if the form ID is 7
    if ($form['id'] != 7) {
        return;
    }

    // Fetch the email field from the form entry, assumed to be field ID 1
    $email = rgar($entry, '1');

    // Initialize SendGrid API key from WordPress options
    $sendgridAPIKey = get_option('bonsai_ai_sendgrid_api_key');

    // Initialize cURL
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode([
            'list_ids' => ['8b0f4ea5-f433-480b-8ce3-67b664f8352b'],
            'contacts' => [
                [
                    'email' => $email,
                    'custom_fields' => [
                        'w1_T' => 'waitlist'
                    ]
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer $sendgridAPIKey",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        error_log("cURL Error #:" . $err);
    } else {
        error_log("SendGrid Response: $response");
    }
}

// Add Accountability Partner to SendGrid list when a Gravity Forms form is submitted
add_action('gform_after_submission', 'add_accountability_partner_via_gform', 10, 2);
function add_accountability_partner_via_gform($entry, $form) {
    // Only proceed if the form ID is 38
    if ($form['id'] != 38) {
        return;
    }

    // Fetch the email and username fields from the form entry
    $email = rgar($entry, '1');
    $username = rgar($entry, '3');

    // Initialize SendGrid API key from WordPress options
    $sendgridAPIKey = get_option('bonsai_ai_sendgrid_api_key');

    // Initialize cURL
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode([
            'list_ids' => ['cff9087c-fc83-4f1e-be57-fa4518ce5a2f'],
            'contacts' => [
                [
                    'email' => $email,
                    'custom_fields' => [
                        'w1_T' => 'Accountability Partner',
                        'w2_T' => $username
                    ]
                ]
            ]
        ]),
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer $sendgridAPIKey",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        error_log("cURL Error #:" . $err);
    } else {
        error_log("SendGrid Response: $response");
    }
}
