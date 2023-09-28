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

// Click to copy SenseiBlocks and full SenseiOS
document.addEventListener("DOMContentLoaded", function () {
    const copyText = document.getElementById("copyText");
    const copyButton = document.getElementById("copyButton");

    copyButton.addEventListener("click", function () {
        // Create a range to select the text within the element
        const range = document.createRange();
        range.selectNode(copyText);

        // Create a selection object and select the text
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);

        // Copy the selected text to the clipboard
        document.execCommand("copy");

        // Deselect the text
        selection.removeAllRanges();

        // Add a visual indication (change button text to the icon)
        copyButton.innerHTML = "<i class='fas fa-check'></i> Copied!";

        // Reset the button text after a brief delay (optional)
        setTimeout(function () {
            copyButton.innerHTML = "<i class='fas fa-copy'></i> Copy";
        }, 1000); // Reset after 1 second
    });
});
