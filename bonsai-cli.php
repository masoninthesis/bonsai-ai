<?php
WP_CLI::add_command('bonsai', 'Bonsai_Command');

class Bonsai_Command extends WP_CLI_Command {

    /**
     * Subcommand for Deshi AI.
     */
    public function deshi($args, $assoc_args) {
        if(isset($args[0]) && $args[0] === 'autoresponse') {
            $user_id = isset($assoc_args['user']) ? intval($assoc_args['user']) : null;

            // Make $user_id available in autoresponse.php via a global variable or constant
            if ($user_id) {
                define('AUTO_RESPONSE_USER_ID', $user_id);
            }

            include(plugin_dir_path(__FILE__) . 'autoresponse.php');
        } else {
            WP_CLI::error("Invalid command for deshi.");
        }
    }
}
