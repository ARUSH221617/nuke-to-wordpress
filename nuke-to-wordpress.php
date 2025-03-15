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

function nuke_to_wordpress_activate()
{
    // Add the settings to the whitelist using the correct method
    add_option('nuke_to_wordpress_settings', [
        'nuke_db_host' => 'localhost',
        'nuke_db_name' => '',
        'nuke_db_user' => '',
        'nuke_db_password' => ''
    ]);
}

function nuke_to_wordpress_deactivate()
{
    // Code to run on deactivation
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

function nuke_to_wordpress_enqueue_scripts($hook) {
    // Only load on our plugin's pages
    if (strpos($hook, 'nuke-to-wordpress') === false) {
        return;
    }

    wp_enqueue_script(
        'nuke-to-wordpress-admin',
        plugins_url('admin/js/admin.js', __FILE__),
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
    check_ajax_referer('migration_status', 'nonce');

    $migration_state = get_option('nuke_to_wordpress_migration_state');
    $progress = 0;

    if ($migration_state['total_items'] > 0) {
        $progress = ($migration_state['processed_items'] / $migration_state['total_items']) * 100;
    }

    wp_send_json_success([
        'status' => $migration_state['status'],
        'progress' => $progress,
        'current_task' => $migration_state['current_task'],
        'last_error' => $migration_state['last_error'],
        'last_run' => $migration_state['last_run']
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

function nuke_to_wordpress_cancel_migration() {
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
?>
