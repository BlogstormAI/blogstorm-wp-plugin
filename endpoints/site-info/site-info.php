<?php
require_once(plugin_dir_path(__FILE__) . 'bs_register_site_info_route.php');

function blogstorm_get_site_info(): array
{
    return array(
        'name' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'url' => site_url()
    );
}
