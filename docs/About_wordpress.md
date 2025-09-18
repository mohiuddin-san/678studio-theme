# WordPress Development Notes

## Theme Structure

The 678studio theme follows WordPress best practices:

### Template Hierarchy
- `home.php` - Homepage template
- `page-*.php` - Custom page templates
- `single-*.php` - Custom post type templates
- `archive-*.php` - Archive templates

### Custom Post Types
- **Store**: Photography studio locations
- **Media Achievements**: Portfolio items

### Key Features
- ACF (Advanced Custom Fields) integration
- Custom SCSS compilation with Gulp
- BrowserSync for development
- WordPress debug logging system
- Responsive design with mobile-first approach

### Development Workflow
1. Start Docker environment: `make up`
2. Start SCSS compilation: `cd html/wp-content/themes/678studio && npm run dev`
3. Access site at http://localhost:8080
4. BrowserSync at http://localhost:3000

### Important Files
- `functions.php` - Theme functionality and hooks
- `style.css` - Compiled CSS (don't edit directly)
- `assets/scss/style.scss` - Main SCSS file
- `gulpfile.js` - Build configuration

### Custom Components
All components are in `template-parts/` following WordPress standards:
- `components/` - Reusable UI components
- `sections/` - Page-specific sections
- `header/` - Header-related templates