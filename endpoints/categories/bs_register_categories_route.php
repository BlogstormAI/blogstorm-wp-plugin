<?php
require_once(BASE_PLUGIN_DIR . 'utils/bs_auth_required.php');

/**
 * Register the categories endpoint
 */
function bs_register_categories_route(): void
{
    register_rest_route('blogstorm/v1', 'categories', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('blogstorm_get_categories'),
    ));

    register_rest_route('blogstorm/v1', 'categories/get-or-create', array(
        'methods' => 'POST',
        'callback' => bs_auth_required('blogstorm_get_or_create_category'),
    ));
}

add_action('rest_api_init', 'bs_register_categories_route');
