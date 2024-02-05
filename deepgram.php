<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX actions for authenticated and non-authenticated users
add_action('wp_ajax_transcribe_audio', 'handle_transcription');
add_action('wp_ajax_nopriv_transcribe_audio', 'handle_transcription');

function handle_transcription() {
    // The audio URL you want to transcribe
    $audio_url = 'https://staging.apollohealthmd.com/app/uploads/gravity_forms/4-7f177ef23b77d6fa5d6c869ca01029d1/2024/02/recording_2024-02-03T23-44-16.webm';

    // Set up Deepgram API request with nova-2 model and smart formatting
    $api_key = '4dd9c6d653be146851fb17c19d6e7b457da4ac85'; // Use your actual Deepgram API key
    $deepgram_url = 'https://api.deepgram.com/v1/listen';
    $request_body = array(
        'url' => $audio_url,
        'model' => 'nova-2',
        'language' => 'en-US',
        'smart_format' => true
    );

    $response = wp_remote_post($deepgram_url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(
            'Authorization' => 'Token ' . $api_key,
            'Content-Type' => 'application/json',
        ),
        'body' => json_encode($request_body),
        'cookies' => array()
    ));

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        // Log the error message to PHP error log
        error_log('Connection to Deepgram API failed: ' . $error_message);
        wp_send_json_error("Connection to Deepgram API failed: $error_message");
        return;
    }

    $body = wp_remote_retrieve_body($response);
    $transcription = json_decode($body, true);

    if (isset($transcription['results']) && !empty($transcription['results'])) {
        // Log successful transcription to PHP error log
        error_log('Transcription Success: ' . print_r($transcription['results'], true));
        wp_send_json_success($transcription['results']);
    } else {
        // Log failure to transcribe audio
        error_log('Failed to transcribe audio: ' . print_r($transcription, true));
        wp_send_json_error('Failed to transcribe audio');
    }
}

// Note: Enqueuing scripts might not be necessary for this simplified version,
// but you'll need it when integrating with the front-end.

// This curl is working
  // curl \
  //   -X POST \
  //   "https://api.deepgram.com/v1/listen?smart_format=true&model=nova-2&language=en-US" \
  //   -H "Authorization: Token 4dd9c6d653be146851fb17c19d6e7b457da4ac85" \
  //   -H 'content-type: application/json' \
  //   -d '{"url":"https://staging.apollohealthmd.com/app/uploads/gravity_forms/4-7f177ef23b77d6fa5d6c869ca01029d1/2024/02/recording_2024-02-03T23-44-16.webm"}'
