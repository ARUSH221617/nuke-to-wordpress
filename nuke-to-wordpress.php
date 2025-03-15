<?php
/*
Plugin Name: Nuke to WordPress Migration
Plugin URI: https://example.com/nuke-to-wordpress
Description: A plugin to migrate data from Nuke PHP to WordPress.
Version: 1.0
Author: Your Name
Author URI: https://example.com
License: GPL2
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Activation hook
register_activation_hook(__FILE__, 'nuke_to_wordpress_activate');

// Deactivation hook
register_deactivation_hook(__FILE__, 'nuke_to_wordpress_deactivate');

// Initialization code
add_action('admin_menu', 'nuke_to_wordpress_admin_menu');
add_action('admin_enqueue_scripts', 'nuke_to_wordpress_enqueue_scripts');

function nuke_to_wordpress_activate()
{
    // Code to run on activation
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
}

function nuke_to_wordpress_enqueue_scripts($hook)
{
    // Enqueue scripts and styles
    if ($hook == 'toplevel_page_nuke-to-wordpress-settings' || $hook == 'nuke-to-wordpress_page_nuke-to-wordpress-migration') {
        wp_enqueue_script('nuke-to-wordpress-script', plugin_dir_url(__FILE__) . 'admin/js/nuke-to-wordpress.js', array('jquery'), '1.0', true);
        wp_enqueue_style('nuke-to-wordpress-style', plugin_dir_url(__FILE__) . 'admin/css/nuke-to-wordpress.min.css', array(), '1.0', 'all');
    }
}

function nuke_to_wordpress_settings_page()
{
    // Display settings page
    include plugin_dir_path(__FILE__) . 'admin/settings.php';
}

function nuke_to_wordpress_migration_page()
{
    // Display migration page
    include plugin_dir_path(__FILE__) . 'admin/migration.php';
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

// Include the background processor
require_once plugin_dir_path(__FILE__) . 'includes/class-migration-background-process.php';

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
?>