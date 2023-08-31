<?php

require_once(BASE_PLUGIN_DIR . 'utils/bs_auth_required.php');

/**
 * Register the tags endpoint
 */
function bs_register_tags_route(): void
{
    register_rest_route('blogstorm/v1', 'tags', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('blogstorm_get_tags'),
    ));
}

add_action('rest_api_init', 'bs_register_tags_route');
