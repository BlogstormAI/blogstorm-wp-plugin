<?php
/*
Plugin Name: Blogstorm AI Content
Plugin URI: https://www.blogstorm.ai
Description: A plugin to manage content for Blogstorm, the AI-powered content publishing platform.
Version: 1.0.3
Author: Bishwas Bhandari
Author URI: https://bishwas.net
License: A "Slug" license name e.g. GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Check if we're in a WordPress environment
if (!function_exists('add_action')) {
    return;
}

define("BASE_PLUGIN_PATH", plugin_basename(__FILE__));
define("BASE_PLUGIN_DIR", plugin_dir_path(__FILE__));
const BS_TOKEN_NAME = 'blogstorm_auth_token';
const BS_PROD_PING_VERIFY_URL = 'https://app.blogstorm.ai/public-api/ping-wordpress-site';
const BS_DEV_PING_VERIFY_URL = 'http://localhost:8000/public-api/ping-wordpress-site';

const BS_TOKEN_LENGTH = 32;

// Function to get plugin directory path safely
function blogstorm_get_plugin_path($path) {
    return function_exists('plugin_dir_path') ? plugin_dir_path(__FILE__) . $path : dirname(__FILE__) . '/' . $path;
}

// Include the category and tag endpoint files
$required_files = array(
    'endpoints/links/internal_links.php',
    'endpoints/categories/categories.php',
    'endpoints/tags/tags.php',
    'endpoints/posts/posts.php',
    'endpoints/site-info/site-info.php',
    'endpoints/ping/verify.php',
    'endpoints/pages/page.php',
    'endpoints/iframe/iframe.php'
);

foreach ($required_files as $file) {
    $file_path = blogstorm_get_plugin_path($file);
    if (file_exists($file_path)) {
        require_once $file_path;
    }
}

// Include settings code
if (function_exists('is_admin') && is_admin()) {
    require_once blogstorm_get_plugin_path('settings/settings.php');
}
