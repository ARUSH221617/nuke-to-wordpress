<?php
namespace NukeToWordPress;

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
            PRIMARY KEY (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private function rollback_attachment($operation) {
        if ($operation->wp_id) {
            $file = get_attached_file($operation->wp_id);
            wp_delete_attachment($operation->wp_id, true);
            if ($file && file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function create_checkpoint() {
        return current_time('mysql');
    }
}
