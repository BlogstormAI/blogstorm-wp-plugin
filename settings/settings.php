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

function blogstorm_register_settings_init(): void
{
    register_setting('blogstorm-settings-group', 'blogstorm_auth_token', 'blogstorm_validate_auth_token');
    add_settings_section('blogstorm-auth-section', 'Authentication Settings', 'blogstorm_auth_section_callback', 'blogstorm-settings-group');
    add_settings_field('blogstorm_auth_token', 'Authentication Token', 'blogstorm_auth_token_callback', 'blogstorm-settings-group', 'blogstorm-auth-section');
}

add_action('admin_init', 'blogstorm_register_settings_init');

function blogstorm_validate_auth_token($input)
{
    $new_value = sanitize_text_field($input);
    if (strlen($new_value) !== 36) {
        add_settings_error('blogstorm_auth_token', 'auth_token_invalid', 'Authentication Token should have a length of 36 characters.', 'error');
        return get_option('blogstorm_auth_token');
    }
    return $new_value;
}

function blogstorm_auth_token_callback(): void
{
    $auth_token = esc_attr(get_option('blogstorm_auth_token'));
    ?>
    <div style="display: flex; flex-direction: row; max-width: 370px;">
        <input type="password" name="blogstorm_auth_token" id="blogstorm_auth_token" style="width: 100%;"
               placeholder="EXAMPLE-1234-5678-9012-12345-EXAMPLE" value="<?php echo $auth_token; ?>"/>
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

function blogstorm_auth_section_callback(): void
{
    echo '<p>Enter your authentication token below:</p>';
}

function blogstorm_render_settings_page(): void
{
    $bs_auth_token = sanitize_text_field($_GET['auth_token']);
    ?>
    <div class="wrap">
        <h2>Blogstorm Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('blogstorm-settings-group'); ?>
            <?php do_settings_sections('blogstorm-settings-group'); ?>
            <blockquote class="notice notice-warning">
                <p>
                    <strong>Important:</strong> Do not share your this token with anyone.
                    It is a crucial security feature that makes WordPress publishing possible.
                    Keep it safe.
                </p>
            </blockquote>
            <?php submit_button(
                'Save and Authenticate',
                'primary',
                'submit',
                false,
                ['style' => 'margin-top: 20px; display: block; margin-bottom: 7px;']
            ); ?>
            <?php if ($bs_auth_token) { ?>
                <small>Press the submit button to save your authentication token.</small>
            <?php } ?>
        </form>
    </div>
    <script>
        const input = document.getElementById('blogstorm_auth_token');
        const button = document.getElementById('toggleButton');
        button.addEventListener('click', () => {
            input.type = input.type === 'password' ? 'text' : 'password';
            button.innerText = input.type === 'password' ? 'Show' : 'Hide';
        });

        const isBsAuthToken = "<?php echo $bs_auth_token; ?>";
        if (isBsAuthToken) {
            const submitButton = document.querySelector('input[id="submit"]');
            submitButton.style.backgroundColor = '#6c40db';
            submitButton.style.color = '#faecff';
            submitButton.style.outlineColor = '#9b56f1';
            submitButton.style.outlineWidth = '2px';
            submitButton.style.outlineStyle = 'solid';
            submitButton.style.borderRadius = '3px';
            submitButton.style.boxShadow = '3px 3px 7px rgba(0, 0, 0, 0.25)';

            input.style.backgroundColor = '#faecff';
            input.style.color = '#6c40db';
            input.style.border = "1px solid #C98AFFFF";
            input.value = isBsAuthToken;
        }
    </script>
    <?php
}
