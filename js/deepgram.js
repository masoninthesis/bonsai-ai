console.log('deepgram.js loaded');

// Deepgram transcription trigger
jQuery(document).ready(function($) {
    $('#transcribeAudio').click(function() {
        var postID = $(this).attr('data-post-id'); // Fetch the post ID

        $.ajax({
            type: "POST",
            url: bonsaiAiAjax.ajaxurl,
            data: {
                action: 'transcribe_audio',
                nonce: bonsaiAiAjax.nonce,
                post_id: postID, // Include the post ID in the request
            },
            beforeSend: function() {
                console.log('Sending AJAX request to server...');
            },
            success: function(response) {
                console.log('AJAX request completed successfully.');
                if(response.success && response.data && response.data.transcript) {
                    console.log('Transcription Success:', response.data.transcript);
                    $('#transcriptionResult').text(response.data.transcript); // Update text
                } else {
                    console.error('Transcription Error:', response.data);
                    $('#transcriptionResult').text('Error: Transcription failed.'); // Update with error message
                }
            },
            error: function(error) {
                console.error('AJAX Error:', error);
            }
        });
    });
});
