<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'vendor/wp-background-processing/wp-background-processing.php';

class Migration_Background_Process extends WP_Background_Process
{
    protected $action = 'nuke_migration';
    private $migration_state;

    public function __construct()
    {
        parent::__construct();
        $this->migration_state = get_option('nuke_to_wordpress_migration_state', [
            'status' => 'not_started',
            'current_batch' => 0,
            'total_items' => 0,
            'processed_items' => 0,
            'current_task' => '',
            'last_error' => '',
            'last_run' => current_time('mysql')
        ]);
    }

    protected function task($item)
    {
        try {
            $this->migration_state['last_run'] = current_time('mysql');
            $this->migration_state['current_task'] = $item['task'];

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
        } catch (Exception $e) {
            $this->migration_state['last_error'] = $e->getMessage();
            error_log('Migration error: ' . $e->getMessage());
        }

        $this->update_state();
        return false;
    }

    protected function complete()
    {
        parent::complete();
        $this->migration_state['status'] = 'completed';
        $this->update_state();
    }

    private function init_migration()
    {
        try {
            $nuke_db = nuke_to_wordpress_get_nuke_db_connection();

            $categories_count = $nuke_db->query("SELECT COUNT(*) FROM nuke_categories")->fetchColumn();
            $articles_count = $nuke_db->query("SELECT COUNT(*) FROM nuke_articles")->fetchColumn();
            $images_count = $nuke_db->query("SELECT COUNT(*) FROM nuke_images")->fetchColumn();

            $this->migration_state['total_items'] = $categories_count + $articles_count + $images_count;
            $this->migration_state['status'] = 'in_progress';
            $this->update_state();

            // Queue up the categories processing
            return [
                'task' => 'categories',
                'offset' => 0,
                'batch_size' => 50
            ];
        } catch (Exception $e) {
            $this->migration_state['last_error'] = $e->getMessage();
            $this->update_state();
            return false;
        }
    }

    private function process_categories($item)
    {
        $processed = nuke_to_wordpress_migrate_categories_batch($item['batch_size']);
        $this->migration_state['processed_items'] += $processed;

        if ($processed < $item['batch_size']) {
            // Move to articles
            return [
                'task' => 'articles',
                'offset' => 0,
                'batch_size' => 50
            ];
        }

        // Continue with next batch of categories
        return [
            'task' => 'categories',
            'offset' => $item['offset'] + $processed,
            'batch_size' => $item['batch_size']
        ];
    }

    private function process_articles($item)
    {
        $processed = nuke_to_wordpress_migrate_articles_batch($item['batch_size']);
        $this->migration_state['processed_items'] += $processed;

        if ($processed < $item['batch_size']) {
            // Move to images
            return [
                'task' => 'images',
                'offset' => 0,
                'batch_size' => 50
            ];
        }

        // Continue with next batch of articles
        return [
            'task' => 'articles',
            'offset' => $item['offset'] + $processed,
            'batch_size' => $item['batch_size']
        ];
    }

    private function process_images($item)
    {
        $processed = nuke_to_wordpress_migrate_images_batch($item['batch_size']);
        $this->migration_state['processed_items'] += $processed;

        if ($processed < $item['batch_size']) {
            return false; // Complete the migration
        }

        // Continue with next batch of images
        return [
            'task' => 'images',
            'offset' => $item['offset'] + $processed,
            'batch_size' => $item['batch_size']
        ];
    }

    private function update_state()
    {
        update_option('nuke_to_wordpress_migration_state', $this->migration_state);
    }
}