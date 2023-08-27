<?php
// This file handles functionality related to goals

// Goal post deletion
function handle_delete_post() {
    if (isset($_POST['action']) && $_POST['action'] === 'delete_post' && isset($_POST['post_id'])) {
        $post_id = intval($_POST['post_id']);
        wp_trash_post($post_id); // Move the post to the trash
    }
}
add_action('admin_post_delete_post', 'handle_delete_post');
add_action('admin_post_nopriv_delete_post', 'handle_delete_post');
