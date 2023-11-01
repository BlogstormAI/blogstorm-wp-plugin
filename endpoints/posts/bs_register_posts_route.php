<?php

require_once(BASE_PLUGIN_DIR . 'utils/bs_auth_required.php');
// Register the posts endpoint

function bs_register_post_routes(): void
{
    register_rest_route('blogstorm/v1', 'posts', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('blogstorm_get_posts'),
    ));

    register_rest_route('blogstorm/v1', 'posts/create', array(
        'methods' => 'POST',
        'callback' => bs_auth_required('blogstorm_get_or_create_post') // Include bs_auth_required wrapper
    ));


    register_rest_route('blogstorm/v1', 'post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('blogstorm_get_post_by_id'),
    ));
    
    // Register the new endpoint for removing h1 tags
    register_rest_route('blogstorm/v1', 'posts/remove-h1', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('remove_h1_tags_from_single_post'),
    ));
}
add_action('rest_api_init', 'bs_register_post_routes');
