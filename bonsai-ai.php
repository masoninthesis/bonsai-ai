<?php
/**
 * Plugin Name: Apollo AI
 * Description: A WordPress plugin that adds AI functionalities such as Sensei AI, and SenseiOS
 * Version: 0.0.1-apollo-0.0.21
 * Author: Jackalope Labs
 * Author URI: https://jackalope.io/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define BONSAI_AI_PLUGIN_FILE.
if ( ! defined( 'BONSAI_AI_PLUGIN_FILE' ) ) {
	define( 'BONSAI_AI_PLUGIN_FILE', __FILE__ );
}

// If WP-CLI is running, load the WP-CLI commands
if (defined('WP_CLI') && WP_CLI) {
    require_once plugin_dir_path(__FILE__) . 'bonsai-cli.php';
}

// Include the files
// Apollo AI functionality
require_once plugin_dir_path(__FILE__) . 'apollo.php';
require_once plugin_dir_path(__FILE__) . 'deepgram.php';

// Merge tags for Gravity Forms
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'merge-tags.php';
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'shortcodes.php';

// Filters for various functionalities
require_once plugin_dir_path(__FILE__) . 'filters.php';

// Set goal functionality
require_once plugin_dir_path( __FILE__ ) . 'login.php';

// Image optimization functionality
require_once plugin_dir_path( __FILE__ ) . 'images.php';

// Pagination functionality
require_once plugin_dir_path( __FILE__ ) . 'pagination.php';

// Sensei functionality
require_once plugin_dir_path( __FILE__ ) . 'sensei.php';

// Signup functionality
require_once plugin_dir_path( __FILE__ ) . 'signup.php';

// Subscribe functionality
require_once plugin_dir_path( __FILE__ ) . 'subscribe.php';

// Spam functionality
require_once plugin_dir_path( __FILE__ ) . 'spam.php';

// Directives functionality
require_once plugin_dir_path( __FILE__ ) . 'directives.php';

// Include the admin menu
require_once plugin_dir_path( __FILE__ ) . 'admin/admin-menu.php';

// Include Sendgrid settings
require_once plugin_dir_path( __FILE__ ) . 'admin/sendgrid-settings.php';

// Include Deepgram settings
require_once plugin_dir_path( __FILE__ ) . 'admin/deepgram-settings.php';

// Cron Jobs
require_once plugin_dir_path(__FILE__) . 'cron-jobs.php';
setup_deshi_autoresponse_hooks(__FILE__);

// Apollo and Bonsai JavaScript
function apollo_ai_enqueue_scripts() {
    // Define the path to the Apollo JavaScript file
    $apollo_js_path = plugin_dir_url( __FILE__ ) . 'js/apollo.js';
    // Enqueue the Apollo script
    wp_enqueue_script( 'bonsai-ai-apollo', $apollo_js_path, array(), '1.0.0', true );

    // Define the path to the Bonsai JavaScript file
    $bonsai_js_path = plugin_dir_url( __FILE__ ) . 'js/bonsai.js';
    // Enqueue the Bonsai script
    wp_enqueue_script( 'bonsai-ai-bonsai', $bonsai_js_path, array(), '1.0.0', true );
}
add_action( 'wp_enqueue_scripts', 'apollo_ai_enqueue_scripts' );

// AJAX and Deepgram
function deepgram_enqueue_scripts() {
    wp_enqueue_script('bonsai-ai-script', plugin_dir_url(__FILE__) . 'js/deepgram.js', array('jquery'), null, true);
    wp_localize_script('bonsai-ai-script', 'bonsaiAiAjax', array('ajaxurl' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('transcribe_audio_nonce')));
}

add_action('wp_enqueue_scripts', 'deepgram_enqueue_scripts');
