<?php

class Nuke_To_WordPress_Debug_Handler
{
    private static $instance = null;
    private $log_file;
    private $is_debug_enabled;
    private $max_file_size = 5242880; // 5MB
    private $valid_levels = ['debug', 'info', 'warning', 'error'];

    private function __construct()
    {
        $this->is_debug_enabled = defined('WP_DEBUG') && WP_DEBUG;
        $this->log_file = WP_CONTENT_DIR . '/plugins/nuke-to-wordpress/nuke-to-wordpress-debug.log';
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function log($message, $context = [], $level = 'info')
    {
        if (!$this->is_debug_enabled) {
            return;
        }

        if (!in_array(strtolower($level), $this->valid_levels)) {
            $level = 'info';
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

        $this->rotate_logs();

        if (!is_writable(dirname($this->log_file))) {
            error_log('Nuke to WordPress: Log directory is not writable');
            return false;
        }

        $result = error_log($log_entry, 3, $this->log_file);

        if (defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log(sprintf('Nuke to WordPress: %s', $log_entry));
        }

        return $result;
    }

    public function get_logs($limit = null)
    {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $logs = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$logs) {
            return [];
        }

        if ($limit && is_numeric($limit)) {
            $logs = array_slice($logs, -$limit);
        }

        return $logs;
    }

    private function rotate_logs()
    {
        if (!file_exists($this->log_file)) {
            return;
        }

        if (filesize($this->log_file) < $this->max_file_size) {
            return;
        }

        $backup_file = $this->log_file . '.' . date('Y-m-d-H-i-s');
        rename($this->log_file, $backup_file);
    }

    public function clear_logs()
    {
        if (!file_exists($this->log_file)) {
            return true;
        }
        if (!is_writable($this->log_file)) {
            return false;
        }
        return @unlink($this->log_file);
    }

    /**
     * Handles and logs exceptions
     */
    public function handle_exception(Throwable $exception)
    {
        $context = [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'code' => $exception->getCode(),
            'trace' => $exception->getTraceAsString()
        ];

        $this->log(
            sprintf('Exception: %s', $exception->getMessage()),
            $context,
            'error'
        );

        if ($this->is_debug_enabled) {
            throw $exception;
        }

        return false;
    }

    /**
     * Sets up exception handling for the plugin
     */
    public function register_exception_handler()
    {
        set_exception_handler([$this, 'handle_exception']);
        restore_error_handler();
        set_error_handler(function ($severity, $message, $file, $line) {
            if (!(error_reporting() & $severity)) {
                return false;
            }

            $context = [
                'severity' => $severity,
                'file' => $file,
                'line' => $line
            ];

            $this->log($message, $context, 'error');
            return true;
        });
    }

    /**
     * Safely executes a callback with exception handling
     */
    public function safe_execute(callable $callback, $default_return = null)
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            $this->handle_exception($e);
            return $default_return;
        }
    }
}
