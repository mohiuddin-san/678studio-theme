# 678 Studio WordPress Theme

A modern WordPress theme for 678 Studio photography website.

## 🎯 Features

- **Responsive Design**: Mobile-first approach with BEM methodology
- **Modern SCSS**: Gulp-based compilation with BrowserSync
- **Debug System**: Comprehensive PHP/JS logging for development
- **WordPress Integration**: Full WordPress theme support
- **Performance Optimized**: Lightweight and fast loading

## 🛠️ Development Setup

### Prerequisites

- Node.js (v14 or higher)
- npm or yarn
- WordPress development environment

### Installation

1. Clone this repository into your WordPress themes directory:
   ```bash
   git clone <repository-url> wp-content/themes/678studio
   ```

2. Navigate to theme directory:
   ```bash
   cd wp-content/themes/678studio
   ```

3. Install dependencies:
   ```bash
   npm install
   ```

4. Start development server:
   ```bash
   npm run dev
   ```

## 📝 Development Commands

```bash
# Development mode (compile + watch + BrowserSync)
npm run dev

# Production build (minified)
npm run build

# Watch only (no BrowserSync)
npm run watch

# SCSS compile only
npm run sass

# WordPress Debug System
npm run wp-logs:analyze      # Analyze all logs
npm run wp-logs:errors       # Show errors only
npm run wp-logs:summary      # Last hour summary
npm run wp-logs:cleanup      # Clean old logs
```

## 🎨 Theme Structure

```
678studio/
├── assets/
│   ├── scss/           # SCSS source files
│   │   ├── base/       # Base styles, mixins, variables
│   │   ├── components/ # Component styles
│   │   └── style.scss  # Main SCSS entry point
│   └── js/
│       └── debug-logger.js
├── lib/
│   └── debug-logger.php
├── dist/               # Compiled assets (auto-generated)
├── functions.php       # WordPress theme functions
├── home.php           # Home page template
├── index.php          # Fallback template
├── style.css          # Main stylesheet (auto-generated)
└── package.json       # Dependencies and scripts
```

## 🎯 Key Features

### SCSS Development
- **Responsive Functions**: `fs()`, `fsm()`, `mq()` mixins
- **BEM Methodology**: Structured component styling
- **Auto-compilation**: Live reload with BrowserSync
- **Production Build**: Minified and optimized output

### WordPress Integration
- **Theme Support**: Title tag, post thumbnails, menus
- **Custom Templates**: Home page, fallback templates
- **ACF Ready**: Advanced Custom Fields integration
- **Security**: Input sanitization and nonce verification

### Debug System
- **PHP Logging**: Comprehensive server-side logging
- **JavaScript Logging**: Client-side error tracking
- **WordPress Hooks**: Template and query tracking
- **Performance Monitoring**: Memory usage and query analysis

## 🔧 Configuration

### SCSS Variables
Located in `assets/scss/base/_valiables.scss`:
- Color scheme
- Typography settings
- Breakpoints
- Spacing units

### WordPress Functions
Located in `functions.php`:
- Theme setup
- Script/style enqueuing
- Debug system integration
- Custom hooks

## 🚀 Deployment

1. Build production assets:
   ```bash
   npm run build
   ```

2. Upload theme files to production server
3. Activate theme in WordPress admin

## 📊 Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- IE11 (limited support)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and build
5. Submit a pull request

## 📄 License

This theme is developed for 678 Studio. All rights reserved.

## 🏗️ Built With

- **WordPress**: CMS framework
- **SCSS**: CSS preprocessor
- **Gulp**: Build system
- **BrowserSync**: Live reload
- **PHP**: Server-side logic
- **JavaScript**: Client-side interactions

## 📬 Developer Notes

### For mohi-san

**Store Detail Page Development Status:**

Currently, the store detail functionality is implemented using a **test page template** rather than the standard `single-store.php` approach:

- **Test URL**: `http://localhost:8080/store-detail-test/`
- **Template**: `page-store-detail.php` (Page Template: "Store Detail Test")
- **Reason**: Using a page template allows for immediate browser testing and design iteration without requiring custom post types or database setup

**Current Implementation:**
```
✅ Store Hero Section (image + title + category tag)
✅ Breadcrumb Navigation (with SVG underlines)  
✅ Basic Information Section (with underline-store.svg)
✅ Responsive SCSS styling
✅ Header navigation integration
```

**Next Steps:**
1. Complete additional sections (gallery, access, staff, etc.)
2. Convert to proper `single-store.php` template with dynamic data
3. Set up custom post type for stores if needed
4. Migrate content from test page to production template

**Files Created:**
- `page-store-detail.php` - Test template for development
- `single-store.php` - Ready for production implementation  
- `archive-store.php` - Store listing page
- `page-stores.php` - Store listing test page
- `assets/scss/pages/_single-store.scss` - Store-specific styling

The test page approach allows for faster development and design validation before implementing the final dynamic solution.

---

*For detailed development documentation, see the `.claude/docs/` directory in the project root.*