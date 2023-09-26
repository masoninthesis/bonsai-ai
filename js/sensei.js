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

// Pitch Sensei Sections
jQuery(document).ready(function($) {
    // Function to update class based on textarea value
    function updateClass(textarea, label) {
        if (textarea.val().trim() !== '') {
            label.addClass('text-primary');
        } else {
            label.removeClass('text-primary');
        }
    }

    // Loop through each textarea and its corresponding label
    $('[id^=input_40_]').each(function() {
        var textarea = $(this);
        var label = textarea.closest('li').find('span.mr-3');

        // Update class based on initial value
        updateClass(textarea, label);

        // Attach event listener for future changes
        textarea.on('input', function() {
            updateClass(textarea, label);
        });
    });
});
