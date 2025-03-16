<?php
namespace NukeToWordPress;

use WP_Background_Process;
use Exception;
use Nuke_To_WordPress_Debug_Handler;

if (!defined('ABSPATH')) {
    exit;
}

class Migration_Background_Process extends WP_Background_Process
{
    protected $action = 'nuke_migration';
    private $migration_state;
    private $rollback;
    private $current_checkpoint;

    public function __construct()
    {
        parent::__construct();
        $this->rollback = new Migration_Rollback();
        $this->migration_state = get_option('nuke_to_wordpress_migration_state', [
            'status' => 'not_started',
            'current_batch' => 0,
            'total_items' => 0,
            'processed_items' => 0,
            'current_task' => '',
            'last_error' => '',
            'last_run' => current_time('mysql'),
            'checkpoints' => []
        ]);
    }

    public function cancel_process()
    {
        $batch = $this->get_batch();

        if (!empty($batch)) {
            $this->delete($batch->key);
        }

        $this->data = [];
        $this->save();

        global $wpdb;
        $table = $wpdb->options;
        $key = $wpdb->esc_like($this->identifier . '_batch_') . '%';
        $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE option_name LIKE %s", $key));

        return true;
    }

    protected function task($item)
    {
        try {
            if ($item['task'] !== $this->migration_state['current_task']) {
                $this->current_checkpoint = $this->rollback->create_checkpoint();
                $this->migration_state['checkpoints'][$item['task']] = $this->current_checkpoint;
                $this->update_state();
            }

            $this->migration_state['last_run'] = current_time('mysql');
            $this->migration_state['current_task'] = $item['task'];

            $result = $this->process_task($item);

            if ($result === false) {
                throw new Exception('Task processing failed');
            }

            $this->update_state();
            return $result;

        } catch (Exception $e) {
            $this->handle_error($e);
            return false;
        }
    }

    private function process_task($item)
    {
        switch ($item['task']) {
            case 'init':
                return $this->init_migration();
            case 'categories':
                return $this->process_categories($item);
            case 'articles':
                return $this->process_articles($item);
            case 'images':
                return $this->process_images($item);
        }
        return false;
    }

    private function handle_error($exception)
    {
        $debug = Nuke_To_WordPress_Debug_Handler::get_instance();
        $debug->log('Migration error occurred', [
            'error' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ], 'error');

        $this->migration_state['last_error'] = $exception->getMessage();
        $this->migration_state['status'] = 'failed';

        if ($this->current_checkpoint) {
            $this->rollback->rollback($this->current_checkpoint);
        }

        $this->update_state();
    }

    protected function complete()
    {
        parent::complete();
        $this->migration_state['status'] = 'completed';
        $this->update_state();
    }

    private function update_state()
    {
        update_option('nuke_to_wordpress_migration_state', $this->migration_state);
    }

    private function init_migration()
    {
        try {
            $nuke_db = nuke_to_wordpress_get_nuke_db_connection();

            // Count total items to migrate
            $categories_count = $nuke_db->query("SELECT COUNT(*) FROM nuke_categories")->fetchColumn();
            $articles_count = $nuke_db->query("SELECT COUNT(*) FROM nuke_articles")->fetchColumn();
            $images_count = $nuke_db->query("SELECT COUNT(*) FROM nuke_images")->fetchColumn();

            $total_items = $categories_count + $articles_count + $images_count;

            // Update migration state
            $this->migration_state['status'] = 'in_progress';
            $this->migration_state['total_items'] = $total_items;
            $this->update_state();

            // Queue up the migration tasks
            $this->push_to_queue(['task' => 'categories']);
            $this->push_to_queue(['task' => 'articles']);
            $this->push_to_queue(['task' => 'images']);
            $this->save();

            return true;
        } catch (Exception $e) {
            throw new Exception('Failed to initialize migration: ' . $e->getMessage());
        }
    }

