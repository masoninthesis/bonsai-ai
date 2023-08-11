<?php
WP_CLI::add_command('bonsai', 'Bonsai_Command');

class Bonsai_Command extends WP_CLI_Command {

    /**
     * Subcommand for Deshi AI.
     */
    public function deshi($args, $assoc_args) {
        if(isset($args[0]) && $args[0] === 'autoresponse') {
            include(plugin_dir_path(__FILE__) . 'autoresponse.php');
        } else {
            WP_CLI::error("Invalid command for deshi.");
        }
    }
}
