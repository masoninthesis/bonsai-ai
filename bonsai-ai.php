<?php
/**
 * Plugin Name: Bonsai AI
 * Description: A WordPress plugin that adds AI functionalities
 * Version: 0.0.1-alpha
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
