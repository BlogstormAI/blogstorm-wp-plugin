<?php

// Register the Ping Verify endpoint
function bs_register_ping_verfiy() {
    register_rest_route('blogstorm/v1', '/ping-verify', array(
        'methods' => 'POST',
        'callback' => 'blogstorm_ping_verify',
    ));
}

add_action('rest_api_init', 'bs_register_ping_verfiy');