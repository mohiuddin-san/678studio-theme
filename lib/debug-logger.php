<?php
/**
 * WordPress Debug Logger Class
 * Provides comprehensive logging for WordPress theme development
 * Enables Claude Code to autonomously debug WordPress functionality
 */

class WordPressDebugLogger {
    private static $instance = null;
    private $log_dir;
    private $log_file;
    private $session_id;
    private $buffer = [];
    private $buffer_size = 0;
    private $max_buffer_size = 50;
    
    private function __construct() {
        $this->log_dir = WP_CONTENT_DIR . '/debug-logs/';
        $this->session_id = $this->generateSessionId();
        $this->log_file = $this->log_dir . 'wp-debug-' . date('Y-m-d') . '.log';
        
        // Create log directory if it doesn't exist
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
        }
        
        // Auto-flush buffer on shutdown
        register_shutdown_function([$this, 'flush']);
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Main logging method
     * @param string $level Log level (DEBUG, INFO, WARN, ERROR)
     * @param string $message Log message
     * @param array $context Additional context data
     */
    public function log($level, $message, $context = []) {
        $timestamp = current_time('mysql');
        $memory_usage = memory_get_usage();
        $memory_peak = memory_get_peak_usage();
        
        // Get current WordPress context
        $wp_context = $this->getWordPressContext();
        
        $log_entry = [
            'timestamp' => $timestamp,
            'level' => strtoupper($level),
            'session_id' => $this->session_id,
            'message' => $message,
            'context' => $context,
            'wp_context' => $wp_context,
            'memory_usage' => $memory_usage,
            'memory_peak' => $memory_peak,
            'backtrace' => $this->getBacktrace()
        ];
        
        $this->buffer[] = $log_entry;
        $this->buffer_size++;
        
        // Auto-flush if buffer is full
        if ($this->buffer_size >= $this->max_buffer_size) {
            $this->flush();
        }
        
        // Immediate flush for errors
        if ($level === 'ERROR') {
            $this->flush();
        }
    }
    
    /**
     * Info level logging
     */
    public function info($message, $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    /**
     * Debug level logging
     */
    public function debug($message, $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    /**
     * Error level logging
     */
    public function error($message, $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    /**
     * Warning level logging
     */
    public function warn($message, $context = []) {
        $this->log('WARN', $message, $context);
    }
    
    /**
     * Flush buffer to file
     */
    public function flush() {
        if (empty($this->buffer)) {
            return;
        }
        
        $log_content = '';
        foreach ($this->buffer as $entry) {
            $log_content .= json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n";
        }
        
        file_put_contents($this->log_file, $log_content, FILE_APPEND | LOCK_EX);
        
        $this->buffer = [];
        $this->buffer_size = 0;
    }
    
    /**
     * Generate unique session ID
     */
    private function generateSessionId() {
        return uniqid('wp_debug_', true);
    }
    
    /**
     * Get WordPress-specific context
     */
    private function getWordPressContext() {
        global $wp_query, $post;
        
        $context = [
            'is_admin' => is_admin(),
            'is_ajax' => wp_doing_ajax(),
            'current_user_id' => get_current_user_id(),
            'current_theme' => get_template(),
            'wp_version' => get_bloginfo('version'),
            'php_version' => PHP_VERSION
        ];
        
        // Add query context if available
        if (isset($wp_query) && is_object($wp_query)) {
            $context['query_vars'] = $wp_query->query_vars;
            $context['found_posts'] = $wp_query->found_posts;
            $context['post_count'] = $wp_query->post_count;
        }
        
        // Add post context if available
        if (isset($post) && is_object($post)) {
            $context['post_id'] = $post->ID;
            $context['post_type'] = $post->post_type;
            $context['post_status'] = $post->post_status;
        }
        
        // Add request context
        $context['request_uri'] = $_SERVER['REQUEST_URI'] ?? '';
        $context['request_method'] = $_SERVER['REQUEST_METHOD'] ?? '';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        return $context;
    }
    
    /**
     * Get simplified backtrace
     */
    private function getBacktrace() {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        $simplified = [];
        
        foreach ($trace as $item) {
            if (isset($item['file']) && isset($item['line'])) {
                $simplified[] = [
                    'file' => basename($item['file']),
                    'line' => $item['line'],
                    'function' => $item['function'] ?? 'unknown'
                ];
            }
        }
        
        return $simplified;
    }
    
    /**
     * Track WordPress hooks
     */
    public function trackHook($hook_name, $function_name, $priority = 10) {
        $this->debug('WordPress hook triggered', [
            'hook' => $hook_name,
            'function' => $function_name,
            'priority' => $priority,
            'filters_count' => count($GLOBALS['wp_filter'][$hook_name] ?? [])
        ]);
    }
    
    /**
     * Track database queries
     */
    public function trackQuery($query, $execution_time = null, $result_count = null) {
        $this->info('Database query executed', [
            'query' => $query,
            'execution_time' => $execution_time,
            'result_count' => $result_count,
            'total_queries' => get_num_queries()
        ]);
    }
    
    /**
     * Track user actions
     */
    public function trackUserAction($action, $context = []) {
        $this->info('User action tracked', array_merge([
            'action' => $action,
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql')
        ], $context));
    }
    
    /**
     * Track theme template usage
     */
    public function trackTemplate($template_file, $template_type = 'unknown') {
        $this->debug('Template loaded', [
            'template' => basename($template_file),
            'type' => $template_type,
            'full_path' => $template_file
        ]);
    }
    
    /**
     * Get log statistics
     */
    public function getStats() {
        if (!file_exists($this->log_file)) {
            return ['total_logs' => 0, 'errors' => 0, 'warnings' => 0];
        }
        
        $content = file_get_contents($this->log_file);
        $lines = explode("\n", trim($content));
        
        $stats = [
            'total_logs' => count($lines),
            'errors' => 0,
            'warnings' => 0,
            'info' => 0,
            'debug' => 0
        ];
        
        foreach ($lines as $line) {
            if (empty($line)) continue;
            
            $entry = json_decode($line, true);
            if (isset($entry['level'])) {
                $level = strtolower($entry['level']);
                if (isset($stats[$level])) {
                    $stats[$level]++;
                }
            }
        }
        
        return $stats;
    }
}

/**
 * Global helper function for easy logging
 */
function wp_debug_log($level, $message, $context = []) {
    WordPressDebugLogger::getInstance()->log($level, $message, $context);
}

/**
 * Quick helper functions
 */
function wp_log_info($message, $context = []) {
    WordPressDebugLogger::getInstance()->info($message, $context);
}

function wp_log_debug($message, $context = []) {
    WordPressDebugLogger::getInstance()->debug($message, $context);
}

function wp_log_error($message, $context = []) {
    WordPressDebugLogger::getInstance()->error($message, $context);
}

function wp_log_warn($message, $context = []) {
    WordPressDebugLogger::getInstance()->warn($message, $context);
}

/**
 * Auto-initialize on WordPress hooks
 */
add_action('init', function() {
    WordPressDebugLogger::getInstance()->info('WordPress debug logger initialized');
});