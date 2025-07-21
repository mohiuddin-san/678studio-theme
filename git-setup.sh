#!/bin/bash
# 678 Studio Theme - Git Setup Script

echo "🚀 678 Studio Theme Git Setup"
echo "Repository: https://github.com/mohiuddin-san/678studio-theme.git"
echo "Branch: junichy-dev"
echo ""

# Initialize git repository if not exists
if [ ! -d ".git" ]; then
    echo "📦 Initializing git repository..."
    git init
    echo "✅ Git repository initialized"
else
    echo "📦 Git repository already exists"
fi

# Add remote origin
echo "🔗 Adding remote origin..."
git remote remove origin 2>/dev/null || true
git remote add origin https://github.com/mohiuddin-san/678studio-theme.git
echo "✅ Remote origin added"

# Create and switch to junichy-dev branch
echo "🌟 Creating junichy-dev branch..."
git checkout -b junichy-dev 2>/dev/null || git checkout junichy-dev
echo "✅ Switched to junichy-dev branch"

# Stage all files
echo "📝 Staging files..."
git add .
echo "✅ Files staged"

# Show status
echo "📊 Git status:"
git status --short

# Commit files
echo "💾 Committing files..."
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

echo "✅ Files committed successfully"

# Push to remote
echo "🚀 Pushing to GitHub..."
git push -u origin junichy-dev

echo ""
echo "✅ 678 Studio theme successfully pushed to GitHub!"
echo "🔗 Repository: https://github.com/mohiuddin-san/678studio-theme.git"
echo "🌟 Branch: junichy-dev"