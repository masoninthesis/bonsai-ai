<?php
// Add icon to a GF buttons
// Form 25
add_filter( 'gform_submit_button_25', function ( $button, $form ) {
  return "<div class='col-sm'><button class='btn button gform_button' id='gform_submit_button_{$form['id']}'><span class='btn-copy mr-3'>Become a Sensei</span><i class='fas fa-arrow-right'></i></button></div>";
}, 10, 2);
// Form 26
add_filter( 'gform_submit_button_26', function ( $button, $form ) {
  return "<div class='col-sm'><button class='btn button gform_button' id='gform_submit_button_{$form['id']}'><span class='btn-copy mr-3'>Subscribe to Sensei</span><i class='fas fa-arrow-right'></i></button></div>";
}, 10, 2);
