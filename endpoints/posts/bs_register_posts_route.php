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
        'callback' => bs_auth_required('blogstorm_create_post'), // Include bs_auth_required wrapper
        'permission_callback' => function () {
            return current_user_can('publish_posts');
        },
    ));


    register_rest_route('blogstorm/v1', 'post/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('blogstorm_get_post_by_id'),
    ));
}
add_action('rest_api_init', 'bs_register_post_routes');
