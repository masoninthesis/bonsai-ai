console.log('deepgram.js loaded');

// Deepgram transcription trigger
jQuery(document).ready(function($) {
    // Deepgram transcription trigger
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

                    // Update the transcription result display
                    $('#transcriptionResult').text(response.data.transcript);

                    // Populate the transcription into the form input
                    $('#input_5_1').val(response.data.transcript);

                    // Automatically submit the form containing the transcription input
                    // Assuming '#input_5_1' is unique and directly within the form to be submitted
                    $('#input_5_1').closest('form').submit();
                } else {
                    console.error('Transcription Error:', response.data);
                    $('#transcriptionResult').text('Error: Transcription failed.');
                }
            },
            error: function(error) {
                console.error('AJAX Error:', error);
            }
        });
    });
});
