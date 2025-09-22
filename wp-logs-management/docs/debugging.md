# Debug System Guide

## Overview
The myproject theme includes a comprehensive debug logging system for both PHP and JavaScript.

## Usage

### PHP Debugging
```php
// Use global functions
wp_debug_log('Message');
wp_debug_log_error('Error message');
wp_debug_log_info('Info message', ['context' => 'data']);
```

### JavaScript Debugging
```javascript
// Use WPDebugLogger
WPDebugLogger.log('Message');
WPDebugLogger.error('Error message', {context: 'data'});
```

## Analyzing Logs
```bash
npm run wp-logs:analyze    # View all logs
npm run wp-logs:errors     # View errors only
npm run wp-logs:summary    # Recent activity summary
npm run wp-logs:component  # View by component
npm run wp-logs:cleanup    # Clean old logs
```

## 3-Step Workflow
1. **LOG** - Add debug statements
2. **TEST** - Analyze logs with `npm run wp-logs:analyze`
3. **CLEAN** - Remove debug code before production

## myproject Specific Debug Features
- Automatic template tracking
- User action logging
- Slow query detection
- JavaScript error catching
- WordPress die event tracking

## Log File Locations
- PHP logs: `html/wp-content/debug-logs/wp-debug-YYYY-MM-DD.log`
- JS logs: `html/wp-content/debug-logs/js-debug-YYYY-MM-DD.log`

*This document contains myproject-specific debugging guidelines.*
