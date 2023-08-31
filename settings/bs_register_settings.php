<?php

function blogstorm_register_settings(): void
{
    register_setting('blogstorm-settings-group', BS_TOKEN_NAME, 'blogstorm_validate_auth_token');
    add_settings_section('blogstorm-auth-section', 'Authentication Settings', 'blogstorm_auth_section_callback', 'blogstorm-settings-group');
    add_settings_field(
        BS_TOKEN_NAME,
        'Authentication Token',
        'blogstorm_auth_token_callback',
        'blogstorm-settings-group',
        'blogstorm-auth-section',
        array('label_for' => 'x_' . BS_TOKEN_NAME . '_input')
    );
}

add_action('admin_init', 'blogstorm_register_settings');


function blogstorm_validate_auth_token($input)
{
    $new_value = sanitize_text_field($input);
    if (strlen($new_value) !== 36) {
        add_settings_error(BS_TOKEN_NAME, 'auth_token_invalid', 'Authentication Token should have a length of 36 characters.', 'error');
        return get_option(BS_TOKEN_NAME);
    }
    return $new_value;
}


function blogstorm_auth_token_callback(): void
{
    $auth_token = esc_attr(get_option(BS_TOKEN_NAME));
    ?>
    <div style="display: flex; flex-direction: row; max-width: 370px;">
        <input type="password" name="<?php echo BS_TOKEN_NAME ?>" id="x_<?php echo BS_TOKEN_NAME ?>_input" style="width: 100%;" placeholder="EXAMPLE-1234-5678-9012-12345-EXAMPLE" value="<?php echo $auth_token; ?>"/>
        <button type="button" id="toggleButton">Show</button>
    </div>
    <?php if (empty($auth_token)) { ?>
    <a href="https://demo.blogstorm.ai/" target="_blank">
        <small>Get your authentication token</small>
    </a>
<?php } ?>
    <small class="description">Please enter a token with a minimum length of 36 characters.</small>
    <?php
}

function blogstorm_auth_section_callback(): void
{
    echo '<p>Enter your authentication token below:</p>';
}

