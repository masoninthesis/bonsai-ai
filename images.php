<?php
// Add custom image size
function bonsai_add_custom_image_size() {
    // Define new image size (name, width, height, crop)
    add_image_size('bonsai_thumbnail', 76, 76, true);
}

// Hook into the 'after_setup_theme' action
add_action('after_setup_theme', 'bonsai_add_custom_image_size');
