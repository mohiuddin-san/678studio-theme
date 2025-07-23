# 678 Studio WordPress Theme - Core Configuration

## 🚀 Quick Reference

### Essential Commands
```bash
# Environment
make up              # Start development
make down            # Stop environment
make restart         # Restart environment

# Development
cd html/wp-content/themes/678studio
npm run dev          # Start SCSS compilation + BrowserSync
npm run build        # Production build

# WordPress Debug System
npm run wp-logs:analyze      # Analyze all logs
npm run wp-logs:errors       # Show errors only
npm run wp-logs:summary      # Last hour summary
```

### Access URLs
- **WordPress**: http://localhost:8080
- **BrowserSync**: http://localhost:3000
- **phpMyAdmin**: http://localhost:8081

## 📋 タスク開始前のチェックリスト

**全てのタスクを開始する前に、必ず以下を確認してください：**

1. **[ ] .claude/docs/README.md を確認**
2. **[ ] 関連する技術仕様書を確認**
   - WordPress機能 → `.claude/docs/wordpress.md`
   - デバッグ/テスト → `.claude/docs/debugging.md`
   - SCSS/スタイル → `.claude/docs/scss.md`
   - デプロイメント → `.claude/docs/deployment.md`
3. **[ ] コーディング規約を確認**
4. **[ ] 既存のコードベースとの整合性を確認**

## 🎯 Project Overview

678 Studio WordPress theme development environment with:
- **Docker**: WordPress 6.4 + MySQL 8.0 + PHP 8.2
- **SCSS**: Gulp-based compilation with BrowserSync
- **Debug System**: Comprehensive PHP/JS logging
- **Deployment**: SSH-based automation (template state)

## 📁 Key Directories

- `html/wp-content/themes/678studio/` - Main theme
- `html/wp-content/themes/678studio/assets/scss/` - SCSS source
- `html/wp-content/themes/678studio/assets/js/` - JavaScript
- `scripts/` - Deployment automation
- `.claude/docs/` - Detailed documentation

## 🚨 Critical Rules

### Development Workflow
**ALWAYS follow 3-step process:**
1. **LOG** - Add debugging logs
2. **TEST** - Verify with `npm run wp-logs:analyze`
3. **CLEAN** - Remove debug logs before production

### Prohibited Actions
❌ **NEVER use console.log, var_dump, print_r, echo for debugging**
❌ **NEVER commit debug logs to production**
❌ **NEVER skip error analysis**

### Required Actions
✅ **ALWAYS use WordPress debug system**
✅ **ALWAYS verify 0 errors before completion**
✅ **ALWAYS clean debug logs after testing**
✅ **ALWAYS reference .claude/docs/ before starting tasks**

## 🔧 Current Status

- ✅ Docker environment configured
- ✅ SCSS compilation working
- ✅ WordPress theme active
- ✅ ACF plugin installed
- ✅ Debug system implemented
- 🔄 Deployment scripts (template state)

## 📚 Detailed Documentation

**必須**: タスク実行前に関連ドキュメントを参照してください

See `.claude/docs/` for comprehensive guides:
- `README.md` - Documentation overview and checklist
- `wordpress.md` - WordPress development
- `debugging.md` - Debug system details
- `scss.md` - SCSS development
- `deployment.md` - Deployment procedures

---

*This is a streamlined core configuration. Detailed documentation is organized in separate files for better maintainability.*