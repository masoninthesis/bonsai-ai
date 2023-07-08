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

    // Get the category passed from the form submission
    $prompt_category_slug = rgar($entry, '5'); // Replace 'xx' with the Field ID of the hidden field in your form

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

    // Check if a mapping exists for the prompt category
    if (array_key_exists($prompt_category_slug, $category_mapping)) {
        // Get the "entry" category
        $new_cat_id = get_category_by_slug($category_mapping[$prompt_category_slug])->term_id;

        // Update the post categories
        wp_set_post_categories($post_id, array($new_cat_id));
    }
}
