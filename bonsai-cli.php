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
            'date_query' => [
                [
                    'after' => '1 day ago' // Fetch posts from the last 24 hours
                ]
            ],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'transcription_text',
                    'compare' => 'EXISTS',
                ],
                [
                    'key' => 'form_submitted',
                    'compare' => 'NOT EXISTS', // Check if the form submission flag is not set
                ],
            ],
        ]);

        foreach ($posts as $post) {
            $transcription_text = get_post_meta($post->ID, 'transcription_text', true);

            // Prepare and submit the form entry
            $entry = [
                'input_1' => $transcription_text,
                'input_2' => $post->ID,
            ];

            $result = GFAPI::submit_form($form_id, $entry);
            if (is_wp_error($result)) {
                WP_CLI::error(sprintf('Failed to submit form for post ID %d: %s', $post->ID, $result->get_error_message()));
            } else {
                WP_CLI::success(sprintf('Form submitted for post ID %d with transcription text.', $post->ID));
                // Mark the post as having the form submitted to avoid future submissions
                update_post_meta($post->ID, 'form_submitted', 'yes');
            }
        }
    }
}

WP_CLI::add_command('gf-auto-submit', 'GF_Auto_Submit_Command');


class GF_Auto_Transcribe_Command extends WP_CLI_Command {
    public function process($args, $assoc_args) {
        $posts = get_posts([
            'post_type' => 'notes',
            'posts_per_page' => -1,
            'date_query' => [
                [
                    'after' => '1 day ago'
                ]
            ],
            'meta_query' => [
                ['key' => 'transcription_text', 'compare' => 'NOT EXISTS'],
            ],
        ]);

        foreach ($posts as $post) {
            if (handle_transcription_for_cli($post->ID)) {
                WP_CLI::success("Transcription processed for post ID {$post->ID}.");
            } else {
                WP_CLI::warning("Transcription failed for post ID {$post->ID}.");
            }
        }
    }
}

WP_CLI::add_command('gf-auto-transcribe', 'GF_Auto_Transcribe_Command');
