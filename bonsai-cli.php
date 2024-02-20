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


class GF_Auto_Submit_Command extends WP_CLI_Command {
    public function process($args, $assoc_args) {
        $form_id = 5; // The ID of your Gravity Form
        $posts = get_posts([
            'post_type' => 'notes',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'transcription_text',
                    'compare' => 'EXISTS', // Changed to fetch posts with existing transcription_text
                ],
            ],
        ]);

        foreach ($posts as $post) {
            // Fetch the transcription text from post metadata
            $transcription_text = get_post_meta($post->ID, 'transcription_text', true);

            // Prepare the form entry with actual transcription text instead of dummy data
            $entry = [
                'input_1' => $transcription_text, // Use the actual transcription text
                'input_2' => $post->ID, // Post ID for field 2
            ];

            $result = GFAPI::submit_form($form_id, $entry);
            if (is_wp_error($result)) {
                WP_CLI::error(sprintf('Failed to submit form for post ID %d: %s', $post->ID, $result->get_error_message()));
            } else {
                WP_CLI::success(sprintf('Form submitted for post ID %d with transcription text.', $post->ID));
            }
        }
    }
}

WP_CLI::add_command('gf-auto-submit', 'GF_Auto_Submit_Command');


class GF_Auto_Transcribe_Command extends WP_CLI_Command {
    /**
     * Processes transcription for posts missing 'transcription_text' metadata.
     */
    public function process($args, $assoc_args) {
        $posts = get_posts([
            'post_type' => 'notes',
            'posts_per_page' => -1,
            'meta_query' => [
                ['key' => 'transcription_text', 'compare' => 'NOT EXISTS'],
            ],
        ]);

        foreach ($posts as $post) {
            if (handle_transcription_for_cli($post->ID)) {
                WP_CLI::success("Transcription processed for post ID {$post->ID}.");
            } else {
                WP_CLI::error("Transcription failed for post ID {$post->ID}.");
            }
        }
    }
}

WP_CLI::add_command('gf-auto-transcribe', 'GF_Auto_Transcribe_Command');
