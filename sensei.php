<?php
// Post creation and redirect for Sensei Signup

add_action( 'gform_after_submission_25', 'create_post_and_redirect', 10, 2 );
function create_post_and_redirect( $entry, $form ) {

    $username = rgar( $entry, '1' );
    $password = rgar( $entry, '3' );
    $email    = rgar( $entry, '2' );

    // Create empty password string if user is logged in so password doesn't change
    $password = '';
    if ( ! is_user_logged_in() ) {
        $password = rgar( $entry, '3' );
    }

    // Check if the user exists
    $user_id = username_exists( $username );

    if ( ! $user_id && email_exists($email) == false ) { // User doesn't exist, create a new user
        $user_id = wp_create_user( $username, $password, $email );
    }

    $user = new WP_User($user_id);

    // Add 'sensei' role to the user
    $user->add_role('sensei');

    // Check if user was created successfully
    if ( ! is_wp_error( $user_id ) ) {
        // Post data
        $post_data = array(
            'post_title'    => rgar( $entry, '1' ), // replace '1' with the ID of your Post Title field
            'post_content'  => rgar( $entry, '1' ), // replace '1' with the ID of your Post Content field
            'post_status'   => 'publish',
            'post_author'   => $user_id,
            'post_type'     => 'sensei' // change from 'post' to 'sensei'
        );

        // Insert the post and get the ID
        $post_id = wp_insert_post( $post_data );


        // Check if post was created successfully
        if ( ! is_wp_error( $post_id ) ) {
            // Get the URL of the new post
            $url = get_permalink( $post_id );

            if( ! is_user_logged_in() ){ // Only sign in the user if they're not logged in
                // Log in the user
                $creds = array(
                    'user_login'    => $username,
                    'user_password' => $password,
                    'remember'      => true
                );

                $user = wp_signon( $creds, false );

                if ( is_wp_error( $user ) ) {
                    error_log( "Failed to log in user. Error: " . $user->get_error_message() );
                }
            }

            // Log the redirection for debugging
            error_log( "Redirecting to post with ID: " . $post_id );

            // Redirect to the new post
            wp_redirect( $url );
            exit;
        } else {
            error_log( "Failed to create post. Error: " . $post_id->get_error_message() );
        }
    } else {
        error_log( "Failed to create user. Error: " . $user_id->get_error_message() );
    }
}

// Prepopulate Username and Email for Logged-In Users

add_filter( 'gform_field_value_username', 'populate_username' );
function populate_username( $value ) {
    $current_user = wp_get_current_user();
    return $current_user->user_login; // Return the username of the logged in user
}

add_filter( 'gform_field_value_email', 'populate_email' );
function populate_email( $value ) {
    $current_user = wp_get_current_user();
    return $current_user->user_email; // Return the email of the logged in user
}

// Custom Post Type for sensei profile
add_action('init', 'create_sensei_post_type');

function create_sensei_post_type() {
    register_post_type('sensei',
        array(
            'labels' => array(
                'name' => __('Senseis'),
                'singular_name' => __('Sensei')
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'sensei'),
            'show_in_rest' => true,
            'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions'),
        )
    );
}

// Sensei Post Type Excerpts
function new_excerpt_more($more) {
    global $post;
    if($post->post_type == 'sensei'){
        return ' ... <strong>Subscribe to read more</strong>.';
    }
    return $more;
}
add_filter('excerpt_more', 'new_excerpt_more', 999);

// SenseiOS Profile ACF Field Groups Editability
function senseios_all_head() {
    // Check if the acf_form function exists
    if ( ! function_exists('acf_form_head') ) {
        return;
    }
    acf_form_head();
}
add_action('get_header', 'senseios_all_head');

// SenseiOS All fields in a form shortcode
function senseios_all_content() {
    if ( ! function_exists('acf_form') ) {
        return;
    }

    acf_enqueue_uploader(); // Needed for form to function properly

    $field_groups = [];
    for($i = 1; $i <= 150; $i++) {
        $field_group_id = 'group_' . sprintf('%03d', $i);
        if( acf_get_field_group($field_group_id) ) {
            $field_groups[] = $field_group_id;
        }
    }

    // Loop through each field group and create fields for each one
    foreach ($field_groups as $group_id) {
        acf_form(array(
            'post_id' => get_the_ID(), // use the ID of the current post
            'field_groups' => [$group_id], // pass single field group each time
            'form' => false, // set form to false
            'return' => add_query_arg( 'updated', 'true', get_permalink() ),
            'html_before_fields' => '',
            'html_after_fields' => '', // don't add a submit button after each field group
            'submit_value' => '',
        ));
    }

    // Add the senseiOS field group
    acf_form(array(
        'post_id' => get_the_ID(),
        'field_groups' => ['group_64b061d56b6cc'], // Only include the 'senseiOS' field group
        'form' => true,
        'return' => add_query_arg( 'updated', 'true', get_permalink() ),
        'html_before_fields' => '',
        'html_after_fields' => '', // add a single submit button after all fields
        'submit_value' => 'Update',
    ));
}
add_shortcode('senseios_all', 'senseios_all_content');

// SenseiOS Forms Shortcodes
function senseios_shortcode($atts) {
    // Shortcode attributes
    $atts = shortcode_atts(
        array(
            'field' => 'senseios_1',
        ),
        $atts
    );

    $post_id = get_the_ID(); // Get current post ID
    $author_id = get_post_field('post_author', $post_id); // Get post author ID
    $current_user_id = get_current_user_id(); // Get current logged in user ID
    $output = '';

    // Check if current user is the author of the post
    if ($author_id == $current_user_id) {
        ob_start();
        $form = array(
            'id' => 'acf-form',
            'post_id' => $post_id,
            'fields' => array($atts['field']), // The name of your group field
            'return' => '', // Return URL
            'submit_value' => 'Update' // Text for the submit button
        );
        acf_form($form);
        $output = ob_get_clean();
    }

    return $output;
}

add_shortcode('senseios', 'senseios_shortcode');
