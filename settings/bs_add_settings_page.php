<?php

function blogstorm_add_settings_page(): void
{
    add_options_page(
        'Blogstorm Settings',
        'Blogstorm',
        'manage_options',
        'blogstorm-settings',
        'blogstorm_render_settings_page'
    );
}

add_action('admin_menu', 'blogstorm_add_settings_page');

