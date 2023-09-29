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
    // Handle copying for SenseiBlocks
    const copyTextElements = document.querySelectorAll(".copyText");
    const copyButtonElements = document.querySelectorAll(".copyButton");

    copyButtonElements.forEach((copyButton, index) => {
        const copyText = copyTextElements[index];

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

    // Handle copying for the merge tag
    const mergeTagElement = document.getElementById("mergeTag");
    const mergeCopyButton = document.getElementById("copyButton_mergeTag");  // Assuming you have a button with this ID

    // Copy SenseiOS
    mergeCopyButton.addEventListener("click", function () {
        // Make sure to capture the current inner text of the element
        const currentText = mergeTagElement.innerText || mergeTagElement.textContent;

        // Create a temporary textarea to copy the text
        const tempTextArea = document.createElement("textarea");
        tempTextArea.value = currentText;
        document.body.appendChild(tempTextArea);

        // Select the text
        tempTextArea.select();

        // Perform the copy operation
        document.execCommand("copy");

        // Remove the temporary textarea
        document.body.removeChild(tempTextArea);

        // Add a visual indication (change button text to the icon)
        mergeCopyButton.innerHTML = "<i class='fas fa-check'></i> Copied!";

        // Reset the button text after a brief delay (optional)
        setTimeout(function () {
            mergeCopyButton.innerHTML = "<i class='fas fa-copy'></i> Copy SenseiOS";
        }, 1000); // Reset after 1 second
    });
});
