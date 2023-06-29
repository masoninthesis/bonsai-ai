<?php
// Functions to get all prompts and determine the next prompt id
function jp_get_all_prompts() {
    $args = array(
        'category_name'  => 'journal-prompts',
        'orderby'        => 'date',
        'order'          => 'ASC',
        'posts_per_page' => -1
    );
    $prompts = get_posts($args);
    return $prompts;
}

function jp_get_next_prompt_id($current_prompt_id) {
    $prompts = jp_get_all_prompts();

    $ids = array_map(function($post) {
        return $post->ID;
    }, $prompts);

    $current_index = array_search($current_prompt_id, $ids);

    if ($current_index !== false && $current_index < count($ids) - 1) {
        return $ids[$current_index + 1];
    }

    return false;
}

// Function to update the current journal prompt after form submission
function jp_update_current_journal_prompt($entry, $form) {
    if ($form['id'] != 21) {
        return;
    }

    $user_id = get_current_user_id();
    $current_prompt_id = get_user_meta($user_id, 'current_journal_prompt', true);
    $next_prompt_id = jp_get_next_prompt_id($current_prompt_id);

    if ($next_prompt_id !== false) {
        update_user_meta($user_id, 'current_journal_prompt', $next_prompt_id);
    }
}

add_action('gform_after_submission', 'jp_update_current_journal_prompt', 10, 2);
