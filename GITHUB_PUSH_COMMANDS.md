# 678 Studio Theme - GitHub Push Commands

## 🎯 Repository Information
- **Repository**: https://github.com/mohiuddin-san/678studio-theme.git
- **Branch**: junichy-dev (always push to this branch)

## 🚀 Manual Push Commands

Execute these commands in terminal:

```bash
# Navigate to theme directory
cd html/wp-content/themes/678studio

# Make setup script executable
chmod +x git-setup.sh

# Run setup script
./git-setup.sh
```

## 📝 Alternative Manual Commands

If you prefer to run commands manually:

```bash
# Navigate to theme directory
cd html/wp-content/themes/678studio

# Initialize git repository (if not exists)
git init

# Add remote origin
git remote add origin https://github.com/mohiuddin-san/678studio-theme.git

# Create and switch to junichy-dev branch
git checkout -b junichy-dev

# Stage all files
git add .

# Commit with message
git commit -m "feat: Add 678 Studio WordPress theme with home.php and debug system

- Add home.php template for photography studio homepage
- Add index.php fallback template
- Implement WordPress debug system (PHP/JS logging)
- Add SCSS compilation with Gulp and BrowserSync
- Add responsive design with custom mixins
- Add WordPress theme functions and hooks
- Add comprehensive README documentation

🤖 Generated with Claude Code (https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>"

# Push to remote
git push -u origin junichy-dev
```

## 🔄 Future Updates

For future updates, always use:

```bash
cd html/wp-content/themes/678studio
git add .
git commit -m "Your commit message"
git push origin junichy-dev
```

## 📊 Files to be Committed

### ✅ Included Files:
- `functions.php` - WordPress theme functions
- `home.php` - Home page template
- `index.php` - Fallback template
- `package.json` - Dependencies and scripts
- `gulpfile.js` - Build system
- `README.md` - Documentation
- `assets/scss/` - SCSS source files
- `lib/debug-logger.php` - PHP debug system
- `assets/js/debug-logger.js` - JS debug system

### ❌ Excluded Files (.gitignore):
- `node_modules/`
- `package-lock.json`
- `dist/`
- `style.css` and `style.css.map`
- `*.log`
- Development files

## 🎉 Ready to Push!

The theme is ready for GitHub push. Execute the commands above to upload to:
**https://github.com/mohiuddin-san/678studio-theme.git** (junichy-dev branch)