<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Create Bonsai AI admin menu
function bonsai_ai_admin_menu() {
    // In your admin-menu.php
    $icon_url = plugins_url('bonsai.svg', __FILE__);

    // Create main menu
    add_menu_page(
      'Bonsai AI Settings', // Page title
      'Bonsai AI',          // Menu title
      'manage_options',     // Capability
      'bonsai_ai',          // Menu slug
      'bonsai_ai_settings_page', // Callback function
      'data:image/svg+xml;base64,' . base64_encode('<svg id="logo" xmlns="http://www.w3.org/2000/svg" x="0" y="0" viewBox="0 0 731 731.2" style="enable-background:new 0 0 731 731.2; width: 36px; height: 34px;" xml:space="preserve"><path class="st0" d="m-1724 213.1 1357.3 783.7-1357.3 783.7-1357.3-783.7L-1724 213.1zm-1346 777.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.1 1357.2 783.7m-1346-790.2 1357.3 783.7M-2991.5 945l1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.2 1357.2 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2913 899.7l1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.2 1357.2 783.7m-1346-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7M-2756 809l1357.3 783.7m-1346-790.2 1357.3 783.7M-2733.5 796l1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.2 1357.2 783.7m-1346-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2655 750.7l1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346-790.2 1357.2 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7M-2520.4 673l1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2498 660l1357.3 783.7m-1346-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2397 601.7l1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.2 1357.2 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2L-950 1333.6m-1346-790.2 1357.2 783.7m-1346-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2262.4 524l1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2240 511.1l1357.3 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2L-849 1275.3m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2139 452.8l1357.3 783.7m-1346.1-790.2L-770.5 1230m-1346.1-790.2 1357.3 783.7m-1346-790.2L-748.1 1217m-1346-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2049.3 401-692 1184.7m-1346-790.2 1357.2 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7M-2004.4 375l1357.3 783.7m-1346.1-790.1 1357.3 783.7M-1982 362.1l1357.3 783.7m-1346-790.2 1357.2 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.1L-591 1126.4m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346-790.1 1357.3 783.7m-1346.1-790.2L-534.9 1094M-1881 303.8l1357.3 783.7m-1346.1-790.2L-512.5 1081m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7M-1791.3 252-434 1035.7m-1346-790.2 1357.2 783.7m-1346-790.2 1357.3 783.7m-1346.1-790.1 1357.3 783.7m-1346.1-790.2 1357.3 783.7m-1346.1-790.2 1357.3 783.7m0-13L-1735.2 1774m1346.1-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.2L-1780 1748.1M-434 958l-1357.3 783.7m1346.1-790.2-1357.3 783.7M-456.4 945l-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2L-1881 1689.8m1346.1-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7M-591 867.3-1948.3 1651m1346.1-790.2-1357.3 783.7m1346-790.2L-1970.7 1638m1346-790.1L-1982 1631.6m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.1L-2038 1599.2M-692 809l-1357.3 783.7m1346.1-790.2-1357.3 783.7M-714.4 796l-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.2-1357.2 783.7m1346-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346.1-790.2L-2139 1540.9m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.1-1357.3 783.7m1346.1-790.2L-2183.9 1515m1346.1-790.2-1357.3 783.7M-849 718.3-2206.3 1502m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346-790.2L-2240 1482.6m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7M-927.5 673l-1357.3 783.7m1346-790.2L-2296 1450.2M-950 660l-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.2-1357.2 783.7m1346-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2L-2397 1391.9m1346.1-790.2-1357.3 783.7m1346.1-790.1L-2419.4 1379m1346-790.2-1357.3 783.7m1346.1-790.2L-2441.9 1366m1346.1-790.2-1357.3 783.7M-1107 569.4l-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.2L-2498 1333.6m1346.1-790.2-1357.3 783.7M-1163.1 537l-1357.3 783.7m1346.1-790.2-1357.3 783.7M-1185.5 524l-1357.3 783.7m1346.1-790.2L-2554 1301.2m1346-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346-790.2-1357.2 783.7m1346-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2L-2655 1242.9m1346.1-790.1-1357.3 783.7m1346.1-790.2L-2677.4 1230m1346-790.2-1357.2 783.7m1346-790.2L-2699.9 1217m1346.1-790.1-1357.3 783.7M-1365 420.4l-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.1L-2756 1184.7m1346.1-790.2-1357.3 783.7M-1421.1 388l-1357.3 783.7m1346.1-790.2-1357.3 783.7M-1443.5 375l-1357.3 783.7m1346.1-790.1L-2812 1152.3m1346-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346-790.2-1357.2 783.7m1346-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346.1-790.2L-2913 1094m1346.1-790.2-1357.3 783.7m1346.1-790.2L-2935.4 1081m1346-790.2-1357.2 783.7m1346-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7M-1623 271.4l-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346-790.2-1357.2 783.7m1346-790.2-1357.3 783.7M-1679.1 239l-1357.3 783.7m1346.1-790.1-1357.3 783.7m1346.1-790.2-1357.3 783.7m1346.1-790.2L-3070 1003.3M647.1 172.7 396 27.7a56.727 56.727 0 0 0-56.9 0l-251 144.9c-17.6 10.2-28.4 28.9-28.4 49.3v289.9c0 20.3 10.8 39.1 28.4 49.3l251 144.9c17.6 10.2 39.3 10.2 56.9 0l251-144.9c17.6-10.2 28.4-28.9 28.4-49.3V222c.1-20.4-10.7-39.2-28.3-49.3zM542.6 447.1c-14.9 16.8-41.5 14.9-52.4 4.3-4.9 2.8-17.9 9.7-31.2 8.3l-96.7 55.5c-1.3.8-2.9 1.2-4.4 1.2h-48.6c-2.5 0-3.4-3.3-1.2-4.6l107.7-62.7c9.7-5.7 9.7-19.7 0-25.4l-41-23.9-32.1-4.7s-98.3 28.5-133.9 12.2c-35.5-16.3-45 0-45 0s-15 13-37.5 3.1c-11.4-15.9 0-25.6 15.2-20.4-1.8-10.1 7.4-13 14.9-20.5-9.1-1.4-7.5-14.2-10.3-22.9-10.4-12.5-1.4-29.1 10.3-29.1-5.7-29.8 11.9-31.6 30-27.7 2.2-28.4 22.6-42.9 52.6-29.9-4.2-41.7 47.8-26 47.8-26s-16-15.5-2.8-26c13.1-10.5 18 0 18 0 16.6-19.5 49.5-4.7 51.2 8.7 1.3-4.6 20.6-4.2 20.6-4.2 36.5-28.2 79.4-4.5 75.3 17.2 63.3-24.8 54.8 56.4 54.8 56.4 14.8-13.5 13.8.6 27.8 8.7 34.9 10.4 19.4 23.5 19.4 23.5 37.7-7.1 59.3 19.9 44 41.1 18.4 11.9 17.9 25 10.5 33.6 6.6 7.2 7.9 19.3-2.8 32.5 4 29.4-28.1 33.4-60.2 21.7z" style="fill: #f0f0f1;"></path></svg>'),
      6                   // Position
    );

    // Create Sendgrid Settings submenu
    add_submenu_page(
        'bonsai_ai',               // Parent slug
        'Sendgrid Settings',       // Page title
        'Sendgrid Settings',       // Menu title
        'manage_options',          // Capability
        'bonsai_ai_sendgrid',      // Menu slug
        'bonsai_ai_sendgrid_page'  // Callback function
    );
    // Create Deepgram Settings submenu
    add_submenu_page(
        'bonsai_ai',               // Parent slug
        'Deepgram Settings',       // Page title
        'Deepgram Settings',       // Menu title
        'manage_options',          // Capability
        'bonsai_ai_deepgram',      // Menu slug
        'bonsai_ai_deepgram_page'  // Callback function for rendering the settings page
    );
}
add_action('admin_menu', 'bonsai_ai_admin_menu');