    private function process_categories($item)
    {
        try {
            $nuke_db = nuke_to_wordpress_get_nuke_db_connection();
            $categories = $nuke_db->query("SELECT * FROM nuke_categories")->fetchAll();

            foreach ($categories as $category) {
                // Check if category already exists
                $existing_cat = get_term_by('name', $category['title'], 'category');
                if ($existing_cat) {
                    continue;
                }

                // Insert category
                $wp_cat_id = wp_insert_category([
                    'cat_name' => $category['title'],
                    'category_description' => $category['description'],
                    'category_nicename' => sanitize_title($category['title'])
                ]);

                if (is_wp_error($wp_cat_id)) {
                    throw new Exception('Failed to insert category: ' . $wp_cat_id->get_error_message());
                }

                // Log the migration
                $this->rollback->log_operation('insert', 'category', $wp_cat_id, $category['id']);
                $this->migration_state['processed_items']++;
            }

            $this->update_state();
            return true;
        } catch (Exception $e) {
            throw new Exception('Category migration failed: ' . $e->getMessage());
        }
    }

    private function process_articles($item)
    {
        try {
            $nuke_db = nuke_to_wordpress_get_nuke_db_connection();
            $articles = $nuke_db->query("SELECT * FROM nuke_articles")->fetchAll();

            foreach ($articles as $article) {
                // Check if article already exists
                $existing_post = get_page_by_title($article['title'], OBJECT, 'post');
                if ($existing_post) {
                    continue;
                }

                // Prepare post data
                $post_data = [
                    'post_title' => $article['title'],
                    'post_content' => $article['content'],
                    'post_status' => 'publish',
                    'post_author' => get_current_user_id(),
                    'post_date' => $article['date'],
                    'post_type' => 'post'
                ];

                // Insert post
                $post_id = wp_insert_post($post_data, true);

                if (is_wp_error($post_id)) {
                    throw new Exception('Failed to insert article: ' . $post_id->get_error_message());
                }

                // Set categories if they exist
                if (!empty($article['category_id'])) {
                    wp_set_post_categories($post_id, [$article['category_id']]);
                }

                // Log the migration
                $this->rollback->log_operation('insert', 'post', $post_id, $article['id']);
                $this->migration_state['processed_items']++;
            }

            $this->update_state();
            return true;
        } catch (Exception $e) {
            throw new Exception('Article migration failed: ' . $e->getMessage());
        }
    }

    private function process_images($item)
    {
        try {
            $nuke_db = nuke_to_wordpress_get_nuke_db_connection();
            $images = $nuke_db->query("SELECT * FROM nuke_images")->fetchAll();
            $upload_dir = wp_upload_dir();

            foreach ($images as $image) {
                // Create year/month directories if they don't exist
                $year_month = date('Y/m', strtotime($image['date']));
                $upload_path = $upload_dir['basedir'] . '/' . $year_month;

                if (!file_exists($upload_path)) {
                    wp_mkdir_p($upload_path);
                }

                // Download and save the image
                $image_url = $image['url'];
                $image_data = file_get_contents($image_url);
                $filename = basename($image_url);
                $file_path = $upload_path . '/' . $filename;

                if (file_put_contents($file_path, $image_data) === false) {
                    throw new Exception('Failed to save image: ' . $filename);
                }

                // Prepare attachment data
                $wp_filetype = wp_check_filetype($filename);
                $attachment = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title' => sanitize_file_name($filename),
                    'post_content' => '',
                    'post_status' => 'inherit'
                ];

                // Insert attachment
                $attach_id = wp_insert_attachment($attachment, $file_path);

                if (is_wp_error($attach_id)) {
                    throw new Exception('Failed to insert attachment: ' . $attach_id->get_error_message());
                }

                // Generate attachment metadata
                require_once(ABSPATH . 'wp-admin/includes/image.php');
                $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                wp_update_attachment_metadata($attach_id, $attach_data);

                // Log the migration
                $this->rollback->log_operation('insert', 'attachment', $attach_id, $image['id']);
                $this->migration_state['processed_items']++;
            }

            $this->update_state();
            return true;
        } catch (Exception $e) {
            throw new Exception('Image migration failed: ' . $e->getMessage());
        }
    }
}
