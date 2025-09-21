/**
 * WordPress JavaScript Debug Logger
 * Provides comprehensive client-side logging for WordPress theme development
 * Enables Claude Code to autonomously debug JavaScript functionality
 */

class WordPressJSLogger {
    constructor() {
        this.logs = [];
        this.sessionId = this.generateSessionId();
        this.maxBufferSize = 50;
        this.flushInterval = 5000; // 5 seconds
        this.isFlushingScheduled = false;
        
        // Configuration
        this.config = {
            enableStorage: true,
            enableConsole: false, // Disabled to prevent console pollution
            enableAjax: true,
            logLevel: 'DEBUG'
        };
        
        this.init();
    }
    
    init() {
        // Global error handler
        window.addEventListener('error', (e) => {
            this.error('JavaScript Error', {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                stack: e.error?.stack,
                timestamp: new Date().toISOString()
            });
        });
        
        // Unhandled promise rejections
        window.addEventListener('unhandledrejection', (e) => {
            this.error('Unhandled Promise Rejection', {
                reason: e.reason,
                stack: e.reason?.stack,
                timestamp: new Date().toISOString()
            });
        });
        
        // WordPress specific events
        if (typeof jQuery !== 'undefined') {
            jQuery(document).ready(() => {
                this.info('jQuery ready', {
                    jQueryVersion: jQuery.fn.jquery,
                    timestamp: new Date().toISOString()
                });
            });
        }
        
        // Track page load performance
        window.addEventListener('load', () => {
            this.trackPageLoad();
        });
        
        // Auto-flush interval
        setInterval(() => {
            this.flush();
        }, this.flushInterval);
        
        // Flush on page unload
        window.addEventListener('beforeunload', () => {
            this.flush();
        });
        
        this.info('WordPress JS Logger initialized', {
            sessionId: this.sessionId,
            userAgent: navigator.userAgent,
            url: window.location.href,
            timestamp: new Date().toISOString()
        });
    }
    
    /**
     * Main logging method
     * @param {string} level Log level (DEBUG, INFO, WARN, ERROR)
     * @param {string} message Log message
     * @param {object} context Additional context data
     */
    log(level, message, context = {}) {
        const timestamp = new Date().toISOString();
        const logEntry = {
            timestamp: timestamp,
            level: level.toUpperCase(),
            sessionId: this.sessionId,
            message: message,
            context: context,
            url: window.location.href,
            userAgent: navigator.userAgent,
            viewport: {
                width: window.innerWidth,
                height: window.innerHeight
            },
            performance: this.getPerformanceData(),
            stack: this.getStackTrace()
        };
        
        this.logs.push(logEntry);
        
        // Optional console output for development
        if (this.config.enableConsole) {
            console.log(`[${level}] ${message}`, context);
        }
        
        // Auto-flush if buffer is full or error level
        if (this.logs.length >= this.maxBufferSize || level === 'ERROR') {
            this.flush();
        }
        
        // Store in localStorage as backup
        if (this.config.enableStorage) {
            this.saveToLocalStorage(logEntry);
        }
    }
    
    /**
     * Info level logging
     */
    info(message, context = {}) {
        this.log('INFO', message, context);
    }
    
    /**
     * Debug level logging
     */
    debug(message, context = {}) {
        this.log('DEBUG', message, context);
    }
    
    /**
     * Error level logging
     */
    error(message, context = {}) {
        this.log('ERROR', message, context);
    }
    
    /**
     * Warning level logging
     */
    warn(message, context = {}) {
        this.log('WARN', message, context);
    }
    
    /**
     * Track user actions
     */
    trackUserAction(action, element, context = {}) {
        this.info('User Action', {
            action: action,
            element: {
                tagName: element.tagName,
                id: element.id,
                className: element.className,
                innerHTML: element.innerHTML?.substring(0, 100) // Truncate for performance
            },
            ...context
        });
    }
    
    /**
     * Track AJAX requests
     */
    trackAjax(url, method, data, response, duration) {
        this.info('AJAX Request', {
            url: url,
            method: method,
            data: data,
            response: response,
            duration: duration,
            timestamp: new Date().toISOString()
        });
    }
    
    /**
     * Track page load performance
     */
    trackPageLoad() {
        if (performance.timing) {
            const timing = performance.timing;
            const loadTime = timing.loadEventEnd - timing.navigationStart;
            const domReady = timing.domContentLoadedEventEnd - timing.navigationStart;
            
            this.info('Page Load Performance', {
                loadTime: loadTime,
                domReady: domReady,
                dnsLookup: timing.domainLookupEnd - timing.domainLookupStart,
                serverResponse: timing.responseEnd - timing.requestStart,
                domProcessing: timing.domComplete - timing.domLoading,
                timestamp: new Date().toISOString()
            });
        }
    }
    
    /**
     * Track WordPress specific events
     */
    trackWordPressEvent(event, data = {}) {
        this.info('WordPress Event', {
            event: event,
            data: data,
            timestamp: new Date().toISOString()
        });
    }
    
