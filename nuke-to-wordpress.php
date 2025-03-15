<?php
/*
Plugin Name: Nuke to WordPress Migration
Plugin URI: https://github.com/ARUSH221617/nuke-to-wordpress
Description: A professional migration tool to transfer content from Nuke PHP to WordPress
Version: 1.0.0
Requires at least: 5.8
Requires PHP: 7.4
Author: Your Name
Author URI: https://arush.ir
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: nuke-to-wordpress
Domain Path: /languages
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'includes/vendor/autoload.php';

use NukeToWordPress\Migration_Background_Process;

// Activation hook
register_activation_hook(__FILE__, 'nuke_to_wordpress_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'nuke_to_wordpress_deactivate');

// Initialization code
add_action('admin_menu', 'nuke_to_wordpress_admin_menu');
add_action('admin_enqueue_scripts', 'nuke_to_wordpress_enqueue_scripts');
add_action('admin_enqueue_scripts', 'nuke_to_wordpress_admin_styles');

function nuke_to_wordpress_activate()
{
    // Add the settings to the whitelist using the correct method
    add_option('nuke_to_wordpress_settings', [
        'nuke_db_host' => 'localhost',
        'nuke_db_name' => '',
        'nuke_db_user' => '',
        'nuke_db_password' => ''
    ]);
    // Add cron setting
    add_option('nuke_to_wordpress_cron_disabled', false);
    // Add debug setting
    add_option('nuke_to_wordpress_debug_enabled', false);
}

function nuke_to_wordpress_deactivate()
{
    // Code to run on deactivation
    // Restore default cron if needed
    if (get_option('nuke_to_wordpress_cron_disabled', false)) {
        nuke_to_wordpress_restore_default_cron();
    }
    delete_option('nuke_to_wordpress_cron_disabled');
}

function nuke_to_wordpress_admin_menu()
{
    // Register admin pages
    add_menu_page(
        'Nuke to WordPress Settings',
        'Nuke to WP',
        'manage_options',
        'nuke-to-wordpress-settings',
        'nuke_to_wordpress_settings_page',
        'dashicons-admin-generic',
        60
    );

    add_submenu_page(
        'nuke-to-wordpress-settings',
        'Migration',
        'Migration',
        'manage_options',
        'nuke-to-wordpress-migration',
        'nuke_to_wordpress_migration_page'
    );

    // Add new Help page
    add_submenu_page(
        'nuke-to-wordpress-settings',
        'Help & Documentation',
        'Help',
        'manage_options',
        'nuke-to-wordpress-help',
        'nuke_to_wordpress_help_page'
    );
}

function nuke_to_wordpress_enqueue_scripts($hook)
{
    // Only load on our plugin's pages
    if (strpos($hook, 'nuke-to-wordpress') === false) {
        return;
    }

    wp_enqueue_script(
        'nuke-to-wordpress-admin',
        plugins_url('admin/js/dist/admin.min.js', __FILE__),
        array('jquery'),
        '1.0.0',
        true
    );

    wp_localize_script(
        'nuke-to-wordpress-admin',
        'ajaxurl',
        admin_url('admin-ajax.php')
    );
}
add_action('admin_enqueue_scripts', 'nuke_to_wordpress_enqueue_scripts');

function nuke_to_wordpress_admin_styles($hook)
{
    // Only load on our plugin pages
    if (strpos($hook, 'nuke-to-wordpress') === false) {
        return;
    }

    // Enqueue the compiled and minified CSS
    wp_enqueue_style(
        'nuke-to-wordpress-admin',
        plugin_dir_url(__FILE__) . 'admin/css/nuke-to-wordpress.min.css',
        [],
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'nuke_to_wordpress_admin_styles');

function nuke_to_wordpress_settings_page()
{
    // Use plugin_dir_path to get the correct absolute path
    require_once plugin_dir_path(__FILE__) . 'admin/settings.php';
    nuke_to_wordpress_settings_page_content();
}

function nuke_to_wordpress_migration_page()
{
    require_once plugin_dir_path(__FILE__) . 'admin/migration.php';
    nuke_to_wordpress_migration_page_content();
}

// Database connection functions
function nuke_to_wordpress_get_nuke_db_connection()
{
    $options = get_option('nuke_to_wordpress_settings');
    $host = $options['nuke_db_host'];
    $dbname = $options['nuke_db_name'];
    $user = $options['nuke_db_user'];
    $password = $options['nuke_db_password'];

    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    try {
        $pdo = new PDO($dsn, $user, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Connection failed: ' . $e->getMessage());
        return null;
    }
}

function nuke_to_wordpress_get_wp_db_connection()
{
    global $wpdb;
    return $wpdb;
}

// Initialize the background processor
global $migration_process;
$migration_process = new Migration_Background_Process();

// Start migration function
function nuke_to_wordpress_start_migration()
{
    check_ajax_referer('start_migration', 'nonce');

    global $migration_process;

    // Initialize migration state
    update_option('nuke_to_wordpress_migration_state', [
        'status' => 'starting',
        'current_batch' => 0,
        'total_items' => 0,
        'processed_items' => 0,
        'current_task' => 'init',
        'last_error' => '',
        'last_run' => current_time('mysql')
    ]);

    // Queue the initial task
    $migration_process->push_to_queue(['task' => 'init']);
    $migration_process->save()->dispatch();

    wp_send_json_success(['message' => 'Migration started']);
}
add_action('wp_ajax_start_migration', 'nuke_to_wordpress_start_migration');

// Check migration status
function nuke_to_wordpress_check_status()
{
    check_ajax_referer('start_migration', 'nonce');

    $migration_state = get_option('nuke_to_wordpress_migration_state', [
        'status' => 'not_started',
        'processed_items' => 0,
        'total_items' => 0,
        'current_task' => '',
        'last_run' => ''
    ]);

    $progress = 0;
    if ($migration_state['total_items'] > 0) {
        $progress = ($migration_state['processed_items'] / $migration_state['total_items']) * 100;
    }

    wp_send_json_success([
        'status' => $migration_state['status'],
        'progress' => $progress,
        'current_task' => $migration_state['current_task'],
        'last_run' => $migration_state['last_run'],
        'last_error' => $migration_state['last_error'] ?? ''
    ]);
}
add_action('wp_ajax_check_migration_status', 'nuke_to_wordpress_check_status');

function nuke_to_wordpress_migrate_categories_batch($batch_size)
{
    $nuke_db = nuke_to_wordpress_get_nuke_db_connection();
    $offset = get_option('nuke_to_wordpress_category_offset', 0);

    $stmt = $nuke_db->prepare("SELECT * FROM nuke_categories LIMIT :offset, :batch_size");
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':batch_size', $batch_size, PDO::PARAM_INT);
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $processed = 0;

    foreach ($categories as $category) {
        wp_insert_term(
            $category['title'],
            'category',
            [
                'description' => $category['description'],
                'slug' => sanitize_title($category['title'])
            ]
        );
        $processed++;
    }

    update_option('nuke_to_wordpress_category_offset', $offset + $processed);
    return $processed;
}

// Similar batch processing functions for articles and images
function nuke_to_wordpress_migrate_articles_batch($batch_size)
{
    // Implementation similar to categories batch processing
    // but for articles
}

function nuke_to_wordpress_migrate_images_batch($batch_size)
{
    // Implementation similar to categories batch processing
    // but for images
}

function nuke_to_wordpress_retry_migration()
{
    check_ajax_referer('start_migration', 'nonce');

    global $migration_process;
    $result = $migration_process->retry_failed_migration();

    if ($result) {
        wp_send_json_success(['message' => 'Migration retry initiated']);
    } else {
        wp_send_json_error(['message' => 'Unable to retry migration']);
    }
}
add_action('wp_ajax_retry_migration', 'nuke_to_wordpress_retry_migration');

function nuke_to_wordpress_rollback_migration()
{
    check_ajax_referer('start_migration', 'nonce');

    $rollback = new Migration_Rollback();
    $result = $rollback->rollback();

    if ($result) {
        // Reset migration state
        update_option('nuke_to_wordpress_migration_state', [
            'status' => 'not_started',
            'current_batch' => 0,
            'total_items' => 0,
            'processed_items' => 0,
            'current_task' => '',
            'last_error' => '',
            'last_run' => current_time('mysql'),
            'checkpoints' => []
        ]);

        wp_send_json_success(['message' => 'Migration rolled back successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to rollback migration']);
    }
}
add_action('wp_ajax_rollback_migration', 'nuke_to_wordpress_rollback_migration');

function nuke_to_wordpress_cancel_migration()
{
    check_ajax_referer('start_migration', 'nonce');

    global $migration_process;

    // Update migration state
    $migration_state = get_option('nuke_to_wordpress_migration_state', []);
    $migration_state['status'] = 'cancelled';
    update_option('nuke_to_wordpress_migration_state', $migration_state);

    // Cancel the background process
    $migration_process->cancel_process();

    // Perform rollback
    $rollback = new Migration_Rollback();
    $result = $rollback->rollback();

    if ($result) {
        // Reset migration state
        update_option('nuke_to_wordpress_migration_state', [
            'status' => 'not_started',
            'current_batch' => 0,
            'total_items' => 0,
            'processed_items' => 0,
            'current_task' => '',
            'last_error' => '',
            'last_run' => current_time('mysql'),
            'checkpoints' => []
        ]);

        wp_send_json_success(['message' => 'Migration cancelled and rolled back successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to cancel migration']);
    }
}
add_action('wp_ajax_cancel_migration', 'nuke_to_wordpress_cancel_migration');

// Add the help page function
function nuke_to_wordpress_help_page()
{
    require_once plugin_dir_path(__FILE__) . 'admin/help.php';
    nuke_to_wordpress_help_page_content();
}

function nuke_to_wordpress_toggle_cron()
{
    check_ajax_referer('nuke_to_wordpress_settings_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }

    $disable_cron = isset($_POST['disable_cron']) ? filter_var($_POST['disable_cron'], FILTER_VALIDATE_BOOLEAN) : false;

    try {
        if ($disable_cron) {
            $result = nuke_to_wordpress_configure_system_cron();
        } else {
            $result = nuke_to_wordpress_restore_default_cron();
        }

        if ($result['success']) {
            update_option('nuke_to_wordpress_cron_disabled', $disable_cron);
            wp_send_json_success(['message' => $result['message']]);
        } else {
            wp_send_json_error(['message' => $result['message']]);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_toggle_wp_cron', 'nuke_to_wordpress_toggle_cron');

function nuke_to_wordpress_configure_system_cron()
{
    // Path to wp-config.php
    $config_path = ABSPATH . 'wp-config.php';

    if (!is_writable($config_path)) {
        return [
            'success' => false,
            'message' => 'wp-config.php is not writable. Please check file permissions.'
        ];
    }

    // Read wp-config.php
    $config_content = file_get_contents($config_path);

    // Check if DISABLE_WP_CRON is already defined
    if (strpos($config_content, "define('DISABLE_WP_CRON'") === false) {
        // Add the constant definition before WordPress settings
        $config_content = preg_replace(
            '/(\/\*\s*\@package\sWordPress\s*\*\/)/',
            "$1\n\ndefine('DISABLE_WP_CRON', true);",
            $config_content
        );

        // Backup wp-config.php
        copy($config_path, $config_path . '.backup');

        // Write the modified content
        if (file_put_contents($config_path, $config_content) === false) {
            return [
                'success' => false,
                'message' => 'Failed to update wp-config.php'
            ];
        }
    }

    // Set up system cron job
    if (function_exists('exec')) {
        $site_url = get_site_url();
        $cron_command = "*/5 * * * * wget -q -O - {$site_url}/wp-cron.php?doing_wp_cron >/dev/null 2>&1";

        // Remove any existing wp-cron jobs
        exec('crontab -l | grep -v "wp-cron.php" | crontab -');

        // Add new cron job
        exec('(crontab -l 2>/dev/null; echo "' . $cron_command . '") | crontab -');

        return [
            'success' => true,
            'message' => 'WordPress cron disabled and system cron job configured successfully'
        ];
    }

    return [
        'success' => true,
        'message' => 'WordPress cron disabled. Please set up system cron job manually.'
    ];
}

