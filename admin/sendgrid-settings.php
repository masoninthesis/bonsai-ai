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

// Assign users to lists based on role
$role_to_list_id_mapping = [
    'subscriber' => 'b4fd5719-20e6-41b5-a5a5-db350e61d96d',
    'pre_deshi'  => '17a852c3-7ab8-4384-a79c-a0d7ceefffa3',
    'deshi'      => '08b7e55b-8e11-4c10-a5fc-803b56d528ae',
    'sensei'     => '5af1f435-fc13-44ce-9731-f412f2b1ac5e',
    // Add more roles and their corresponding SendGrid list IDs
];

function get_sendgrid_contact_id($email, $sendgridAPIKey) {
    // Initialize cURL
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts/search",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode([
            'query' => "email LIKE '$email'"
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
        error_log("cURL Error while fetching contact ID: " . $err);
        return null;
    }

    $response_data = json_decode($response, true);

    if (isset($response_data['result'][0]['id'])) {
        return $response_data['result'][0]['id'];
    }

    return null;
}

// New function to remove a user from an old SendGrid list
function remove_user_from_sendgrid_list($email, $old_list_id, $sendgridAPIKey) {
    $contact_id = get_sendgrid_contact_id($email, $sendgridAPIKey);

    if (!$contact_id) {
        error_log("Could not find SendGrid contact ID for email $email");
        return;
    }

    error_log("Attempting to remove contact ID $contact_id from list ID $old_list_id");

    // Initialize cURL
    $curl = curl_init();

    $url = "https://api.sendgrid.com/v3/marketing/lists/$old_list_id/contacts?contact_ids=$contact_id";

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "DELETE",
        CURLOPT_HTTPHEADER => [
            "authorization: Bearer $sendgridAPIKey",
            "content-type: application/json"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        error_log("cURL Error while removing from list: " . $err);
    } else {
        error_log("Removed from list. SendGrid Response: $response");
    }
}

// Function to handle the common logic between creating and updating a user
function handle_sendgrid_wp($user_id, $role, $old_role = null) {
    $user_info = get_userdata($user_id);
    $email = $user_info->user_email;
    $username = $user_info->user_login;

    // Initialize SendGrid API key
    $sendgridAPIKey = get_option('bonsai_ai_sendgrid_api_key');

    global $role_to_list_id_mapping;

    // Determine the old and new SendGrid list IDs based on roles
    $list_id = isset($role_to_list_id_mapping[$role]) ? $role_to_list_id_mapping[$role] : 'default_list_id';
    $old_list_id = $old_role && isset($role_to_list_id_mapping[$old_role]) ? $role_to_list_id_mapping[$old_role] : null;

    // Log and remove user from old list if applicable
    if ($old_list_id) {
        error_log("Old List ID: $old_list_id");
        remove_user_from_sendgrid_list($email, $old_list_id, $sendgridAPIKey);
    }

    // Using cURL to send the API request
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.sendgrid.com/v3/marketing/contacts",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "PUT",
        CURLOPT_POSTFIELDS => json_encode([
            'list_ids' => [$list_id],  // Use the looked-up list ID here
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
// For programmatic role changes
add_action('set_user_role', function($user_id, $role, $old_roles) {
    $old_role = !empty($old_roles) ? array_shift($old_roles) : null;
    handle_sendgrid_wp($user_id, $role, $old_role);
}, 10, 3);

// Temporary storage for the old role
$old_wp_role = null;

// Capture the old role just before updating
add_action('update_user_meta', function($meta_id, $user_id, $meta_key, $meta_value) {
    global $old_wp_role;
    if ('wp_capabilities' === $meta_key) {
        $user_info = get_userdata($user_id);
        $old_wp_role = isset($user_info->roles[0]) ? $user_info->roles[0] : null;
    }
}, 10, 4);

// For role changes made via the WordPress admin
add_action('updated_user_meta', function($meta_id, $user_id, $meta_key, $_meta_value) {
    global $old_wp_role;
    if ('wp_capabilities' === $meta_key) {
        $user_info = get_userdata($user_id);
        $new_role = $user_info->roles[0];  // This should now have the updated role
        error_log("updated_user_meta action triggered. Updated role: $new_role");
        handle_sendgrid_wp($user_id, $new_role, $old_wp_role);
        $old_wp_role = null;  // Reset the old role
    }
}, 10, 4);


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
