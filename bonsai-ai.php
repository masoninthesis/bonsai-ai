<?php
/**
 * Plugin Name: Bonsai AI
 * Description: A WordPress plugin that adds AI functionalities such as Sensei AI, and SenseiOS
 * Version: 0.0.1-alpha-0.32
 * Author: Jackalope Labs
 * Author URI: https:/bonsai.so/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If WP-CLI is running, load the WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    require_once plugin_dir_path(__FILE__) . 'bonsai-cli.php';
}

// Define BONSAI_AI_PLUGIN_FILE.
if ( ! defined( 'BONSAI_AI_PLUGIN_FILE' ) ) {
	define( 'BONSAI_AI_PLUGIN_FILE', __FILE__ );
}

// Include the files.
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'merge-tags.php';
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'journal-prompts.php';
require_once plugin_dir_path(__FILE__) . 'journal-entries.php';
require_once plugin_dir_path(__FILE__) . 'ask-sensei.php';
require_once plugin_dir_path(__FILE__) . 'chat.php';
require_once plugin_dir_path(__FILE__) . 'filters.php';
require_once plugin_dir_path(__FILE__) . 'followup.php';
require_once plugin_dir_path( __FILE__ ) . 'set-goal.php';
require_once plugin_dir_path( __FILE__ ) . 'goal-checkin.php';
require_once plugin_dir_path( __FILE__ ) . 'sensei.php';
require_once plugin_dir_path( __FILE__ ) . 'subscribe.php';
require_once plugin_dir_path( __FILE__ ) . 'directives.php';
require_once plugin_dir_path( __FILE__ ) . 'daily-checkins.php';

// Sensei Upgrade: Sensei upgrade password field is hidden if deshi user is already logged in
function hide_field_for_logged_in_users() {
    if (is_user_logged_in()) {
        wp_enqueue_script('hide-form-field', plugins_url('/js/sensei.js', __FILE__), array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'hide_field_for_logged_in_users');
