# WordPress Development Guide

## Overview
This guide covers WordPress-specific development practices for the myproject theme.

## Theme Structure
- `functions.php` - Core theme functionality
- `index.php` - Main template file
- `style.css` - Theme information and styles

## Development Workflow
1. Use WordPress hooks and filters appropriately
2. Follow WordPress coding standards
3. Test with debug mode enabled

## WP-CLI Commands
```bash
# Common commands
make wp cmd="plugin list"
make wp cmd="theme activate myproject"
make wp cmd="cache flush"
```

## Security Best Practices
- Always escape output: `esc_html()`, `esc_attr()`, `esc_url()`
- Validate and sanitize input
- Use nonces for form submissions
- Check user capabilities

## Custom Functions Location
All custom functions for myproject are located in:
- `functions.php` - Main theme functions
- `lib/debug-logger.php` - Debug system integration

## Theme-Specific Features
- Debug logging system integrated
- SCSS compilation with BrowserSync
- Responsive design with fs() and fsm() functions
- Mobile-first approach with mq() mixins

*This document contains myproject-specific WordPress development guidelines.*
