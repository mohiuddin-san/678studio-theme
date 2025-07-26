# SCSS Development Guide

## Overview
The myproject theme uses SCSS with Gulp compilation and BrowserSync for live reload.

## File Structure
```
assets/scss/
├── style.scss          # Main entry point
├── base/
│   ├── _variables.scss # Colors, fonts, breakpoints
│   ├── _mixins.scss    # Reusable mixins and fs()/fsm() functions
│   └── _reset.scss     # Reset styles
└── components/
    ├── _common.scss    # Common styles
    ├── _header.scss    # Header styles
    └── _footer.scss    # Footer styles
```

## Development Commands
```bash
npm run dev    # Start with BrowserSync (http://localhost:3000)
npm run build  # Production build
npm run watch  # Watch without BrowserSync
npm run sass   # Compile SCSS once
```

## Responsive Design Functions
### fs() - Font Size Function
```scss
// Usage: fs(max-size, ratio)
font-size: fs(20);      // Default ratio 0.74
font-size: fs(24, 0.8); // Custom ratio
```

### fsm() - Mobile Font Size Function
```scss
// Usage: fsm(size-380, ratio-320, ratio-767)
font-size: fsm(16);           // Default ratios
font-size: fsm(18, 0.9, 1.4); // Custom ratios
```

### mq() - Media Query Mixin
```scss
// Default is max-width (mobile-first)
@include mq(md) { /* styles for <768px */ }
@include mq(lg, min) { /* styles for >=1024px */ }
```

## myproject Specific Features
- Integrated debug logging system
- BrowserSync live reload on port 3000
- Responsive fs() and fsm() functions
- Mobile-first mq() mixins
- Automated SCSS compilation with Gulp

## Best Practices
- Use BEM naming convention
- Leverage fs()/fsm() for responsive typography
- Use mq() mixins for breakpoints
- Keep components modular in separate files
- Use variables for consistency

*This document contains myproject-specific SCSS development guidelines.*
