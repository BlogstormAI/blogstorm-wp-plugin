<?php
require_once( ABSPATH . '/wp-admin/includes/taxonomy.php');


// Register the categories endpoint
function blogstorm_register_categories_route(): void
{
    register_rest_route('blogstorm/v1', 'categories', array(
        'methods' => 'GET',
        'callback' => 'blogstorm_get_categories',
    ));

    register_rest_route('blogstorm/v1', 'categories/get-or-create', array(
        'methods' => 'POST',
        'callback' => 'blogstorm_get_or_create_category',
    ));
}

// Callback function for the categories endpoint
function blogstorm_get_categories(): array
{
    $categories = get_categories(array(
        'orderby' => 'name',
        'order' => 'ASC',
        'hide_empty' => false,
    ));

    $result = array();
    foreach ($categories as $category) {
        $result[] = array(
            'id' => $category->term_id,
            'name' => $category->name,
            'slug' => $category->slug,
        );
    }

    return $result;
}

// Callback function for the get-or-create endpoint
function blogstorm_get_or_create_category($request): array
{
//    Check if the a category exists with the given slug or name
//    If it does, return the category
//    If it doesn't, create a new category and return it

    $blogstorm_auth_string = $request->get_header('blogstorm-auth');
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