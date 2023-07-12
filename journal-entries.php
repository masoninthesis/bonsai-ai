<?php
// Populat Prompt Post Category
add_filter('gform_field_value_prompt_category', 'populate_prompt_category');

function populate_prompt_category($value) {
    // Fetch the category by its slug.
    // Replace 'my-category' with the slug of your category.
    $category = get_category_by_slug('values-journal-prompts');

    // If the category is found, return its name.
    if ($category) {
        return $category->name;
    }

    // If no category was found, return an empty string.
    return '';
}

// Dynamically populate post category field
add_filter( 'gform_field_value_post_category', function( $value ) {
    global $post;
    if ( is_object($post) ) {
        $categories = get_the_category( $post->ID );
    } else {
        error_log( 'Global $post is not an object.' );
    }

    $category_slugs = array();
    if ( ! empty( $categories ) ) {
        foreach( $categories as $category ) {
            $category_slugs[] = $category->slug;
        }
    }
    return implode( ',', $category_slugs ); // return category slugs as a comma-separated string
} );


// add the action
add_action('gform_after_submission_27', 'create_post', 10, 2);
function create_post($entry, $form) {
    $post_category_string = rgar($entry, '2'); // ID of your hidden field
    $post_categories = explode(',', $post_category_string); // convert string to array

    // Mapping array
    $category_mapping = array(
        'values-journal-prompts' => 'values-journal-entries',
        'dreams-journal-prompts' => 'dreams-journal-entries',
        'fears-journal-prompts' => 'fears-journal-entries',
        'fights-journal-prompts' => 'fights-journal-entries',
        'flow-journal-prompts' => 'flow-journal-entries',
        'likeness-journal-prompts' => 'likeness-journal-entries',
        'custom-prompt-journal-prompts' => 'custom-prompt-journal-entries'
    );

    // Initialize new post categories
    $new_post_categories = array('journal-entries');

    // Loop through the category mapping
    foreach ($category_mapping as $prompt => $entry_category) {
        if (in_array($prompt, $post_categories)) {
            $new_post_categories[] = $entry_category;
        }
    }

    $post_data = array(
        'post_title'    => rgar($entry, '1'),
        'post_type'     => 'post',
        'post_status'   => 'publish',
        'post_excerpt'  => rgar($entry, '3'),  // use field 3 for the excerpt
        'post_content'  => rgar($entry, '4'),  // use field 4 for the content
    );


    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    if(!is_wp_error($post_id)){
        //the post is valid
        $result = wp_set_object_terms($post_id, $new_post_categories, 'category');

        // If there's an error, let's log it
        if(is_wp_error($result)) {
            error_log('Error setting categories: ' . $result->get_error_message());
        }

        error_log('Post created: ' . rgar($entry, '1'));
    } else {
        //there was an error in the post insertion,
        error_log($post_id->get_error_message());
    }
}


// add_action( 'gform_after_create_post_21', 'custom_update_post_excerpt', 10, 3 );
//
// function custom_update_post_excerpt( $post_id, $entry, $form ) {
//     error_log( 'gform_after_create_post_21 called.' );
//     error_log( 'Post ID: ' . $post_id );
//     error_log( 'Entry: ' . print_r( $entry, true ) );
//     error_log( 'Form: ' . print_r( $form, true ) );
//
//     $excerpt = rgar( $entry, '6' );
//
//     if ( $post_id && $excerpt ) {
//         wp_update_post( array(
//             'ID' => $post_id,
//             'post_excerpt' => $excerpt,
//         ) );
//         error_log( 'Post excerpt updated.' );
//     } else {
//         error_log( 'Post excerpt not updated - Missing Post ID or Excerpt.' );
//     }
// }
