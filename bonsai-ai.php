<?php
/**
 * Plugin Name: Bonsai AI
 * Description: A WordPress plugin that adds AI functionalities such as Sensei AI, and SenseiOS
 * Version: 0.0.1-alpha-0.90
 * Author: Jackalope Labs
 * Author URI: https://bonsai.so/
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

// Include the files
// Merge tags for Gravity Forms
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'merge-tags.php';
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'shortcodes.php';

// Journal prompts functionality
require_once plugin_dir_path( BONSAI_AI_PLUGIN_FILE ) . 'journal-prompts.php';

// Journal entries functionality
require_once plugin_dir_path(__FILE__) . 'journal-entries.php';

// Chat functionality
require_once plugin_dir_path(__FILE__) . 'chat.php';

// Filters for various functionalities
require_once plugin_dir_path(__FILE__) . 'filters.php';

// Deshi user functionality
require_once plugin_dir_path(__FILE__) . 'deshi.php';

// Set goal functionality
require_once plugin_dir_path( __FILE__ ) . 'login.php';

// SenseiModules functionality
// Ask Sensei functionality
require_once plugin_dir_path( __FILE__ ) . 'modules/ask-sensei.php';
// Daily checkin functionality
require_once plugin_dir_path( __FILE__ ) . 'modules/daily-checkin.php';
// Set goal functionality
require_once plugin_dir_path( __FILE__ ) . 'modules/goal-setting.php';
// Pitch Sensei
require_once plugin_dir_path( __FILE__ ) . 'modules/pitch-sensei.php';

// Goal check-in functionality
require_once plugin_dir_path( __FILE__ ) . 'goal-checkin.php';

// Goals functionality
require_once plugin_dir_path( __FILE__ ) . 'goals.php';

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

// Daily check-ins functionality
// require_once plugin_dir_path( __FILE__ ) . 'daily-checkins.php';

// Include the admin menu
require_once plugin_dir_path( __FILE__ ) . 'admin/admin-menu.php';

// Include Sendgrid settings
require_once plugin_dir_path( __FILE__ ) . 'admin/sendgrid-settings.php';


// Cron Jobs
require_once plugin_dir_path(__FILE__) . 'cron-jobs.php';
setup_deshi_autoresponse_hooks(__FILE__);

// Sensei Upgrade: Sensei upgrade password field is hidden if deshi user is already logged in
function hide_field_for_logged_in_users() {
    if (is_user_logged_in()) {
        wp_enqueue_script('hide-form-field', plugins_url('/js/sensei.js', __FILE__), array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'hide_field_for_logged_in_users');
