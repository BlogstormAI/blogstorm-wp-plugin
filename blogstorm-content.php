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

// Include the category and tag endpoint files
require_once plugin_dir_path(__FILE__) . 'endpoints/categories.php';
require_once plugin_dir_path(__FILE__) . 'endpoints/tags.php';

// Include activation code
require_once plugin_dir_path(__FILE__) . 'activation.php';

// Include settings code
require_once plugin_dir_path(__FILE__) . 'settings/settings.php';
