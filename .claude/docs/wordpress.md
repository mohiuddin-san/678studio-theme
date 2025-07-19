# WordPress Development Guide

## 🎯 Theme Structure

### Current Implementation
- **Theme Directory**: `html/wp-content/themes/678studio/`
- **Main Template**: `index.php` (basic WordPress template)
- **Stylesheet**: `style.css` (with WordPress theme header)
- **Functions**: `functions.php` (theme support and enqueue scripts)

### WordPress Features
- Title tag support
- Post thumbnails
- Navigation menus
- Custom styling with responsive design

## 🔧 Development Environment

### Docker Services
- **WordPress**: http://localhost:8080
- **MySQL**: localhost:3306
- **phpMyAdmin**: http://localhost:8081
- **WP-CLI**: Available via `make wp cmd="..."`

### Environment Variables
```
MYSQL_ROOT_PASSWORD=password
MYSQL_DATABASE=db_local
MYSQL_USER=wp_user
MYSQL_PASSWORD=password
```

## 📝 Common WP-CLI Commands

```bash
# Theme management
make wp cmd="theme list"
make wp cmd="theme activate 678studio"

# Plugin management
make wp cmd="plugin list"
make wp cmd="plugin install plugin-name --activate"

# Database operations
make wp cmd="db export /backup/manual-backup.sql"
make wp cmd="db import /backup/backup.sql"

# User management
make wp cmd="user list"
make wp cmd="user create username email@example.com"
```

## 🎨 Theme Development

### Template Hierarchy
- `index.php` - Main template (currently active)
- `header.php` - Header template (create if needed)
- `footer.php` - Footer template (create if needed)
- `functions.php` - Theme functions

### WordPress Hooks
Common hooks used in theme development:
- `wp_enqueue_scripts` - Load styles/scripts
- `after_setup_theme` - Theme setup
- `init` - Initialize features
- `wp_head` - Head section content

### Custom Post Types & Fields
- **ACF Plugin**: Already installed and activated
- Custom fields can be added via ACF interface
- Custom post types via functions.php or plugins

## 🔍 Debugging WordPress

### Debug Constants
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Log Files
- WordPress logs: `html/wp-content/debug.log`
- Custom debug logs: `html/wp-content/debug-logs/`

### Common Issues
1. **Theme not appearing**: Check theme files structure
2. **Functions not working**: Verify functions.php syntax
3. **Styles not loading**: Check wp_enqueue_style() calls
4. **Database connection**: Verify container status

## 🚀 Performance Optimization

### Caching
- Object caching via plugins
- Page caching for production
- Database query optimization

### Asset Management
- Minification of CSS/JS
- Image optimization
- CDN integration (production)

## 🔐 Security Best Practices

### Input Sanitization
```php
$clean_input = sanitize_text_field($_POST['input']);
$clean_email = sanitize_email($_POST['email']);
```

### Output Escaping
```php
echo esc_html($user_input);
echo esc_url($url);
echo esc_attr($attribute);
```

### Nonce Verification
```php
wp_nonce_field('action_name', 'nonce_field');
wp_verify_nonce($_POST['nonce_field'], 'action_name');
```

## 📊 WordPress Specific Logging

### PHP Logging (Planned)
```php
wp_debug_log('INFO', 'User action', [
    'user_id' => get_current_user_id(),
    'action' => 'login',
    'timestamp' => current_time('mysql')
]);
```

### JavaScript Logging (Planned)
```javascript
wpDebugLogger.info('User interaction', {
    element: 'button',
    action: 'click',
    page: window.location.pathname
});
```

## 🎯 WordPress Testing

### Manual Testing Checklist
- [ ] Theme activation works
- [ ] Basic pages render correctly
- [ ] Navigation functions properly
- [ ] Forms submit correctly
- [ ] Mobile responsiveness
- [ ] Cross-browser compatibility

### Automated Testing
- WordPress unit tests
- Theme review standards
- Accessibility testing
- Performance testing

---

*For implementation details of the debug system, see `debugging.md`*