console.log('deepgram.js loaded');

// Deepgram transcription trigger
jQuery(document).ready(function($) {
    $('#transcribeAudio').click(function() {
        console.log('Button clicked, starting transcription process...');

        $.ajax({
            type: "POST",
            url: bonsaiAiAjax.ajaxurl,
            data: {
                action: 'transcribe_audio',
                nonce: bonsaiAiAjax.nonce,
                // Optionally pass any other data like audio_url if needed
            },
            beforeSend: function() {
                console.log('Sending AJAX request to server...');
            },
            success: function(response) {
                console.log('AJAX request completed successfully.');
                if(response.success && response.data && response.data.transcript) {
                    console.log('Transcription Success:', response.data.transcript);
                    $('#transcriptionResult').text(response.data.transcript); // Display transcription
                } else {
                    console.error('Transcription Error:', response.data);
                    $('#transcriptionResult').text('Error: ' + response.data); // Display error message
                }
            },
            error: function(error) {
                console.error('AJAX Error:', error);
                console.log('Error details:', error.responseText);
            }
        });
    });
});
