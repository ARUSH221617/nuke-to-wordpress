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

function nuke_to_wordpress_activate() {
    // Code to run on activation
}

function nuke_to_wordpress_deactivate() {
    // Code to run on deactivation
}

function nuke_to_wordpress_admin_menu() {
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

function nuke_to_wordpress_enqueue_scripts($hook) {
    // Enqueue scripts and styles
    if ($hook == 'toplevel_page_nuke-to-wordpress-settings' || $hook == 'nuke-to-wordpress_page_nuke-to-wordpress-migration') {
        wp_enqueue_script('nuke-to-wordpress-script', plugin_dir_url(__FILE__) . 'admin/js/nuke-to-wordpress.js', array('jquery'), '1.0', true);
        wp_enqueue_style('nuke-to-wordpress-style', plugin_dir_url(__FILE__) . 'admin/css/nuke-to-wordpress.min.css', array(), '1.0', 'all');
    }
}

function nuke_to_wordpress_settings_page() {
    // Display settings page
    include plugin_dir_path(__FILE__) . 'admin/settings.php';
}

function nuke_to_wordpress_migration_page() {
    // Display migration page
    include plugin_dir_path(__FILE__) . 'admin/migration.php';
}

// Database connection functions
function nuke_to_wordpress_get_nuke_db_connection() {
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

function nuke_to_wordpress_get_wp_db_connection() {
    global $wpdb;
    return $wpdb;
}

// Migration functions
function nuke_to_wordpress_start_migration() {
    $nuke_db = nuke_to_wordpress_get_nuke_db_connection();
    $wp_db = nuke_to_wordpress_get_wp_db_connection();

    if (!$nuke_db || !$wp_db) {
        echo '<div class="notice notice-error is-dismissible"><p>Database connection failed. Please check your settings.</p></div>';
        return;
    }

    // Placeholder for migration logic
    echo '<div class="notice notice-success is-dismissible"><p>Migration started. Please check the progress below.</p></div>';

    // Example: Migrate categories
    try {
        nuke_to_wordpress_migrate_categories($nuke_db, $wp_db);
    } catch (Exception $e) {
        echo '<div class="notice notice-error is-dismissible"><p>Error migrating categories: ' . esc_html($e->getMessage()) . '</p></div>';
        error_log('Error migrating categories: ' . $e->getMessage());
    }

    // Migrate articles
    try {
        nuke_to_wordpress_migrate_articles($nuke_db, $wp_db);
    } catch (Exception $e) {
        echo '<div class="notice notice-error is-dismissible"><p>Error migrating articles: ' . esc_html($e->getMessage()) . '</p></div>';
        error_log('Error migrating articles: ' . $e->getMessage());
    }

    // Migrate images
    try {
        nuke_to_wordpress_migrate_images($nuke_db, $wp_db);
    } catch (Exception $e) {
        echo '<div class="notice notice-error is-dismissible"><p>Error migrating images: ' . esc_html($e->getMessage()) . '</p></div>';
        error_log('Error migrating images: ' . $e->getMessage());
    }

    // Migrate tags
    try {
        nuke_to_wordpress_migrate_tags($nuke_db, $wp_db);
    } catch (Exception $e) {
        echo '<div class="notice notice-error is-dismissible"><p>Error migrating tags: ' . esc_html($e->getMessage()) . '</p></div>';
        error_log('Error migrating tags: ' . $e->getMessage());
    }

    // Add more migration functions here
}

function nuke_to_wordpress_migrate_categories($nuke_db, $wp_db) {
    $stmt = $nuke_db->query("SELECT * FROM nuke_categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($categories as $category) {
        $term_id = wp_insert_term(
            $category['title'],
            'category',
            array(
                'description' => $category['description'],
                'slug' => sanitize_title($category['title'])
            )
        );

        if (is_wp_error($term_id)) {
            error_log('Error inserting category: ' . $term_id->get_error_message());
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Category ' . esc_html($category['title']) . ' migrated successfully.</p></div>';
        }
    }
}

function nuke_to_wordpress_migrate_articles($nuke_db, $wp_db) {
    $stmt = $nuke_db->query("SELECT * FROM nuke_articles");
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($articles as $article) {
        $post_data = array(
            'post_title'    => $article['title'],
            'post_content'  => $article['content'],
            'post_status'   => 'publish',
            'post_author'   => 1, // Default author ID
            'post_date'     => $article['date'],
            'post_date_gmt' => get_gmt_from_date($article['date']),
            'post_name'     => sanitize_title($article['title']),
            'post_type'     => 'post',
            'comment_status' => 'open',
            'ping_status'    => 'open',
        );

        $post_id = wp_insert_post($post_data);

        if (is_wp_error($post_id)) {
            error_log('Error inserting article: ' . $post_id->get_error_message());
        } else {
            echo '<div class="notice notice-success is-dismissible"><p>Article ' . esc_html($article['title']) . ' migrated successfully.</p></div>';

            // Migrate categories for the article
            $category_ids = explode(',', $article['categories']);
            wp_set_post_categories($post_id, $category_ids);

            // Migrate tags for the article
            $tags = explode(',', $article['tags']);
            wp_set_post_tags($post_id, $tags);
        }
    }
}

function nuke_to_wordpress_migrate_images($nuke_db, $wp_db) {
    $stmt = $nuke_db->query("SELECT * FROM nuke_images");
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($images as $image) {
        $image_path = $image['path'];
        $image_url = plugin_dir_path(__FILE__) . 'files/images/' . $image_path;

        if (file_exists($image_url)) {
            $attachment = array(
                'guid'           => wp_upload_dir()['url'] . '/' . basename($image_url),
                'post_mime_type' => wp_check_filetype(basename($image_url))['type'],
                'post_title'     => sanitize_file_name(basename($image_url)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            $attach_id = wp_insert_attachment($attachment, $image_url);

            if (is_wp_error($attach_id)) {
                error_log('Error inserting image: ' . $attach_id->get_error_message());
            } else {
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $image_url);
                wp_update_attachment_metadata($attach_id, $attach_data);

                echo '<div class="notice notice-success is-dismissible"><p>Image ' . esc_html(basename($image_url)) . ' migrated successfully.</p></div>';
            }
        } else {
            error_log('Image file not found: ' . $image_url);
        }
    }
}

function nuke_to_wordpress_migrate_tags($nuke_db, $wp_db) {
    $stmt = $nuke_db->query("SELECT * FROM nuke_tags");
    $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $tag_map = array();

    foreach ($tags as $tag) {
        $term_id = wp_insert_term(
            $tag['name'],
            'post_tag',
            array(
                'description' => $tag['description'],
                'slug' => sanitize_title($tag['name'])
            )
        );

        if (is_wp_error($term_id)) {
            error_log('Error inserting tag: ' . $term_id->get_error_message());
        } else {
            $tag_map[$tag['id']] = $term_id['term_id'];
            echo '<div class="notice notice-success is-dismissible"><p>Tag ' . esc_html($tag['name']) . ' migrated successfully.</p></div>';
        }
    }

    // Migrate tag associations with articles
    $stmt = $nuke_db->query("SELECT * FROM nuke_article_tags");
    $article_tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($article_tags as $article_tag) {
        $article_id = $article_tag['article_id'];
        $tag_id = $article_tag['tag_id'];

        if (isset($tag_map[$tag_id])) {
            wp_set_post_tags($article_id, array($tag_map[$tag_id]), true);
        } else {
            error_log('Tag ID not found in map: ' . $tag_id);
        }
    }
}
?>
