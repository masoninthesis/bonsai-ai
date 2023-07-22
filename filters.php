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

// Gravity Forms passes custom parameter through shortcode
function customize_gravity_form_shortcode($out, $pairs, $atts) {
    // Check if the 'product' attribute is set
    if (isset($atts['product'])) {
        // Add a filter to pre-populate the field
        add_filter('gform_field_value_product', function() use ($atts) {
            return $atts['product'];
        });
    }

    return $out;
}
add_filter('shortcode_atts_gravityforms', 'customize_gravity_form_shortcode', 10, 3);