// Main settings page content
// Invite code
function bonsai_ai_settings_page() {
    ?>
    <h1>Bonsai AI Settings</h1>
    <form method="post" action="options.php">
        <?php
        // Add nonce, action, and option_page fields for a settings page.
        settings_fields('bonsai_ai_settings_group');

        // Output the invite code settings section
        do_settings_sections('bonsai_ai_settings');

        // Output the submit button
        submit_button();
        ?>
    </form>
    <?php
}

// Initialize the option during admin initialization
add_action('admin_init', 'register_bonsai_ai_setting');
function register_bonsai_ai_setting() {
    register_setting(
        'bonsai_ai_settings_group', // Group
        'my_correct_invite_codes',  // Option name
        'bonsai_ai_sanitize_invite_codes'  // New Sanitize callback
    );

    add_settings_section(
        'bonsai_ai_invite_code_section', // ID
        'Invite Code Settings',          // Title
        null,                            // Callback
        'bonsai_ai_settings'             // Page
    );

    add_settings_field(
        'my_correct_invite_codes',         // ID
        'Invite Codes',                    // Title
        'bonsai_ai_correct_invite_code_callback_function', // Callback
        'bonsai_ai_settings',             // Page
        'bonsai_ai_invite_code_section',  // Section
        array('label_for' => 'my_correct_invite_codes')
    );
}

// Callback function to display the textarea
function bonsai_ai_correct_invite_code_callback_function($args) {
    $option = get_option('my_correct_invite_codes', array());
    // var_dump(get_option('my_correct_invite_codes', array()));
    $option_str = implode(', ', $option);
    echo '<textarea id="' . $args['label_for'] . '" name="' . $args['label_for'] . '">' . esc_textarea($option_str) . '</textarea>';
}

// New sanitization function
function bonsai_ai_sanitize_invite_codes($input) {
    // Split codes by comma or newline and trim whitespace
    $codes = preg_split("/[\s,]+/", $input);
    // Remove any empty values from the array
    $codes = array_filter($codes);
    return $codes;
}
