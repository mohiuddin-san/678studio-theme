# Deployment Guide

## Overview
This guide covers deployment procedures for the myproject theme.

## Pre-deployment Checklist
- [ ] Run production build: `npm run build`
- [ ] Test thoroughly in development environment
- [ ] Remove all debug code and console logs
- [ ] Verify no errors: `npm run wp-logs:analyze`
- [ ] Check SCSS compilation: `npm run sass`
- [ ] Test responsive design with fs()/fsm() functions
- [ ] Create database backup: `make db-backup`
- [ ] Verify all WordPress functionality works
- [ ] Test theme activation and customization

## myproject Specific Deployment Steps
1. **Build Phase**
   ```bash
   cd html/wp-content/themes/myproject
   npm run build
   ```

2. **Quality Assurance**
   - Test all responsive breakpoints
   - Verify fs() and fsm() functions work correctly
   - Check BrowserSync functionality
   - Validate SCSS compilation
   - Run debug log analysis

3. **Production Deployment**
   - Upload theme files to production server
   - Activate myproject theme
   - Update WordPress configuration if needed
   - Test functionality on production

4. **Post-deployment Verification**
   - Verify site loads correctly
   - Test responsive design
   - Check all template files work
   - Monitor error logs

## Rollback Procedure
1. Keep previous myproject theme version backup
2. Database backup before deployment
3. Quick rollback process:
   ```bash
   # Restore previous theme version
   # Restore database if needed
   # Clear any caches
   ```

## Environment-Specific Notes
- **Development**: Use `npm run dev` for live reloading
- **Staging**: Use `npm run build` for production assets
- **Production**: Ensure debug mode is disabled

*This document contains myproject-specific deployment procedures.*
