<?php
/**
 * Plugin Name: Bonsai AI
 * Description: A WordPress plugin that adds AI functionalities such as Sensei AI, and SenseiOS
 * Version: 0.0.1-alpha-0.29
 * Author: Jackalope Labs
 * Author URI: https://bonsai.so/
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Define BONSAI_AI_PLUGIN_FILE.
if ( ! defined( 'BONSAI_AI_PLUGIN_FILE' ) ) {
    define( 'BONSAI_AI_PLUGIN_FILE', __FILE__ );
}

// List of plugin files to include
$plugin_files = array(
    'ask-sensei',
    'chat',
    'checkin',
    'daily-checkins',
    'deshi',
    'directives',
    'filters',
    'followup',
    'journal-entries',
    'journal-prompts',
    'login',
    'merge-tags',
    'sensei',
    'set-goal',
    'subscribe'
);

// Include the plugin files.
foreach ($plugin_files as $file) {
    require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . $file . '.php';
}

// Sensei Upgrade: Sensei upgrade password field is hidden if deshi user is already logged in
function hide_field_for_logged_in_users() {
    if (is_user_logged_in()) {
        wp_enqueue_script('hide-form-field', plugins_url('/js/sensei.js', __FILE__), array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'hide_field_for_logged_in_users');
