jQuery(document).ready(function($) {
    // Check if user is logged in
    if (bonsai_data.logged_in === 'true') {
        var $usernameField = $('#input_25_1');
        $usernameField.prop('readonly', true);

        // Create new style tag
        var newStyle = '<style>#input_25_1 { background-color: #acacac !important; }</style>';

        // Append new style tag to head
        $('head').append(newStyle);

        // Hide password field if user is logged in
        $('#input_25_3').prop('disabled', true); // Disable the password input field
        $('#input_25_3').hide(); // Hide the password input field
    }
});
