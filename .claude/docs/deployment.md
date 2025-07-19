# Deployment Guide

## 🎯 Current Status

**⚠️ Template State**: All deployment scripts are currently in template state and require production server configuration before use.

## 🏗️ Deployment Architecture

### SSH-Based Deployment
- **Authentication**: SSH key-based authentication
- **Automation**: Bash scripts for deployment tasks
- **Backup**: Automatic backups before deployment
- **Rollback**: Capability to revert changes

### Key Scripts
- `scripts/ssh-setup.sh` - SSH authentication setup
- `scripts/deploy-to-prod.sh` - Full production deployment
- `scripts/deploy-single-file.sh` - Single file deployment
- `scripts/sync-from-prod.sh` - Production to local sync

## 📋 Required Configuration

### 1. SSH Configuration
**File**: `~/.ssh/config`
```
Host 678studio
    HostName your-server.com
    Port 22
    User your-username
    IdentityFile ~/.ssh/678studio_rsa
```

### 2. SSH Key Generation
```bash
# Generate SSH key pair
ssh-keygen -t rsa -b 4096 -f ~/.ssh/678studio_rsa -C "678studio@your-domain.com"

# Copy public key to server
ssh-copy-id -i ~/.ssh/678studio_rsa.pub your-username@your-server.com
```

### 3. Script Configuration
Update the following variables in deployment scripts:

```bash
# Configuration variables to update
REMOTE_HOST="678studio"                    # SSH host alias
REMOTE_PATH="/path/to/678studio/wordpress" # Production WordPress path
LOCAL_URL="http://localhost:8080"          # Local development URL
PROD_URL="https://678studio.com"           # Production domain
SSH_PORT="22"                              # SSH port
SSH_KEY="$HOME/.ssh/678studio_rsa"         # SSH key path
```

## 🚀 Deployment Commands

### Environment Management
```bash
# Setup SSH authentication
make ssh-setup

# Deploy local changes to production
make deploy

# Deploy single file
make deploy-file FILE=wp-content/themes/678studio/style.css

# Sync production to local
make sync
```

### Manual Deployment Steps
```bash
# 1. Test local environment
make up
make status

# 2. Build production assets
cd html/wp-content/themes/678studio
npm run build

# 3. Backup database
make db-backup

# 4. Deploy to production
make deploy
```

## 🔧 Deployment Workflow

### Pre-Deployment Checklist
- [ ] Local environment fully tested
- [ ] All tests passing
- [ ] Database backup created
- [ ] Production assets built
- [ ] SSH connection verified

### Deployment Process
1. **Backup**: Create production backup
2. **Upload**: Transfer files to production
3. **Database**: Update database if needed
4. **Verify**: Check production functionality
5. **Rollback**: If issues detected

### Post-Deployment Verification
- [ ] Site loads correctly
- [ ] All functionality works
- [ ] Database connection stable
- [ ] SSL certificate valid
- [ ] Performance acceptable

## 📊 Monitoring & Maintenance

### Log Files
```bash
# Deployment logs
logs/deployment-YYYY-MM-DD.log

# Error logs
logs/error-YYYY-MM-DD.log

# Access logs
logs/access-YYYY-MM-DD.log
```

### Performance Monitoring
- Page load times
- Database query performance
- Error rates
- Uptime monitoring

## 🔒 Security Considerations

### File Permissions
```bash
# WordPress files
find /path/to/wordpress/ -type f -exec chmod 644 {} \;
find /path/to/wordpress/ -type d -exec chmod 755 {} \;

# wp-config.php
chmod 600 wp-config.php
```

### Security Headers
```apache
# .htaccess security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
```

### Database Security
- Regular backups
- Strong passwords
- Limited database user privileges
- Database prefix randomization

## 🔄 Rollback Procedures

### Automatic Rollback
```bash
# If deployment fails
make rollback

# Restore from backup
make restore-backup BACKUP_DATE=2025-07-16
```

### Manual Rollback
1. **Stop services**: Prevent user access
2. **Restore files**: From backup location
3. **Restore database**: From SQL backup
4. **Verify**: Check functionality
5. **Resume services**: Allow user access

## 🎯 Production Environment Setup

### Server Requirements
- **OS**: Ubuntu 20.04+ or CentOS 8+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **PHP**: 8.0+ with required extensions
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **SSL**: Valid SSL certificate

### WordPress Configuration
```php
// Production wp-config.php settings
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('DISALLOW_FILE_EDIT', true);
define('AUTOMATIC_UPDATER_DISABLED', true);
```

### Caching Setup
- **Object Cache**: Redis or Memcached
- **Page Cache**: W3 Total Cache or WP Rocket
- **CDN**: CloudFlare or AWS CloudFront
- **Database**: Query optimization

## 🔧 Troubleshooting

### Common Issues
1. **SSH connection failed**
   - Check SSH key permissions
   - Verify host configuration
   - Test manual SSH connection

2. **File permission errors**
   - Check ownership and permissions
   - Verify web server user
   - Update file permissions

3. **Database connection issues**
   - Check database credentials
   - Verify database server status
   - Test database connectivity

4. **Performance issues**
   - Check server resources
   - Monitor database queries
   - Optimize caching settings

### Debug Commands
```bash
# Test SSH connection
ssh -v 678studio

# Check file permissions
ls -la /path/to/wordpress/

# Test database connection
mysql -u username -p -h hostname

# Check server resources
htop
df -h
```

## 📈 Performance Optimization

### Production Optimizations
- **Gzip compression**: Enable server-side compression
- **Browser caching**: Set appropriate cache headers
- **Image optimization**: Compress and optimize images
- **Database optimization**: Regular maintenance and optimization

### Monitoring Tools
- **Uptime**: UptimeRobot or Pingdom
- **Performance**: GTmetrix or PageSpeed Insights
- **Error tracking**: Sentry or Rollbar
- **Analytics**: Google Analytics or similar

---

*⚠️ Remember: All deployment scripts require proper configuration before first use. Test thoroughly in a staging environment before production deployment.*