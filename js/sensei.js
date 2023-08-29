// Sensei Upgrade: Sensei upgrade password field is hidden if deshi user is already logged in
jQuery(document).ready(function($) {
    $('#input_25_3').hide();
});

// Private vs Public Goals Toggle
/* global myLocalizedVars */

jQuery(document).ready(function($) {
    // Initialize toggle status text based on checkbox state
    $('#private-toggle').is(':checked') ? $('#toggle-status').text('Private') : $('#toggle-status').text('Public');

    // Listen for toggle changes
    $('#private-toggle').on('change', function() {
        var postID = $(this).data('post-id');
        var newStatus = $(this).is(':checked') ? 'private' : 'publish';

        // Update the status text
        newStatus === 'private' ? $('#toggle-status').text('Private') : $('#toggle-status').text('Public');

        $.ajax({
            url: myLocalizedVars.ajaxurl,
            type: 'POST',
            data: {
                action: 'toggle_post_status',
                post_id: postID,
                new_status: newStatus,
                security: myLocalizedVars.security
            },
            success: function(response) {
                console.log('Post status updated:', response);
            }
        });
    });
});

// Deshi pricing boxes selects product dropdown by value
jQuery(document).ready(function() {
    jQuery('.card').on('click', function() {
        var selectedTier = jQuery(this).data('tier');
        jQuery('#input_31_4').val(selectedTier);
    });
});
