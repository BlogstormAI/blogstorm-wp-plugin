<?php
require_once(BASE_PLUGIN_DIR . 'utils/bs_header_required.php');

function bs_register_site_info_route(): void
{
    register_rest_route('blogstorm/v1', 'site-info', array(
        'methods' => 'GET',
        'callback' => bs_header_required('blogstorm_get_site_info'),
    ));
}

add_action('rest_api_init', 'bs_register_site_info_route');