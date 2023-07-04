// $(document).on('gform_confirmation_loaded', function(event, formId){
//     if(formId == 23) {
//         $.ajax({
//             url: your_ajax_url,
//             method: 'POST',
//             data: {
//                 action: 'fetch_new_comment',
//                 form_id: formId,
//             },
//             success: function(response){
//                 $('#comment-section').append(response);
//             }
//         });
//     }
// });
//
// $('#comments-form').on('submit', function(e) {
//     e.preventDefault();
//
//     $.ajax({
//         url: ajaxurl, // WordPress defines this variable for you
//         method: 'POST',
//         data: {
//             action: 'add_comment', // This matches the action name you used when registering the AJAX action in your PHP script
//             post_id: $('#post_id').val(),
//             name: $('#name').val(),
//             comment: $('#comment').val(),
//         },
//         success: function(response){
//             if (response.success) {
//                 // Success
//                 $('#comment-section').append(response.data);
//             } else {
//                 // Failure
//                 console.error(response.data);
//             }
//         }
//     });
// });
