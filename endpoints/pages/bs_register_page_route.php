<?php
require_once(BASE_PLUGIN_DIR . 'utils/bs_auth_required.php');
// Register the pages endpoint

function bs_register_page_routes(): void
{
	register_rest_route('blogstorm/v1', 'pages/create', array(
		'methods' => 'POST',
		'callback' => bs_auth_required('blogstorm_get_or_create_page') // Include bs_auth_required wrapper
	));
}
add_action('rest_api_init', 'bs_register_page_routes');