<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once plugin_dir_path(__FILE__) . 'vendor/wp-background-processing/wp-background-processing.php';

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

    protected function task($item)
    {
        try {
            // Create checkpoint before starting new task
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
        $this->migration_state['last_error'] = $exception->getMessage();
        $this->migration_state['status'] = 'failed';
        
        // Rollback changes from current task
        if ($this->current_checkpoint) {
            $this->rollback->rollback($this->current_checkpoint);
        }
        
        $this->update_state();
        error_log('Migration error: ' . $exception->getMessage());
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
        try {
            $processed = nuke_to_wordpress_migrate_categories_batch($item['batch_size']);
            
            // Log each category creation
            foreach ($processed['items'] as $category) {
                $this->rollback->log_operation(
                    'create',
                    'category',
                    $category['wp_id'],
                    $category['nuke_id'],
                    ['name' => $category['name']]
                );
            }

            $this->migration_state['processed_items'] += count($processed['items']);
            
            if ($processed['count'] < $item['batch_size']) {
                return [
                    'task' => 'articles',
                    'offset' => 0,
                    'batch_size' => 50
                ];
            }

            return [
                'task' => 'categories',
                'offset' => $item['offset'] + $processed['count'],
                'batch_size' => $item['batch_size']
            ];

        } catch (Exception $e) {
            throw new Exception('Category migration failed: ' . $e->getMessage());
        }
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

    public function retry_failed_migration()
    {
        if ($this->migration_state['status'] !== 'failed') {
            return false;
        }

        // Reset state for current task
        $current_task = $this->migration_state['current_task'];
        $checkpoint = $this->migration_state['checkpoints'][$current_task] ?? null;
        
        if ($checkpoint) {
            // Rollback changes from failed task
            $this->rollback->rollback($checkpoint);
            
            // Remove checkpoint and retry task
            unset($this->migration_state['checkpoints'][$current_task]);
            $this->migration_state['status'] = 'in_progress';
            $this->migration_state['last_error'] = '';
            $this->update_state();

            // Re-queue the failed task
            $this->push_to_queue([
                'task' => $current_task,
                'offset' => 0,
                'batch_size' => 50
            ]);
            $this->save()->dispatch();
            
            return true;
        }

        return false;
    }
}
