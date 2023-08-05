// Sensei Upgrade: Sensei upgrade password field is hidden if deshi user is already logged in
jQuery(document).ready(function($) {
    $('#input_25_3').hide();
});

// Not working– Loading animation modal triggers on form submissions (Duplicated in theme's common.js)
// jQuery(document).ready(function($) {
//     // Select forms 32 and 33
//     $('#gform_22, #gform_23, #gform_32, #gform_33').on('submit', function(event) {
//         // Prevent the form from submitting immediately
//         event.preventDefault();
//
//         // Show the modal
//         $('#loadingModal').modal('show');
//
//         // Submit the form after a delay to allow the modal to show
//         setTimeout(() => {
//             event.target.submit();
//         }, 500);  // Delay in milliseconds
//     });
// });
