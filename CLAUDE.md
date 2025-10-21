# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Jake and Co WordPress project built on Pantheon's WordPress Composer Managed upstream. It features:
- **Timber/Twig** templating for theme development
- **ACF Pro** with ACF Extended Pro for custom fields
- **Composer** for PHP dependency management
- **Laravel Mix** for asset compilation
- **Bootstrap 5** for front-end framework
- **Custom block system** with scaffolding tools
- **Storybook** for component development

## Environment Setup

### Starting the Development Environment

**DDEV (Recommended):**
```bash
ddev start
```
The site will be available at `https://stanford-del-wordpress.ddev.site`

**Docker Compose (Alternative):**
```bash
docker-compose up -d
```
The site will be available at `http://localhost:8000`

### Initial Setup

1. **Install dependencies:**
```bash
# Root level (WordPress core and plugins)
composer install

# Theme level
cd web/app/themes/limerock
composer install
npm install  # or pnpm install (preferred package manager)
```

2. **Build assets:**
```bash
cd web/app/themes/limerock
npm run build    # One-time build
npm run watch    # Watch mode for development
```

## Theme Development

The active custom theme is located at `web/app/themes/limerock/`.

### Directory Structure

- `lib/` - PHP backend functionality, including ACF composer field definitions
- `src/` - Source files for assets (compiled to `dist/`)
  - `src/js/` - JavaScript modules
  - `src/scss/` - SCSS styles with Bootstrap customization
  - `src/assets/` - Images and fonts
- `views/` - Twig templates (follows WordPress template hierarchy)
  - `views/blocks/` - Gutenberg block templates
  - `views/partial/` - Reusable template partials
  - `views/parts/` - Theme parts (header, footer, etc.)
- `acf-json/` - ACF field group definitions (managed via WordPress admin)
- `skel/` - Scaffolding tool source code
- `.storybook/` - Storybook configuration for component development

### Key Commands

**Asset Compilation:**
```bash
cd web/app/themes/limerock
npm run build        # Production build
npm run watch        # Development with file watching
npm run storybook    # Launch Storybook on port 6006
```

**Scaffolding:**
```bash
cd web/app/themes/limerock
npm run generate                           # Interactive mode
npm run generate block -- --name="My Block"
npm run generate post-type -- --name="My Post Type"
npm run generate options -- --name="My Options Page"
```

**Testing (Theme):**
```bash
cd web/app/themes/limerock
composer test    # Run PHPUnit tests
```

## Code Quality

### Linting

**Root Level:**
```bash
composer lint              # Run all linters
composer lint:php          # PHP syntax check
composer lint:phpcs        # PHP CodeSniffer
composer lint:phpcbf       # PHP Code Beautifier and Fixer (auto-fix)
composer lint:bash         # Shellcheck for bash scripts
```

**PHPCS Configuration:**
- Uses Pantheon WordPress Coding Standards
- Excludes: WordPress core (`web/wp`), plugins, mu-plugins, vendor
- Theme code in `web/app/themes/limerock` is linted

### Standards
- Follow WordPress coding standards (enforced by PHPCS)
- Use Timber/Twig for all template rendering (no direct PHP in templates)
- Keep business logic in PHP classes within `lib/`, not in template files

## WordPress Configuration

**Configuration Files:**
- `.env` - Local environment variables (not in git, see `.env.example`)
- `config/application.php` - Main WP configuration (uses environment variables)
- `wp-cli.yml` - WP-CLI configuration

**WordPress Installation:**
- Core: `web/wp/` (managed by Composer, do not edit)
- Content: `web/app/` (themes, plugins, uploads)
- Document root: `web/`

## ACF Block Development

Blocks use a custom ACF Composer system for reusable field definitions.

**Including Composed Fields in `acf-composed.json`:**
```json
{
  "fields": [
    "LimeRockTheme/ACF/fields/body-copy",
    {
      "acf_composer_extend": "LimeRockTheme/ACF/fields/body-copy",
      "name": "overridden",
      "label": "Custom Label"
    }
  ]
}
```

Field definitions are stored in `lib/acf-composer/<type>/<field>.json`.

**Block Structure:**
Each block in `views/blocks/<block-name>/` typically has:
- `<block-name>.twig` - Template
- `acf-composed.json` - Field definitions

## Asset Pipeline

**Laravel Mix Configuration** (`webpack.mix.js`):
- JavaScript: `src/js/index.js` → `dist/js/main.js`
- Admin JavaScript: `src/js/admin/index.js` → `dist/js/admin.js`
- Styles: `src/scss/index.scss` → `dist/css/main.css`
- Editor Styles: `src/scss/_editor-base.scss` → `dist/css/editor-base.css`
- Images: Optimized and converted to WebP
- Fonts: Copied to `dist/assets/fonts/`

**Bootstrap Customization:**
Bootstrap variables are overridden in `src/scss/abstracts/bootstrap-vars/`.

## Database Management

**Import Database:**
Use Sequel Pro or similar MySQL client:
- Host: 127.0.0.1
- Port: 8081 (docker-compose) or check DDEV config
- User/Password: Check `.env` file

**WP-CLI Access:**
```bash
# With DDEV
ddev wp <command>

# With Docker
docker-compose exec wordpress wp <command>
```

## Required Plugins

Theme requires these plugins to be active:
- Advanced Custom Fields Pro
- ACF Field Group Composer

Without these, the theme will deactivate and show an error.

## Deployment Notes

This project is configured for deployment to Pantheon:
- Uses Pantheon's mu-plugin for platform integration
- Environment-specific configuration via `.env.pantheon`
- Push to Pantheon git remote or use standard Pantheon workflow

## Front-End Libraries

**JavaScript:**
- GSAP for animations
- Fancybox for lightboxes
- Swiper for carousels
- Masonry for grid layouts
- Accordion.js for accordions

**CSS:**
- Bootstrap 5.3+
- Normalize SCSS
- Custom SCSS architecture following ITCSS principles

## Important Notes

- Never edit files in `web/wp/` - these are managed by Composer
- ACF field groups can be edited in WordPress admin and will sync to `acf-json/`
- Use the scaffolding tool (`npm run generate`) to create new blocks/post types for consistency
- Storybook is available for developing and documenting components in isolation
- Theme uses PHP 8.1+ and requires Timber 2.x
