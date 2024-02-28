<?php
// Prevent direct file access
if (!defined('ABSPATH')) {
    exit;
}

// Register AJAX actions for authenticated and non-authenticated users
add_action('wp_ajax_transcribe_audio', 'handle_transcription');
add_action('wp_ajax_nopriv_transcribe_audio', 'handle_transcription');

function handle_transcription() {
    // Check if post ID is provided
    if (!isset($_POST['post_id'])) {
        wp_send_json_error('Post ID not provided');
        return;
    }

    $post_id = sanitize_text_field($_POST['post_id']);
    // Original dynamic retrieval from post metadata (replace this line)
    // $audio_url = get_post_meta($post_id, 'uploaded_file_url', true);

    // Hardcoded URL for testing
    $audio_url = 'https://staging.apollohealthmd.com/app/uploads/gravity_forms/4-7f177ef23b77d6fa5d6c869ca01029d1/2024/02/recording_2024-02-06T23-30-35.webm';


    if (empty($audio_url)) {
        wp_send_json_error('No audio URL found for post ID: ' . $post_id);
        return;
    }

    $api_key = get_option('bonsai_ai_deepgram_api_key');
    $deepgram_url = 'https://api.deepgram.com/v1/listen?smart_format=true&model=nova-2&language=en-US';

    $response = wp_remote_post($deepgram_url, array(
       'method' => 'POST',
       'timeout' => 45,
       'headers' => array(
           'Authorization' => 'Token ' . $api_key,
           'Content-Type' => 'application/json',
       ),
       'body' => json_encode(array(
           'url' => $audio_url,
           'model' => 'nova-2',
           'language' => 'en-US',
           'smart_format' => true,
           'diarize' => true, // Enable diarization
       )),
    ));


    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('Connection to Deepgram API failed: ' . $error_message);
        wp_send_json_error("Connection to Deepgram API failed: $error_message");
        return;
    }

    $body = wp_remote_retrieve_body($response);
    error_log('Full Deepgram Response: ' . $body); // Log the raw JSON response
    $transcription = json_decode($body, true);

    if (isset($transcription['results']) && !empty($transcription['results'])) {
        foreach ($transcription['results']['channels'][0]['alternatives'][0]['words'] as $word) {
            $speaker = isset($word['speaker']) ? $word['speaker'] : 'unknown';
            error_log("Speaker {$speaker}: {$word['word']} ({$word['start']} - {$word['end']})");
        }

        $transcriptText = $transcription['results']['channels'][0]['alternatives'][0]['transcript'];
        update_post_meta($post_id, 'transcription_text', $transcriptText);
        wp_send_json_success(array('transcript' => $transcriptText));
    } else {
        wp_send_json_error('Failed to transcribe audio');
    }
}

function handle_transcription_for_cli($post_id) {
    if (empty($post_id)) {
        error_log('handle_transcription_for_cli: Post ID not provided.');
        return false;
    }

    // Retrieve the audio URL from post metadata or use a hardcoded URL for testing
    // $audio_url = get_post_meta($post_id, 'uploaded_file_url', true);
    // Uncomment the next line if you need to use a hardcoded URL for testing
    $audio_url = 'https://staging.apollohealthmd.com/app/uploads/gravity_forms/4-7f177ef23b77d6fa5d6c869ca01029d1/2024/02/recording_2024-02-06T23-30-35.webm';

    if (empty($audio_url)) {
        error_log("handle_transcription_for_cli: No audio URL found for post ID: {$post_id}");
        return false;
    }

    $api_key = get_option('bonsai_ai_deepgram_api_key');
    if (empty($api_key)) {
        error_log('handle_transcription_for_cli: Deepgram API key not configured.');
        return false;
    }

    $deepgram_url = 'https://api.deepgram.com/v1/listen';
    $response = wp_remote_post($deepgram_url, [
        'method'    => 'POST',
        'timeout'   => 45,
        'headers'   => [
            'Authorization' => 'Token ' . $api_key,
            'Content-Type'  => 'application/json',
        ],
        'body'      => json_encode([
            'url'           => $audio_url,
            'model'         => 'latest',
            'language'      => 'en-US',
            'punctuate'     => true,
            'diarize'       => true,
        ]),
    ]);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log("handle_transcription_for_cli: Connection to Deepgram API failed for post ID {$post_id}: {$error_message}");
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $transcription = json_decode($body, true);

    // Log the full Deepgram response for debugging purposes
    error_log("handle_transcription_for_cli: Deepgram Response for post ID {$post_id}: " . print_r($body, true));

    if (isset($transcription['results']) && !empty($transcription['results']['channels'][0]['alternatives'][0]['transcript'])) {
        $transcriptText = $transcription['results']['channels'][0]['alternatives'][0]['transcript'];
        update_post_meta($post_id, 'transcription_text', $transcriptText);
        error_log("handle_transcription_for_cli: Transcription success for post ID {$post_id}");
        return true;
    } else {
        error_log("handle_transcription_for_cli: Failed to transcribe audio for post ID {$post_id}.");
        return false;
    }
}

// Working diarization curl
// curl --request POST \
//   --url 'https://api.deepgram.com/v1/listen?diarize=true&punctuate=true&utterances=true' \
//   --header 'Authorization: Token api_key' \
//   --header 'Content-Type: application/json' \
//   --data '{"url":"https://staging.apollohealthmd.com/app/uploads/gravity_forms/4-7f177ef23b77d6fa5d6c869ca01029d1/2024/02/recording_2024-02-06T23-30-35.webm"}'
// Note: Enqueuing scripts might not be necessary for this simplified version,
// but you'll need it when integrating with the front-end.

// Note: Enqueuing scripts might not be necessary for this simplified version,
// but you'll need it when integrating with the front-end.

// This curl is working
  // curl \
  //   -X POST \
  //   "https://api.deepgram.com/v1/listen?smart_format=true&model=nova-2&language=en-US" \
  //   -H "Authorization: Token 4dd9c6d653be146851fb17c19d6e7b457da4ac85" \
  //   -H 'content-type: application/json' \
  //   -d '{"url":"https://staging.apollohealthmd.com/app/uploads/gravity_forms/4-7f177ef23b77d6fa5d6c869ca01029d1/2024/02/recording_2024-02-03T23-44-16.webm"}'
