<?php
/*
Plugin Name: Blogstorm Content
Plugin URI: https://www.blogstorm.ai
Description: A plugin to manage content for Blogstorm, the AI-powered content publishing platform.
Version: 1.0
Author: Bishwas Bhandari
Author URI: https://bishwas.net
License: A "Slug" license name e.g. GPL2
*/

const BS_TOKEN_NAME = 'blogstorm_auth_token';
const BS_TOKEN_LENGTH = 36;

// Include the category and tag endpoint files
require_once plugin_dir_path(__FILE__) . 'endpoints/categories.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/tags.php';

// Include settings code
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'settings/settings.php';
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'blogstorm_add_settings_link');
}
