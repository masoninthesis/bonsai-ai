<?php
function bonsai_ai_transcribe_audio($post_id) {
    // Ensure you have the post ID and the audio file URL metadata is set
    $audio_file_url = get_post_meta($post_id, 'uploaded_file_url', true);

    if (empty($audio_file_url)) {
        error_log("No audio file URL found for post ID: $post_id");
        return; // Exit if there is no audio file URL
    }

    $deepgram_api_key = '4dd9c6d653be146851fb17c19d6e7b457da4ac85'; // Securely store and retrieve this
    $deepgram_endpoint = 'https://api.deepgram.com/v1/listen';

    // Adjust the payload to include the parameters you want to use
    $post_fields = json_encode([
        'url' => $audio_file_url,
        'language' => 'en',
        'model' => 'nova-2'
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $deepgram_endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Token ' . $deepgram_api_key,
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        error_log("cURL Error when trying to reach Deepgram: $err");
    } else {
        error_log("Response from Deepgram for post ID $post_id: $response");
        $decoded_response = json_decode($response, true);

        // Check if the transcription was successful and the expected data is present
        if (!empty($decoded_response['results']['channels'][0]['alternatives'][0]['transcript'])) {
            $transcript = $decoded_response['results']['channels'][0]['alternatives'][0]['transcript'];
            update_post_meta($post_id, 'audio_transcript', $transcript);
            error_log("Transcription saved for post ID $post_id: $transcript");
        } else {
            error_log("Failed to retrieve transcription for post ID $post_id or transcription was empty.");
        }
    }
}

// This is the Trigger for the transcription, when a Note page is saved
add_action('wp_insert_post', 'bonsai_ai_handle_audio_transcription', 10, 3);

function bonsai_ai_handle_audio_transcription($post_id, $post, $update) {
    // Check if it's a custom post type and if it's not an update
    if ('notes' === $post->post_type && !$update) {
        // The check for !$update is not necessary here as wp_insert_post is for new posts
        bonsai_ai_transcribe_audio($post_id);
    }
}
