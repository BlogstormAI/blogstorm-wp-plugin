<?php
/*
Plugin Name: Blogstorm Content
Plugin URI: https://www.blogstorm.ai
Description: A plugin to manage content for Blogstorm, the AI-powered content publishing platform.
Version: 1.0.1
Author: Bishwas Bhandari
Author URI: https://bishwas.net
License: A "Slug" license name e.g. GPL2
*/

define("BASE_PLUGIN_PATH", plugin_basename(__FILE__));
define("BASE_PLUGIN_DIR", plugin_dir_path(__FILE__));
const BS_TOKEN_NAME = 'blogstorm_auth_token';
const BS_PROD_PING_VERIFY_URL = 'https://app.blogstorm.ai/public-api/ping-wordpress-site';
const BS_DEV_PING_VERIFY_URL = 'http://localhost:8000/public-api/ping-wordpress-site';

const BS_TOKEN_LENGTH = 32;

// Include the category and tag endpoint files
require_once plugin_dir_path(__FILE__) . 'endpoints/links/internal_links.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/categories/categories.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/tags/tags.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/posts/posts.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/site-info/site-info.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/ping/verify.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/pages/page.php';

// Include settings code
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'settings/settings.php';
}
