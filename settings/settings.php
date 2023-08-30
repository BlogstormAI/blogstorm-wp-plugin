<?php
// Add the settings page under the "Settings" menu
function blogstorm_add_settings_page()
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

// Register settings and fields
function blogstorm_register_settings()
{
    register_setting('blogstorm-settings-group', 'blogstorm_auth_token'); // Match the settings group slug here
}

add_action('admin_init', 'blogstorm_register_settings');


// Render the settings page
function blogstorm_render_settings_page()
{
    $token = get_option('blogstorm_auth_token');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        echo "Hello";
        $newToken = sanitize_text_field($_POST['blogstorm_auth_token']);
        if (strlen($newToken) < 36) {
            add_settings_error('blogstorm_auth_token', 'invalid_token', 'Authentication Token must be at least 36 characters long.', 'error');
        } else {
            update_option('blogstorm_auth_token', $newToken);
            add_settings_error('blogstorm_auth_token', 'token_updated', 'Authentication Token updated successfully.', 'updated');
        }
    }

    settings_errors('blogstorm_auth_token');
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
            <table class="form-table">
                <tr class="form-field form-required">
                    <th scope="row"><label for="blogstorm_auth_token">Authentication Token <span class="description">(required)</span></label>
                    </th>
                    <td>
                        <input type="text" name="blogstorm_auth_token"
                               id="blogstorm_auth_token"
                               placeholder="EXAMPLE-1234-5678-9012-EXAMPLE"
                               value="<?php echo esc_attr(get_option('blogstorm_auth_token')); ?>"
                        />
<!--                               required minlength="36"-->
                        <p class="description">Please enter a token with a minimum length of 36 characters.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
