<?php
if (!defined('ABSPATH')) {
    exit;
}

function nuke_to_wordpress_settings_page_content()
{
    $options = get_option('nuke_to_wordpress_settings', [
        'nuke_db_host' => 'localhost',
        'nuke_db_name' => '',
        'nuke_db_user' => '',
        'nuke_db_password' => ''
    ]);
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form id="nuke-to-wordpress-settings-form">
            <?php wp_nonce_field('nuke_to_wordpress_settings_nonce', 'nuke_to_wordpress_settings_nonce'); ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="nuke_db_host">Database Host</label>
                        </th>
                        <td>
                            <input type="text" id="nuke_db_host" name="nuke_to_wordpress_settings[nuke_db_host]"
                                value="<?php echo esc_attr($options['nuke_db_host']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nuke_db_name">Database Name</label>
                        </th>
                        <td>
                            <input type="text" id="nuke_db_name" name="nuke_to_wordpress_settings[nuke_db_name]"
                                value="<?php echo esc_attr($options['nuke_db_name']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nuke_db_user">Database User</label>
                        </th>
                        <td>
                            <input type="text" id="nuke_db_user" name="nuke_to_wordpress_settings[nuke_db_user]"
                                value="<?php echo esc_attr($options['nuke_db_user']); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nuke_db_password">Database Password</label>
                        </th>
                        <td>
                            <input type="password" id="nuke_db_password" name="nuke_to_wordpress_settings[nuke_db_password]"
                                value="<?php echo esc_attr($options['nuke_db_password']); ?>" class="regular-text">
                        </td>
                    </tr>
                </tbody>
            </table>

            <div id="settings-message" class="notice" style="display: none;"></div>

            <div class="submit-button-group">
                <?php submit_button('Save Settings', 'primary', 'submit', false); ?>
                <button type="button" id="cancel-settings" class="button button-secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <style>
        .submit-button-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }
    </style>
    <?php
}

function nuke_to_wordpress_sanitize_settings($input)
{
    $sanitized = [];

    if (isset($input['nuke_db_host'])) {
        $sanitized['nuke_db_host'] = sanitize_text_field($input['nuke_db_host']);
    }

    if (isset($input['nuke_db_name'])) {
        $sanitized['nuke_db_name'] = sanitize_text_field($input['nuke_db_name']);
    }

    if (isset($input['nuke_db_user'])) {
        $sanitized['nuke_db_user'] = sanitize_text_field($input['nuke_db_user']);
    }

    if (isset($input['nuke_db_password'])) {
        $sanitized['nuke_db_password'] = $input['nuke_db_password'];
    }

    return $sanitized;
}

function nuke_to_wordpress_save_settings()
{
    try {
        // Verify nonce
        if (!check_ajax_referer('nuke_to_wordpress_settings_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        // Verify form data exists
        if (empty($_POST['formData'])) {
            wp_send_json_error(['message' => 'No form data received']);
            return;
        }

        // Parse form data
        parse_str($_POST['formData'], $settings);

        if (empty($settings['nuke_to_wordpress_settings'])) {
            wp_send_json_error(['message' => 'Invalid settings format']);
            return;
        }

        $sanitized_settings = nuke_to_wordpress_sanitize_settings($settings['nuke_to_wordpress_settings']);

        // Test database connection
        try {
            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=utf8mb4",
                $sanitized_settings['nuke_db_host'],
                $sanitized_settings['nuke_db_name']
            );

            $pdo = new PDO(
                $dsn,
                $sanitized_settings['nuke_db_user'],
                $sanitized_settings['nuke_db_password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Save settings if connection successful
            $updated = update_option('nuke_to_wordpress_settings', $sanitized_settings);

            if ($updated) {
                wp_send_json_success(['message' => 'Settings saved successfully']);
            } else {
                wp_send_json_error(['message' => 'Failed to update settings in database']);
            }

        } catch (PDOException $e) {
            wp_send_json_error(['message' => 'Database connection failed: ' . $e->getMessage()]);
        }

    } catch (Exception $e) {
        error_log('Nuke to WordPress settings error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'An unexpected error occurred']);
    }
}
add_action('wp_ajax_save_nuke_settings', 'nuke_to_wordpress_save_settings');
