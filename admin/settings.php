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
        <div class="min-h-screen p-8 bg-background text-foreground">
            <div class="max-w-2xl mx-auto">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold tracking-tight">Database Configuration</h1>
                    <p class="text-muted-foreground mt-2">
                        Connect to your Nuke CMS database to begin the migration process.
                    </p>
                </div>

                <!-- Form -->
                <form id="nuke-to-wordpress-settings-form" class="space-y-6">
                    <?php wp_nonce_field('nuke_to_wordpress_settings_nonce', 'nuke_to_wordpress_settings_nonce'); ?>

                    <div class="bg-card rounded-lg border shadow-sm">
                        <!-- Connection Status Indicator -->
                        <div id="connection-status" class="hidden p-4 border-b">
                            <div class="flex items-center gap-2">
                                <div class="connection-spinner hidden">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                            stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                </div>
                                <span class="connection-text"></span>
                            </div>
                        </div>

                        <!-- Form Fields -->
                        <div class="p-6 space-y-4">
                            <!-- Database Host -->
                            <div class="space-y-2">
                                <label for="nuke_db_host" class="text-sm font-medium leading-none">
                                    Database Host <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="nuke_db_host" name="nuke_to_wordpress_settings[nuke_db_host]"
                                        value="<?php echo esc_attr($options['nuke_db_host']); ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        placeholder="e.g., localhost or 127.0.0.1" required />
                                    <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </div>
                                <p class="text-xs text-muted-foreground">The hostname where your Nuke database is located
                                </p>
                            </div>

                            <!-- Database Name -->
                            <div class="space-y-2">
                                <label for="nuke_db_name" class="text-sm font-medium leading-none">
                                    Database Name <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="nuke_db_name" name="nuke_to_wordpress_settings[nuke_db_name]"
                                        value="<?php echo esc_attr($options['nuke_db_name']); ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        placeholder="Enter your Nuke database name" required />
                                    <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Database User -->
                            <div class="space-y-2">
                                <label for="nuke_db_user" class="text-sm font-medium leading-none">
                                    Database User <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="nuke_db_user" name="nuke_to_wordpress_settings[nuke_db_user]"
                                        value="<?php echo esc_attr($options['nuke_db_user']); ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        placeholder="Enter database username" required />
                                    <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                            </div>

                            <!-- Database Password -->
                            <div class="space-y-2">
                                <label for="nuke_db_password" class="text-sm font-medium leading-none">
                                    Database Password <span class="text-destructive">*</span>
                                </label>
                                <div class="relative">
                                    <input type="password" id="nuke_db_password"
                                        name="nuke_to_wordpress_settings[nuke_db_password]"
                                        value="<?php echo esc_attr($options['nuke_db_password']); ?>"
                                        class="flex h-10 w-full rounded-md border border-input bg-background pl-10 pr-9 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                        placeholder="Enter database password" required />
                                    <svg class="absolute left-3 top-3 h-4 w-4 text-muted-foreground"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <button type="button" id="toggle-password"
                                        class="absolute right-3 top-3 text-muted-foreground hover:text-foreground">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cron Job Settings -->
                    <div class="bg-card rounded-xl p-6 border mb-6">
                        <h3 class="font-semibold text-xl mb-4">Cron Job Settings</h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" 
                                    id="disable_wp_cron" 
                                    name="nuke_to_wordpress_settings[disable_wp_cron]" 
                                    class="h-4 w-4 rounded border-gray-300"
                                    <?php checked(get_option('nuke_to_wordpress_cron_disabled'), true); ?>>
                                <label for="disable_wp_cron" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                                    Disable WordPress Default Cron
                                </label>
                            </div>
                            <p class="text-sm text-muted-foreground">
                                When enabled, this will add DISABLE_WP_CRON constant to wp-config.php and attempt to set up a system cron job.
                            </p>
                            <div id="cron-status" class="text-sm hidden"></div>
                        </div>
                    </div>

                    <!-- Debug Settings -->
                    <div class="bg-card rounded-xl p-6 border mb-6">
                        <h2 class="text-2xl font-semibold mb-4">Debug Settings</h2>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="enable_debug" name="nuke_to_wordpress_settings[enable_debug]" 
                                    class="h-4 w-4" <?php checked(get_option('nuke_to_wordpress_debug_enabled')); ?>>
                                <label for="enable_debug" class="text-sm font-medium">
                                    Enable Debug Logging
                                </label>
                            </div>
                            <?php if (get_option('nuke_to_wordpress_debug_enabled')): ?>
                                <div class="mt-4">
                                    <button type="button" id="view-logs" class="inline-flex items-center justify-center rounded-md 
                                        text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none 
                                        focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 
                                        disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground 
                                        hover:bg-secondary/80 h-9 px-4 py-2">
                                        View Logs
                                    </button>
                                    <button type="button" id="clear-logs" class="inline-flex items-center justify-center rounded-md 
                                        text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none 
                                        focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 
                                        disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground 
                                        hover:bg-destructive/90 h-9 px-4 py-2 ml-2">
                                        Clear Logs
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Message Display -->
                    <div id="settings-message" class="hidden rounded-lg border px-4 py-3 text-sm"></div>

                    <!-- Form Actions -->
                    <div class="flex items-center gap-4">
                        <button type="submit" id="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                            </svg>
                            Save Settings
                        </button>
                        <button type="button" id="test-connection"
                            class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 12h14M12 5l7 7-7 7" />
                            </svg>
                            Test Connection
                        </button>
                        <button type="button" id="cancel-settings"
                            class="inline-flex items-center justify-center gap-2 rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-10 px-4 py-2">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

