# myproject WordPress Theme - Documentation Overview

## 📁 Documentation Structure

This directory contains comprehensive documentation for the myproject WordPress theme development project.

### 📚 Available Documentation

#### Core Development Guides
- **[wordpress.md](wordpress.md)** - WordPress development guide
  - Theme structure and development workflow
  - WP-CLI commands and database operations
  - WordPress hooks and security best practices
  - Manual testing checklist

- **[debugging.md](debugging.md)** - Debug system specifications
  - Comprehensive PHP/JS logging system
  - 3-step development workflow (LOG → TEST → CLEAN)
  - Log analysis and recommendations

- **[scss.md](scss.md)** - SCSS development guide
  - Gulp-based compilation system with BrowserSync
  - Responsive functions and mixins
  - Component structure and BEM methodology
  - Performance optimization

- **[deployment.md](deployment.md)** - Deployment procedures
  - SSH-based automation scripts
  - Production environment setup
  - Security considerations and rollback procedures
  - Monitoring and maintenance

## 🎯 How to Use This Documentation

### For Development Tasks
1. **Always start with this README** to understand available resources
2. **Reference the appropriate specialized guide** for your task
3. **Follow the established coding conventions** in each document
4. **Use the pre-task checklist** (see main CLAUDE.md)

### For Claude Code Integration
This modular documentation structure is designed to work with Claude Code:
- **Cascading CLAUDE.md files** for hierarchical configuration
- **Automatic reference resolution** when working with specific components
- **Scalable documentation** that grows with the project

## 🚨 Critical Development Rules

### Before Starting Any Task
Always reference the appropriate documentation:
- **WordPress features** → `wordpress.md`
- **Debugging/testing** → `debugging.md`
- **SCSS/styling** → `scss.md`
- **Deployment** → `deployment.md`

### Development Workflow
1. **Read relevant documentation** before coding
2. **Follow 3-step process**: LOG → TEST → CLEAN
3. **Use WordPress debug system** (no console.log/var_dump)
4. **Verify 0 errors** before completion

## 📋 Pre-Task Checklist Reference

When working on myproject project tasks:

1. **[ ] Read .claude/docs/README.md** (this file)
2. **[ ] Review relevant technical specification**
3. **[ ] Check coding conventions** in the applicable guide
4. **[ ] Verify existing codebase consistency** with established patterns

## 🔧 Current Project Status

### ✅ Completed Components
- Docker development environment
- SCSS compilation with Gulp/BrowserSync
- WordPress theme structure
- Debug system implementation (PHP/JS logging)
- Log analysis scripts

### 🔄 Template State Components
- Deployment scripts (require production server configuration)
- Production monitoring setup

## 🎯 Quick Reference Links

### Essential Commands
```bash
# Development
make up && cd html/wp-content/themes/myproject && npm run dev

# Debug Analysis
npm run wp-logs:analyze
npm run wp-logs:errors

# Production Build
npm run build
```

### Access URLs
- **WordPress**: http://localhost:8080
- **BrowserSync**: http://localhost:3000
- **phpMyAdmin**: http://localhost:8081

---

*This documentation structure ensures comprehensive coverage while maintaining clarity and accessibility for both human developers and Claude Code autonomous operation.*