<?php

// Add the settings page under the "Settings" menu
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
function blogstorm_register_settings_init(): void
{
    // Register the setting with validation callback
    register_setting('blogstorm-settings-group', 'blogstorm_auth_token', 'blogstorm_validate_auth_token');

    // Add a new section if needed
    add_settings_section(
        'blogstorm-auth-section',
        'Authentication Settings',
        'blogstorm_auth_section_callback',
        'blogstorm-settings-group'
    );

    // Add a new field to the section
    add_settings_field(
        'blogstorm_auth_token',
        'Authentication Token',
        'blogstorm_auth_token_callback',
        'blogstorm-settings-group',
        'blogstorm-auth-section'
    );
}

add_action('admin_init', 'blogstorm_register_settings_init');

// Validate the authentication token
function blogstorm_validate_auth_token($input)
{
    $new_value = sanitize_text_field($input);

    if (strlen($new_value) < 36) {
        add_settings_error(
            'blogstorm_auth_token',
            'auth_token_invalid',
            'Authentication Token should have a minimum length of 36 characters.',
            'error'
        );
        return get_option('blogstorm_auth_token'); // Revert to the previous value
    }

    return $new_value;
}

// Callback for the authentication token field
function blogstorm_auth_token_callback(): void
{
    $auth_token = esc_attr(get_option('blogstorm_auth_token'));
    ?>
    <input type="text" name="blogstorm_auth_token"
           id="blogstorm_auth_token"
           placeholder="EXAMPLE-1234-5678-9012-EXAMPLE"
           value="<?php echo $auth_token; ?>"
    />
    <p class="description">Please enter a token with a minimum length of 36 characters.</p>
    <?php
}

// Callback for the authentication section
function blogstorm_auth_section_callback(): void
{
    echo '<p>Enter your authentication settings below:</p>';
}

// Render the settings page
function blogstorm_render_settings_page(): void
{
    ?>
    <div class="wrap">
        <h2>Blogstorm Settings</h2>
        <blockquote class="notice notice-warning">
            <p>
                <strong>Important:</strong> Do not share your Authentication Token with anyone. This token provides make
                WordPress publishing possible. Keep it safe.
            </p>
        </blockquote>

        <form method="post" action="options.php">
            <?php settings_fields('blogstorm-settings-group'); ?>
            <?php do_settings_sections('blogstorm-settings-group'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