function nuke_to_wordpress_save_settings() {
    try {
        // Debug logging
        $debug = Nuke_To_WordPress_Debug_Handler::get_instance();
        $debug->log('Attempting to save settings');

        // Verify nonce
        if (!check_ajax_referer('nuke_to_wordpress_settings_nonce', 'nonce', false)) {
            $debug->log('Nonce verification failed', [], 'error');
            wp_send_json_error(['message' => 'Invalid security token']);
            return;
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            $debug->log('Permission check failed', [], 'error');
            wp_send_json_error(['message' => 'Insufficient permissions']);
            return;
        }

        // Get settings from POST data
        $settings = isset($_POST['settings']) ? $_POST['settings'] : null;
        
        if (!is_array($settings)) {
            $debug->log('Invalid settings data received', [], 'error');
            wp_send_json_error(['message' => 'Invalid settings data']);
            return;
        }

        // Sanitize settings
        $sanitized_settings = [
            'nuke_db_host' => sanitize_text_field($settings['nuke_db_host'] ?? ''),
            'nuke_db_name' => sanitize_text_field($settings['nuke_db_name'] ?? ''),
            'nuke_db_user' => sanitize_text_field($settings['nuke_db_user'] ?? ''),
            'nuke_db_password' => $settings['nuke_db_password'] ?? '',
        ];

        // Validate required fields
        $required_fields = ['nuke_db_host', 'nuke_db_name', 'nuke_db_user', 'nuke_db_password'];
        foreach ($required_fields as $field) {
            if (empty($sanitized_settings[$field])) {
                wp_send_json_error(['message' => 'All database fields are required']);
                return;
            }
        }

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
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );

            // Verify connection
            $pdo->query('SELECT 1');

            // Save settings
            $updated = update_option('nuke_to_wordpress_settings', $sanitized_settings);

            // Save additional settings
            update_option('nuke_to_wordpress_cron_disabled', 
                isset($settings['disable_wp_cron']) && $settings['disable_wp_cron'] === 'on'
            );
            
            update_option('nuke_to_wordpress_debug_enabled', 
                isset($settings['enable_debug']) && $settings['enable_debug'] === 'on'
            );

            if ($updated) {
                $debug->log('Settings saved successfully');
                wp_send_json_success(['message' => 'Settings saved successfully']);
            } else {
                $debug->log('Failed to update settings in database', [], 'error');
                wp_send_json_error(['message' => 'Failed to update settings']);
            }

        } catch (PDOException $e) {
            $debug->log('Database connection failed', ['error' => $e->getMessage()], 'error');
            wp_send_json_error(['message' => 'Database connection failed: ' . $e->getMessage()]);
        }

    } catch (Exception $e) {
        $debug->log('Unexpected error', ['error' => $e->getMessage()], 'error');
        wp_send_json_error(['message' => 'An unexpected error occurred']);
    }
}
add_action('wp_ajax_save_nuke_settings', 'nuke_to_wordpress_save_settings');
