<?php
// Bootstrap Pagination
function bootstrap_pagination($custom_query = null) {
    // Use the global $wp_query by default, switch to custom query if available
    $query_to_use = ($custom_query) ? $custom_query : $GLOBALS['wp_query'];

    $pages = paginate_links(array(
            'base' => str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $query_to_use->max_num_pages,
            'type'  => 'array',
        )
    );

    if (is_array($pages)) {
        $paged = (get_query_var('paged') == 0) ? 1 : get_query_var('paged');
        echo '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        foreach ($pages as $page) {
            echo "<li class='page-item " . (strpos($page, 'current') !== false ? 'active' : '') . "'> " . str_replace('page-numbers', 'page-link', $page) . "</li>";
        }
        echo '</ul></nav>';
    }
}
