<?php
// Add the action hook
add_action('gform_after_submission', 'assign_journal_entry_category', 10, 2);

function assign_journal_entry_category($entry, $form) {
    // Check if the form is your desired form
    if ($form['id'] != '21') {
        return;
    }

    // Get the ID of the created post
    $post_id = $entry['post_id'];

    // Get the post object
    $post = get_post($post_id);

    // Get the post categories
    $categories = wp_get_post_categories($post_id);

    // Map each "prompt" category to its "entry" counterpart
    $category_mapping = [
        'values-journal-prompts' => 'values-journal-entries',
        'dreams-journal-prompts' => 'dreams-journal-entries',
        'fears-journal-prompts' => 'fears-journal-entries',
        'fights-journal-prompts' => 'fights-journal-entries',
        'flow-journal-prompts' => 'flow-journal-entries',
        'likeness-journal-prompts' => 'likeness-journal-entries',
        'custom-prompt-journal-prompts' => 'custom-prompt-journal-entries',
    ];

    // Go through each category and check if it needs to be replaced
    foreach ($categories as $i => $cat_id) {
        $cat = get_category($cat_id);
        if (array_key_exists($cat->slug, $category_mapping)) {
            // Replace with "entry" category
            $new_cat_id = get_category_by_slug($category_mapping[$cat->slug])->term_id;
            $categories[$i] = $new_cat_id;
        }
    }

    // Update the post categories
    wp_set_post_categories($post_id, $categories);
}
