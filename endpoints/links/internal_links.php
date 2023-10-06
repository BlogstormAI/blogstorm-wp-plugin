<?php
require_once(ABSPATH . '/wp-includes/post.php');
require_once(ABSPATH . '/wp-admin/includes/taxonomy.php');
require_once(plugin_dir_path(__FILE__) . 'bs_register_links_route.php');

/**    Get top 10 relatable list of links.
 * @return array
 **/
function blogstorm_get_internal_links($request): array
{
    $search = $request['search'] ?: '';
    $result = array();

    // Search in Pages
    $pages_args = array(
        's' => $search,
        'post_type' => 'page',
        'posts_per_page' => 10
    );

    $pages_query = new WP_Query($pages_args);
    while ($pages_query->have_posts()) {
        $pages_query->the_post();
        $result[] = array(
            'link' => get_the_permalink(),
            'title' => get_the_title(),
            'contentType' => 'Page'
        );
    }

    // Search in Posts
    $posts_args = array(
        's' => $search,
        'post_type' => 'post',
        'posts_per_page' => 10,
    );

    $posts_query = new WP_Query($posts_args);
    while ($posts_query->have_posts()) {
        $posts_query->the_post();
        $result[] = array(
            'link' => get_the_permalink(),
            'title' => get_the_title(),
            'contentType' => 'Post'
        );
    }

    // Search in Categories
    $cat_query = array(
        'name__like' => $search,
        'hide_empty' => false
    );
    $categories = get_categories($cat_query);
    foreach ($categories as $category) {
        $result[] = array(
            'link' => get_category_link($category->term_id),
            'title' => $category->name,
            'contentType' => 'Category',
        );
    }

    return $result;
}