<?php
// Activation hook to create custom database table
function blogstorm_content_activate(): void {
    global $wpdb;
    $table_name = $wpdb->prefix . 'blogstorm_auth_tokens';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        token varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'blogstorm_content_activate');