    /**
     * Flush logs to server
     */
    async flush() {
        if (this.logs.length === 0 || this.isFlushingScheduled) {
            return;
        }
        
        this.isFlushingScheduled = true;
        
        try {
            if (this.config.enableAjax && typeof wpDebugAjax !== 'undefined') {
                console.log('[DEBUG-LOGGER] wpDebugAjax:', wpDebugAjax);
                console.log('[DEBUG-LOGGER] Flushing', this.logs.length, 'logs');

                const formData = new FormData();
                formData.append('action', 'wp_debug_log_js');
                formData.append('nonce', wpDebugAjax.nonce);
                formData.append('logs', JSON.stringify(this.logs));
                
                const response = await fetch(wpDebugAjax.ajaxurl, {
                    method: 'POST',
                    body: formData
                });
                
                if (response.ok) {
                    this.debug('Logs flushed to server', {
                        count: this.logs.length,
                        timestamp: new Date().toISOString()
                    });
                    this.logs = []; // Clear buffer after successful flush
                } else {
                    console.error('[DEBUG-LOGGER] Failed to flush logs to server:', response.status, response.statusText);
                    this.error('Failed to flush logs to server', {
                        status: response.status,
                        statusText: response.statusText
                    });
                }
            }
        } catch (error) {
            console.error('[DEBUG-LOGGER] Error flushing logs:', error);
            this.error('Error flushing logs', {
                error: error.message,
                stack: error.stack
            });
        }
        
        this.isFlushingScheduled = false;
    }
    
    /**
     * Get performance data
     */
    getPerformanceData() {
        if (performance.now) {
            return {
                now: performance.now(),
                memory: performance.memory ? {
                    usedJSHeapSize: performance.memory.usedJSHeapSize,
                    totalJSHeapSize: performance.memory.totalJSHeapSize
                } : null
            };
        }
        return null;
    }
    
    /**
     * Get stack trace
     */
    getStackTrace() {
        try {
            throw new Error();
        } catch (e) {
            return e.stack?.split('\n').slice(0, 5) || [];
        }
    }
    
    /**
     * Generate unique session ID
     */
    generateSessionId() {
        return 'js_debug_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }
    
    /**
     * Save to localStorage as backup
     */
    saveToLocalStorage(logEntry) {
        try {
            const storageKey = 'wp_debug_logs_' + new Date().toISOString().split('T')[0];
            let existingLogs = JSON.parse(localStorage.getItem(storageKey) || '[]');
            
            existingLogs.push(logEntry);
            
            // Keep only last 100 logs per day
            if (existingLogs.length > 100) {
                existingLogs = existingLogs.slice(-100);
            }
            
            localStorage.setItem(storageKey, JSON.stringify(existingLogs));
        } catch (error) {
            // localStorage might be full or disabled
            console.warn('Could not save to localStorage:', error);
        }
    }
    
    /**
     * Get logs from localStorage
     */
    getStoredLogs(date = null) {
        try {
            const storageKey = 'wp_debug_logs_' + (date || new Date().toISOString().split('T')[0]);
            return JSON.parse(localStorage.getItem(storageKey) || '[]');
        } catch (error) {
            return [];
        }
    }
    
    /**
     * Clear stored logs
     */
    clearStoredLogs() {
        try {
            const keys = Object.keys(localStorage);
            keys.forEach(key => {
                if (key.startsWith('wp_debug_logs_')) {
                    localStorage.removeItem(key);
                }
            });
        } catch (error) {
            console.warn('Could not clear localStorage:', error);
        }
    }
    
    /**
     * Get current log statistics
     */
    getStats() {
        const stats = {
            total: this.logs.length,
            error: 0,
            warn: 0,
            info: 0,
            debug: 0
        };
        
        this.logs.forEach(log => {
            const level = log.level.toLowerCase();
            if (stats[level] !== undefined) {
                stats[level]++;
            }
        });
        
        return stats;
    }
}

// Global instance
window.wpDebugLogger = new WordPressJSLogger();

// jQuery integration if available
if (typeof jQuery !== 'undefined') {
    jQuery(document).ready(function($) {
        // Track clicks on important elements
        $('a, button, input[type="submit"]').on('click', function() {
            wpDebugLogger.trackUserAction('click', this, {
                href: this.href,
                type: this.type,
                value: this.value
            });
        });
        
        // Track form submissions
        $('form').on('submit', function() {
            wpDebugLogger.trackUserAction('form_submit', this, {
                action: this.action,
                method: this.method,
                formData: $(this).serialize()
            });
        });
        
        // Track AJAX requests
        $(document).ajaxSend(function(event, xhr, settings) {
            wpDebugLogger.trackAjax(settings.url, settings.type || 'GET', settings.data, null, null);
        });
        
        $(document).ajaxComplete(function(event, xhr, settings) {
            wpDebugLogger.trackAjax(settings.url, settings.type || 'GET', settings.data, xhr.responseText, null);
        });
        
        $(document).ajaxError(function(event, xhr, settings, error) {
            wpDebugLogger.error('AJAX Error', {
                url: settings.url,
                method: settings.type || 'GET',
                error: error,
                status: xhr.status,
                responseText: xhr.responseText
            });
        });
    });
}

// WordPress admin integration
if (typeof wp !== 'undefined' && wp.hooks) {
    wp.hooks.addAction('wp.editor.save', 'wp-debug-logger', function() {
        wpDebugLogger.trackWordPressEvent('editor_save');
    });
}

// Export for use in other scripts
window.wpLog = {
    info: (message, context) => wpDebugLogger.info(message, context),
    debug: (message, context) => wpDebugLogger.debug(message, context),
    error: (message, context) => wpDebugLogger.error(message, context),
    warn: (message, context) => wpDebugLogger.warn(message, context),
    trackUser: (action, element, context) => wpDebugLogger.trackUserAction(action, element, context),
    trackWP: (event, data) => wpDebugLogger.trackWordPressEvent(event, data)
};

// Uppercase alias for compatibility with publication-modal.js
window.WPDebugLogger = {
    log: (message, context) => wpDebugLogger.debug(message, context),
    debug: (message, context) => wpDebugLogger.debug(message, context),
    error: (message, context) => wpDebugLogger.error(message, context),
    warn: (message, context) => wpDebugLogger.warn(message, context),
    info: (message, context) => wpDebugLogger.info(message, context)
};