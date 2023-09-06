<?php

require_once plugin_dir_path(__FILE__) . 'bs_add_settings_page.php';
require_once plugin_dir_path(__FILE__) . 'bs_register_settings.php';
require_once plugin_dir_path(__FILE__) . 'bs_link_settings_to_plugin.php';

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
            <button type="button" class="button" id="ping-verify-button">Ping Verify</button>
            <?php if ($bs_auth_token) { ?>
                <small>Press the submit button to save your authentication token.</small>
            <?php } ?>
        </form>
    </div>
    <script>
        const bsTokenInput = document.getElementById("x_<?php echo BS_TOKEN_NAME; ?>_input");
        const bsToggleButton = document.getElementById('toggleButton');
        bsToggleButton.addEventListener('click', () => {
            bsTokenInput.type = bsTokenInput.type === 'password' ? 'text' : 'password';
            bsToggleButton.innerText = bsTokenInput.type === 'password' ? 'Show' : 'Hide';
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

            bsTokenInput.style.backgroundColor = '#faecff';
            bsTokenInput.style.color = '#6c40db';
            bsTokenInput.style.border = "1px solid #C98AFFFF";
            bsTokenInput.value = isBsAuthToken;
        }

        const pingVerifyButton = document.getElementById('ping-verify-button');
        pingVerifyButton.addEventListener('click', async (event) => {
            await fetch("/wp-json/blogstorm/v1/ping-verify", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                }
            }).then(async (response) => {
                const data = await response.json();
                alert(data['message']);
            }).catch((error) => {
                alert("Error while sending Ping Verify request");
            });
        });
    </script>
    <?php
}
