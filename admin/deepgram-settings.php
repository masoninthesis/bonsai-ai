<?php

function bonsai_ai_deepgram_page() {
    ?>
    <div class="wrap">
        <h1>Deepgram Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('bonsai_ai_deepgram_options_group');
            do_settings_sections('bonsai_ai_deepgram');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function bonsai_ai_register_deepgram_settings() {
    // Register a new setting for Bonsai AI Deepgram API key
    register_setting(
        'bonsai_ai_deepgram_options_group', // Option group
        'bonsai_ai_deepgram_api_key'        // Option name
    );

    // Add a new section to the Deepgram settings page
    add_settings_section(
        'bonsai_ai_deepgram_api_section', // ID
        'API Key',                        // Title
        null,                             // Callback function for the section description
        'bonsai_ai_deepgram'              // Page on which to add this section
    );

    // Add a new field for the Deepgram API key
    add_settings_field(
        'bonsai_ai_deepgram_api_key_field', // ID
        'Deepgram API Key',                 // Title
        'bonsai_ai_deepgram_api_key_cb',    // Callback function to render the input field
        'bonsai_ai_deepgram',               // Page
        'bonsai_ai_deepgram_api_section'    // Section
    );
}
add_action('admin_init', 'bonsai_ai_register_deepgram_settings');

// Callback function to render the input field for the Deepgram API key
function bonsai_ai_deepgram_api_key_cb() {
    $api_key = get_option('bonsai_ai_deepgram_api_key');
    echo "<input type='text' name='bonsai_ai_deepgram_api_key' value='" . esc_attr($api_key) . "' />";
}
