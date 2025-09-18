# GitHub Push Commands

## Standard Workflow

### Commit and Push to jun branch
```bash
git add .
git commit -m "feat: describe your changes"
git push origin jun
```

### Push theme-only to junichy-dev branch
```bash
git checkout junichy-dev
# Copy theme files only
cp -r html/wp-content/themes/678studio/* .
git add .
git commit -m "Update theme files"
git push origin junichy-dev
git checkout jun
```

## Branch Management

- **jun**: Full development environment (Docker + WordPress + Theme)
- **junichy-dev**: Theme files only (for deployment)

## Deployment Notes

- Always test locally before pushing
- Use meaningful commit messages
- Keep theme branch clean (no node_modules, no development files)