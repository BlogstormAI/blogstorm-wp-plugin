<?php
require_once(BASE_PLUGIN_DIR . 'utils/bs_auth_required.php');

/**
 * Register the categories endpoint
 */
function bs_register_links_route(): void
{
    register_rest_route('blogstorm/v1', 'internal-links', array(
        'methods' => 'GET',
        'callback' => bs_auth_required('blogstorm_get_internal_links'),
    ));
}

add_action('rest_api_init', 'bs_register_links_route');
