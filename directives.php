<?php
/**
 * Workaround to add custom Blade directives– use: @if(is_user_subscribed_to($post->post_author)) instead of @subscribed
 */
 function is_user_subscribed_to($sensei_id) {
     // Get the currently logged-in user
     $current_user = wp_get_current_user();

     // Check if the user is logged in
     if ($current_user->ID != 0) {
         // Get the sensei_ids from the user meta data
         $sensei_ids = get_user_meta($current_user->ID, 'sensei_ids', true);

         // Check if the sensei_ids is an array and if the given sensei_id is in the array
         return is_array($sensei_ids) && in_array($sensei_id, $sensei_ids);
     }

     return false;
 }
