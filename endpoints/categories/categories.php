<?php
require_once(ABSPATH . '/wp-admin/includes/taxonomy.php');
require_once(plugin_dir_path(__FILE__) . 'bs_register_categories_route.php');


/**    Get all categories.
 * @return array
 **/
function blogstorm_get_categories($request): array
{
    $per_page = isset($request['per_page']) ? absint($request['per_page']) : 10;
    $page = isset($request['page']) ? absint($request['page']) : 1;

    $categories = get_categories(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
        'number' => $per_page,
        'offset' => ($page - 1) * $per_page,
    ));

    $total_categories = wp_count_terms('category', array('hide_empty' => false));

    $result = array(
        'total_categories' => $total_categories,
        'categories' => array(),
    );

    foreach ($categories as $category) {
        $result['categories'][] = array(
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    return $result;
}

/**    Check if a category exists with the given slug or name.
 * If it does, return the category, else create a new category and return it.
 * @param WP_REST_Request $request
 * @return array
 **/
function blogstorm_get_or_create_category(WP_REST_Request $request): array
{

    $slug = $request->get_param('slug');
    $name = $request->get_param('name');
    $category_description = $request->get_param('category_description');

    $category = get_category_by_slug($slug);
    if ($category) {
        return array(
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    $category = get_category_by_slug($name);
    if ($category) {
        return array(
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    $category_id = wp_insert_category(array(
        'cat_name' => $name,
        'category_nicename' => $slug,
        'category_description' => $category_description ? $category_description : '',
        'category_parent' => '',
        'taxonomy' => 'category',
    ));

    $category = get_category($category_id);
    return array(
        'id' => $category->term_id,
        'name' => $category->name,
        'slug' => $category->slug,
    );
}