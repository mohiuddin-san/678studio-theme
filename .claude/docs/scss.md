# SCSS Development Guide

## 🎯 Setup Overview

678 Studio uses a Gulp-based SCSS compilation system with BrowserSync for live reload and hot module replacement.

### Directory Structure
```
html/wp-content/themes/678studio/
├── assets/
│   └── scss/
│       ├── base/
│       │   ├── _valiables.scss    # Variables (note: valiables, not variables)
│       │   ├── _mixins.scss       # Responsive functions fs(), mq()
│       │   └── _reset.scss        # CSS Reset
│       ├── components/
│       │   ├── _common.scss       # Common components
│       │   └── _header.scss       # Header styles
│       └── style.scss             # Main entry point
├── dist/
│   └── css/
│       ├── style.css              # Compiled CSS
│       └── style.css.map          # Source maps
├── gulpfile.js                    # Gulp configuration
├── package.json                   # Dependencies
└── style.css                      # WordPress theme CSS (auto-generated)
```

## 🚀 Development Commands

### Basic Commands
```bash
# Development mode (compile + watch + BrowserSync)
npm run dev

# Production build (minified)
npm run build

# Watch only (no BrowserSync)
npm run watch

# SCSS compile only
npm run sass
```

### Access URLs
- **WordPress**: http://localhost:8080
- **BrowserSync**: http://localhost:3000 (live reload)

## 📝 SCSS Naming Convention

### Variables and Mixins
```scss
// Import with namespace
@use '../base/valiables' as v;    // v.$main-color
@use '../base/mixins' as m;       // m.fs(), m.mq()

// Usage example
.my-element {
    color: v.$brand-red;          // Brand color
    font-size: m.fs(24);          // Responsive font size
    @include m.mq(md, max) {      // Media query
        font-size: m.fsm(18);     // Mobile font size
    }
}
```

### Color Variables
```scss
// Available in v.$
$main-color: #333333;
$brand-red: #e74c3c;
$brand-blue: #3498db;
$brand-green: #2ecc71;
$text-color: #333333;
$text-light: #666666;
$background-color: #ffffff;
$border-color: #e0e0e0;
```

### Responsive Functions
```scss
// fs() - Responsive font size
font-size: m.fs(24);           // PC: 24px, Mobile: 17.76px (0.74 ratio)
font-size: m.fs(24, 0.5);      // Custom ratio: 0.5
font-size: m.fs(24, 1.2);      // Upscale: 1.2

// fsm() - Mobile-specific responsive
font-size: m.fsm(20);          // 380px base, 320px: 16.8px, 767px: 32px
font-size: m.fsm(20, 0.8, 1.6); // Custom ratios
```

### Media Queries
```scss
// mq() - Media query mixin
@include m.mq(md, max) {        // Max-width: 767px
    // Mobile styles
}

@include m.mq(lg, min) {        // Min-width: 1024px
    // Desktop styles
}

// Available breakpoints
$breakpoints: (
    'xsm': 320px,
    'sm': 480px,
    'md': 768px,
    'lg': 1024px,
    'xl': 1280px,
    'xxl': 1440px,
);
```

## 🎨 Component Structure

### BEM Methodology
```scss
.component {
    // Block styles
    
    &__element {
        // Element styles
    }
    
    &--modifier {
        // Modifier styles
    }
    
    &__element--modifier {
        // Element with modifier
    }
}
```

### Common Components
```scss
// Button component
.btn {
    display: inline-block;
    padding: 12px 24px;
    background-color: v.$brand-red;
    color: white;
    transition: all 0.3s ease;
    
    &:hover {
        background-color: color.adjust(v.$brand-red, $lightness: -10%);
        transform: translateY(-2px);
    }
    
    &--secondary {
        background-color: v.$brand-blue;
    }
    
    &--outline {
        background-color: transparent;
        border: 2px solid v.$brand-red;
        color: v.$brand-red;
    }
}
```

## 🔧 Gulp Configuration

### Key Features
- **SCSS Compilation**: Expanded for development, compressed for production
- **Autoprefixer**: Automatic vendor prefixes
- **Source Maps**: For debugging
- **BrowserSync**: Live reload on localhost:3000
- **Error Handling**: Notifications for compilation errors

### File Watching
```javascript
// Automatically watches:
- assets/scss/**/*.scss  // SCSS files
- **/*.php              // PHP template files
- assets/js/**/*.js      // JavaScript files
```

## 🎯 678 Studio Specific Styles

### Hero Section
```scss
.studio-hero {
    background: linear-gradient(135deg, v.$brand-red, v.$brand-blue);
    color: white;
    padding: v.$spacing-xl 0;
    text-align: center;
    
    &__title {
        font-size: m.fs(48);
        margin-bottom: v.$spacing-md;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        
        @include m.mq(md, max) {
            font-size: m.fsm(36);
        }
    }
}
```

### Gallery Grid
```scss
.studio-gallery {
    &__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: v.$spacing-md;
        
        @include m.mq(md, max) {
            grid-template-columns: 1fr;
            gap: v.$spacing-sm;
        }
    }
    
    &__item {
        aspect-ratio: 4/3;
        overflow: hidden;
        transition: transform 0.3s ease;
        
        &:hover {
            transform: translateY(-4px);
        }
    }
}
```

## 🚨 Common Issues & Solutions

### Compilation Errors
1. **Undefined variable**: Check import statements and namespace usage
2. **Syntax errors**: Verify SCSS syntax and brackets
3. **Import path errors**: Ensure correct relative paths

### Color Function Issues
```scss
// ❌ Deprecated
background-color: darken(v.$brand-red, 10%);

// ✅ Modern (Sass 3.0+)
@use 'sass:color';
background-color: color.adjust(v.$brand-red, $lightness: -10%);
```

### BrowserSync Issues
1. **Not loading**: Check if WordPress is running on :8080
2. **Not refreshing**: Verify file watching patterns
3. **Port conflicts**: Change BrowserSync port in gulpfile.js

## 🔄 Development Workflow

### 1. Start Development
```bash
# Terminal 1: Start WordPress
make up

# Terminal 2: Start SCSS development
cd html/wp-content/themes/678studio
npm run dev
```

### 2. Development Process
1. Edit SCSS files in `assets/scss/`
2. Files auto-compile on save
3. BrowserSync auto-refreshes browser
4. Check console for compilation errors

### 3. Production Build
```bash
npm run build
```

## 📊 Performance Optimization

### SCSS Best Practices
1. **Minimize nesting**: Maximum 3 levels deep
2. **Use mixins**: For repeated patterns
3. **Optimize imports**: Import only what you need
4. **Use variables**: For consistent values

### Output Optimization
- **Minification**: Automatic in production build
- **Autoprefixer**: Handles vendor prefixes
- **Source maps**: For debugging (removed in production)

## 🎯 Future Enhancements

### Planned Features
1. **PostCSS**: Additional CSS processing
2. **Critical CSS**: Above-the-fold optimization
3. **CSS Modules**: Scoped styling
4. **Sass Modules**: Modern @use syntax throughout

---

*This SCSS system provides a modern, efficient workflow for 678 Studio theme development with live reload and production optimization.*