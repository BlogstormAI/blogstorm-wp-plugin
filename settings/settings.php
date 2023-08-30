<?php

function blogstorm_add_settings_page(): void {
    add_options_page(
        'Blogstorm Settings',
        'Blogstorm',
        'manage_options',
        'blogstorm-settings',
        'blogstorm_render_settings_page'
    );
}
add_action('admin_menu', 'blogstorm_add_settings_page');

function blogstorm_register_settings_init(): void {
    register_setting('blogstorm-settings-group', 'blogstorm_auth_token', 'blogstorm_validate_auth_token');
    add_settings_section('blogstorm-auth-section', 'Authentication Settings', 'blogstorm_auth_section_callback', 'blogstorm-settings-group');
    add_settings_field('blogstorm_auth_token', 'Authentication Token', 'blogstorm_auth_token_callback', 'blogstorm-settings-group', 'blogstorm-auth-section');
}
add_action('admin_init', 'blogstorm_register_settings_init');

function blogstorm_validate_auth_token($input) {
    $new_value = sanitize_text_field($input);
    if (strlen($new_value) !== 36) {
        add_settings_error('blogstorm_auth_token', 'auth_token_invalid', 'Authentication Token should have a length of 36 characters.', 'error');
        return get_option('blogstorm_auth_token');
    }
    return $new_value;
}

function blogstorm_auth_token_callback() {
    $auth_token = esc_attr(get_option('blogstorm_auth_token'));
    ?>
    <div style="display: flex; flex-direction: row; max-width: 370px;">
        <input type="password" name="blogstorm_auth_token" id="blogstorm_auth_token" style="width: 100%;" placeholder="EXAMPLE-1234-5678-9012-12345-EXAMPLE" value="<?php echo $auth_token; ?>" />
        <button type="button" id="toggleButton">Show</button>
    </div>
    <?php if (empty($auth_token)) { ?>
        <a href="https://demo.blogstorm.ai/" target="_blank">
            <small>Get your authentication token</small>
        </a>
    <?php } ?>
    <p class="description">Please enter a token with a minimum length of 36 characters.</p>
    <?php
}

function blogstorm_auth_section_callback() {
    echo '<p>Enter your authentication settings below:</p>';
}

function blogstorm_render_settings_page() {
    if (isset($_GET['auth_token'])) {
        $new_auth_token = sanitize_text_field($_GET['auth_token']);
        if (strlen($new_auth_token) === 36) {
            update_option('blogstorm_auth_token', $new_auth_token);
            echo '<div class="notice notice-success"><p>Authentication token updated successfully.</p></div>';
        } else {
            $main_page_url = strtok($_SERVER["REQUEST_URI"], '&');
            echo '<div class="notice notice-error">
                <p>
                    Invalid authentication token. Make sure it has a length of 36 characters. 
                </p>
            </div>';
        }
    }
    ?>
    <div class="wrap">
        <h2>Blogstorm Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('blogstorm-settings-group'); ?>
            <?php do_settings_sections('blogstorm-settings-group'); ?>
            <blockquote class="notice notice-warning">
                <p><strong>Important:</strong> Do not share your Authentication Token with anyone. This token provides make WordPress publishing possible. Keep it safe.</p>
            </blockquote>
            <?php submit_button(); ?>
        </form>
    </div>
    <script>
        const input = document.getElementById('blogstorm_auth_token');
        const button = document.getElementById('toggleButton');
        button.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            button.innerText = input.type === 'password' ? 'Show' : 'Hide';
        });
    </script>
    <?php
}
