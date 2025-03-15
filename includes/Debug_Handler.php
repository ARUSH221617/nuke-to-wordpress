<?php

class Nuke_To_WordPress_Debug_Handler {
    private static $instance = null;
    private $log_file;
    private $is_debug_enabled;

    private function __construct() {
        $this->is_debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
        $this->log_file = WP_CONTENT_DIR . '/nuke-to-wordpress-debug.log';
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function log($message, $context = [], $level = 'info') {
        if (!$this->is_debug_enabled) {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $context_string = !empty($context) ? json_encode($context) : '';
        $log_entry = sprintf(
            "[%s] [%s] %s %s\n",
            $timestamp,
            strtoupper($level),
            $message,
            $context_string
        );

        error_log($log_entry, 3, $this->log_file);

        // Also log to WordPress debug log if enabled
        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf('Nuke to WordPress: %s', $log_entry));
        }
    }

    public function get_logs($lines = 100) {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $logs = file($this->log_file);
        return array_slice($logs, -$lines);
    }

    public function clear_logs() {
        if (file_exists($this->log_file)) {
            return unlink($this->log_file);
        }
        return true;
    }
}