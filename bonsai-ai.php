<?php
/**
 * Plugin Name: Bonsai AI
 * Description: A WordPress plugin that adds AI functionalities such as Sensei AI, and SenseiOS
 * Version: 0.0.1-alpha-0.18
 * Author: Jackalope Labs
 * Author URI: https:/bonsai.so/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define BONSAI_AI_PLUGIN_FILE.
if ( ! defined( 'BONSAI_AI_PLUGIN_FILE' ) ) {
	define( 'BONSAI_AI_PLUGIN_FILE', __FILE__ );
}

// Include the files.
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'merge-tags.php';
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'journal-prompts.php';
require_once plugin_dir_path(__FILE__) . 'journal-entries.php';
require_once plugin_dir_path(__FILE__) . 'chat.php';
require_once plugin_dir_path(__FILE__) . 'filters.php';
require_once plugin_dir_path( __FILE__ ) . 'sensei.php';
require_once plugin_dir_path( __FILE__ ) . 'subscribe.php';
require_once plugin_dir_path( __FILE__ ) . 'directives.php';
require_once plugin_dir_path( __FILE__ ) . 'daily-checkins.php';
