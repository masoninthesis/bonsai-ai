<?php
// SenseiOS fields shortcode
function senseios_fields_shortcode( $atts ) {
    return display_senseios_fields();
}
add_shortcode('senseios_fields', 'senseios_fields_shortcode');