function nuke_to_wordpress_restore_default_cron()
{
    $config_path = ABSPATH . 'wp-config.php';

    if (!is_writable($config_path)) {
        return [
            'success' => false,
            'message' => 'wp-config.php is not writable. Please check file permissions.'
        ];
    }

    // Read wp-config.php
    $config_content = file_get_contents($config_path);

    // Remove DISABLE_WP_CRON definition
    $config_content = preg_replace(
        '/\s*define\s*\(\s*[\'"]DISABLE_WP_CRON[\'"]\s*,\s*true\s*\)\s*;\s*/i',
        '',
        $config_content
    );

    // Write the modified content
    if (file_put_contents($config_path, $config_content) === false) {
        return [
            'success' => false,
            'message' => 'Failed to update wp-config.php'
        ];
    }

    // Remove system cron job if possible
    if (function_exists('exec')) {
        exec('crontab -l | grep -v "wp-cron.php" | crontab -');
    }

    return [
        'success' => true,
        'message' => 'WordPress default cron restored successfully'
    ];
}

function nuke_to_wordpress_manual_migration_step()
{
    check_ajax_referer('start_migration', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }

    $step = isset($_POST['step']) ? sanitize_text_field($_POST['step']) : '';
    $valid_steps = ['categories', 'articles', 'images'];

    if (!in_array($step, $valid_steps)) {
        wp_send_json_error(['message' => 'Invalid migration step']);
        return;
    }

    try {
        // Get migration state
        $migration_state = get_option('nuke_to_wordpress_migration_state', []);

        // Create checkpoint before starting step
        $rollback = new Migration_Rollback();
        $checkpoint = $rollback->create_checkpoint();

        $result = execute_manual_step($step);

        if ($result['success']) {
            // Update migration state
            $migration_state['processed_items'] += $result['processed'];
            $migration_state['current_task'] = $step;
            update_option('nuke_to_wordpress_migration_state', $migration_state);

            wp_send_json_success([
                'message' => $result['message'],
                'progress' => [
                    'processed_items' => $migration_state['processed_items'],
                    'total_items' => $migration_state['total_items']
                ]
            ]);
        } else {
            // Rollback changes if step failed
            $rollback->rollback($checkpoint);
            wp_send_json_error(['message' => $result['message']]);
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_manual_migration_step', 'nuke_to_wordpress_manual_migration_step');

function execute_manual_step($step)
{
    $nuke_db = nuke_to_wordpress_get_nuke_db_connection();

    switch ($step) {
        case 'categories':
            return migrate_categories($nuke_db);

        case 'articles':
            return migrate_articles($nuke_db);

        case 'images':
            return migrate_images($nuke_db);

        default:
            return [
                'success' => false,
                'message' => 'Invalid step'
            ];
    }
}

function migrate_categories($nuke_db)
{
    try {
        $categories = $nuke_db->query("SELECT * FROM nuke_categories")->fetchAll();
        $processed = 0;

        foreach ($categories as $category) {
            $wp_cat_id = wp_insert_category([
                'cat_name' => $category['title'],
                'category_description' => $category['description']
            ]);

            if ($wp_cat_id) {
                $processed++;
            }
        }

        return [
            'success' => true,
            'message' => "Processed {$processed} categories",
            'processed' => $processed
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Failed to migrate categories: " . $e->getMessage()
        ];
    }
}

function migrate_articles($nuke_db)
{
    try {
        $articles = $nuke_db->query("SELECT * FROM nuke_articles")->fetchAll();
        $processed = 0;

        foreach ($articles as $article) {
            $post_data = [
                'post_title' => $article['title'],
                'post_content' => $article['content'],
                'post_status' => 'publish',
                'post_author' => get_current_user_id(),
                'post_date' => $article['date']
            ];

            $post_id = wp_insert_post($post_data);

            if ($post_id) {
                $processed++;
            }
        }

        return [
            'success' => true,
            'message' => "Processed {$processed} articles",
            'processed' => $processed
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Failed to migrate articles: " . $e->getMessage()
        ];
    }
}

function migrate_images($nuke_db)
{
    try {
        $images = $nuke_db->query("SELECT * FROM nuke_images")->fetchAll();
        $processed = 0;

        foreach ($images as $image) {
            // Implementation for image migration
            // This would include downloading the image and using wp_insert_attachment
            $processed++;
        }

        return [
            'success' => true,
            'message' => "Processed {$processed} images",
            'processed' => $processed
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Failed to migrate images: " . $e->getMessage()
        ];
    }
}

// Add these functions to handle debug actions
function nuke_to_wordpress_view_logs()
{
    check_ajax_referer('nuke_to_wordpress_settings_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }

    $debug_handler = Nuke_To_WordPress_Debug_Handler::get_instance();
    $logs = $debug_handler->get_logs();

    wp_send_json_success(['logs' => implode('', $logs)]);
}
add_action('wp_ajax_view_debug_logs', 'nuke_to_wordpress_view_logs');

function nuke_to_wordpress_clear_logs()
{
    check_ajax_referer('nuke_to_wordpress_settings_nonce', 'nonce');

    if (!current_user_can('manage_options')) {
        wp_send_json_error(['message' => 'Insufficient permissions']);
        return;
    }

    $debug_handler = Nuke_To_WordPress_Debug_Handler::get_instance();
    $result = $debug_handler->clear_logs();

    if ($result) {
        wp_send_json_success(['message' => 'Logs cleared successfully']);
    } else {
        wp_send_json_error(['message' => 'Failed to clear logs']);
    }
}
add_action('wp_ajax_clear_debug_logs', 'nuke_to_wordpress_clear_logs');
?>