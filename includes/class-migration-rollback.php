<?php
if (!defined('ABSPATH')) {
    exit;
}

class Migration_Rollback {
    private $log_table;
    private $batch_size = 50;

    public function __construct() {
        global $wpdb;
        $this->log_table = $wpdb->prefix . 'nuke_migration_log';
        $this->create_log_table();
    }

    private function create_log_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$this->log_table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            operation varchar(50) NOT NULL,
            entity_type varchar(50) NOT NULL,
            wp_id bigint(20) NOT NULL,
            nuke_id bigint(20) NOT NULL,
            additional_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY entity_type (entity_type),
            KEY wp_id (wp_id),
            KEY nuke_id (nuke_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function log_operation($operation, $entity_type, $wp_id, $nuke_id, $additional_data = null) {
        global $wpdb;
        
        return $wpdb->insert(
            $this->log_table,
            [
                'operation' => $operation,
                'entity_type' => $entity_type,
                'wp_id' => $wp_id,
                'nuke_id' => $nuke_id,
                'additional_data' => $additional_data ? json_encode($additional_data) : null
            ],
            ['%s', '%s', '%d', '%d', '%s']
        );
    }

    public function rollback($from_checkpoint = null) {
        try {
            global $wpdb;
            
            // Start transaction
            $wpdb->query('START TRANSACTION');

            // Get operations to rollback
            $where = '';
            if ($from_checkpoint) {
                $where = $wpdb->prepare(" WHERE created_at >= %s", $from_checkpoint);
            }
            
            $operations = $wpdb->get_results(
                "SELECT * FROM {$this->log_table} {$where} ORDER BY id DESC"
            );

            foreach ($operations as $operation) {
                switch ($operation->entity_type) {
                    case 'category':
                        $this->rollback_category($operation);
                        break;
                    case 'post':
                        $this->rollback_post($operation);
                        break;
                    case 'attachment':
                        $this->rollback_attachment($operation);
                        break;
                }
                
                // Delete the log entry
                $wpdb->delete($this->log_table, ['id' => $operation->id]);
            }

            // Commit transaction
            $wpdb->query('COMMIT');
            
            return true;
        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            error_log('Migration rollback failed: ' . $e->getMessage());
            return false;
        }
    }

    private function rollback_category($operation) {
        if ($operation->wp_id) {
            wp_delete_term($operation->wp_id, 'category');
        }
    }

    private function rollback_post($operation) {
        if ($operation->wp_id) {
            wp_delete_post($operation->wp_id, true);
        }
    }

    private function rollback_attachment($operation) {
        if ($operation->wp_id) {
            // Get attachment path before deletion
            $file = get_attached_file($operation->wp_id);
            
            // Delete the attachment
            wp_delete_attachment($operation->wp_id, true);
            
            // Delete physical file if it exists
            if ($file && file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function create_checkpoint() {
        return current_time('mysql');
    }
}