<?php
/**
 * Add the settings link to the plugin page
 */

function bs_get_page_url($page): string
{
    $args = ['page' => $page];
    return add_query_arg($args, admin_url('options-general.php'));
}


function blogstorm_add_settings_link($links): array
{
    $settings_links = array(
        '<a href="' . bs_get_page_url('blogstorm-settings') . '">Settings</a>',
    );
    return array_merge($links, $settings_links);
}

add_filter('plugin_action_links_' . BASE_PLUGIN_PATH, 'blogstorm_add_settings_link');