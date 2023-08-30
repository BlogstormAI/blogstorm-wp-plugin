<?php
// Store the token in the database
function blogstorm_store_auth_token($token): void
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'blogstorm_auth_tokens';
    $wpdb->insert($table_name, array('token' => $token));
}

// Retrieve the token from the database
function blogstorm_get_auth_token(): ?string
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'blogstorm_auth_tokens';
    $token = $wpdb->get_var("SELECT token FROM $table_name ORDER BY id DESC LIMIT 1");
    return $token;
}