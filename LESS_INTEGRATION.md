# Using LESS Variables with Campaignr Plugin

## Overview

The Campaignr plugin provides LESS versions of the CSS files, allowing theme developers to customize colors and styles using Winter CMS's asset compiler. This approach is ideal for theme developers who want compile-time customization.

## Two Approaches Available

### 1. Runtime Customization (Settings Panel)
- Best for: End users, site administrators
- Uses: CSS custom properties
- Files: `event-list.css`, `event-component.css`
- Managed through: Backend Settings panel
- Changes: Take effect immediately, no compilation needed

### 2. Compile-Time Customization (LESS)
- Best for: Theme developers, advanced users
- Uses: LESS variables
- Files: `event-list.less`, `event-component.less`
- Managed through: Theme `theme.yaml` and asset compilation
- Changes: Require asset compilation

## How to Use LESS Variables in Your Theme

### Step 1: Configure theme.yaml

Add the LESS variables to your theme's `theme.yaml` customization section:

```yaml
form:
    fields:
        # Event List Component Colors
        event_primary_color:
            label: Event Primary Color
            type: colorpicker
            default: '#003866'
            assetVar: 'less'
            tab: Events

        event_secondary_color:
            label: Event Secondary Color
            type: colorpicker
            default: '#0065b8'
            assetVar: 'less'
            tab: Events

        event_text_primary:
            label: Event Text Color
            type: colorpicker
            default: '#2d3748'
            assetVar: 'less'
            tab: Events

        # Event Component Colors
        event_component_primary:
            label: Event Component Primary
            type: colorpicker
            default: '#007bff'
            assetVar: 'less'
            tab: Events

        event_component_success:
            label: Success Color
            type: colorpicker
            default: '#28a745'
            assetVar: 'less'
            tab: Events
```

### Step 2: Include LESS Files in Your Theme

In your theme's page or layout, use the `{% styles %}` tag with LESS compilation:

```twig
{% styles %}
    <link rel="stylesheet" href="{{ 'plugins/impulsetechnologies/campaignr/assets/css/event-list.less'|theme }}">
    <link rel="stylesheet" href="{{ 'plugins/impulsetechnologies/campaignr/assets/css/event-component.less'|theme }}">
{% endstyles %}
```

Or compile them into your main theme stylesheet:

**assets/less/theme.less:**
```less
// Import Campaignr styles
@import "../../plugins/impulsetechnologies/campaignr/assets/css/event-list.less";
@import "../../plugins/impulsetechnologies/campaignr/assets/css/event-component.less";

// Override variables before import for different values
// @event-primary-color: #custom-color;
```

### Step 3: Enable Asset Compilation

Ensure the theme has asset compilation enabled. In your layout or page:

```twig
{% framework extras %}
{% styles %}
```

## Available LESS Variables

### Event List Component

```less
@event-primary-color: #003866;
@event-secondary-color: #0065b8;
@event-white: #ffffff;
@event-text-primary: #2d3748;
@event-text-secondary: #495057;
@event-text-muted: #6c757d;
@event-bg-light: #f8f9fa;
@event-border-color: #e9ecef;
```

### Event Component

```less
@event-component-primary: #007bff;
@event-component-primary-hover: #0056b3;
@event-component-success: #28a745;
@event-component-secondary: #6c757d;
@event-component-text-primary: #212529;
@event-component-text-secondary: #495057;
@event-component-text-muted: #6c757d;
@event-component-bg-light: #f8f9fa;
@event-component-bg-info: #e7f3ff;
@event-component-border: #e9ecef;
@event-component-border-light: #dee2e6;
@event-component-white: #ffffff;
@event-component-info-text: #004085;
```

## Advanced: Override in Your Theme LESS

Create a theme-specific LESS file that imports and overrides:

**themes/yourtheme/assets/less/events.less:**
```less
// Override Campaignr variables
@event-primary-color: #your-brand-color;
@event-secondary-color: #your-secondary-color;
@event-component-primary: #your-brand-color;

// Import the plugin styles (variables will be applied)
@import "../../../../plugins/impulsetechnologies/campaignr/assets/css/event-list.less";
@import "../../../../plugins/impulsetechnologies/campaignr/assets/css/event-component.less";

// Add additional custom styles
.event-card {
    // Your custom overrides
    border-radius: 20px;

    &:hover {
        transform: scale(1.02);
    }
}
```

Then include in your page/layout:
```twig
{% styles %}
    <link rel="stylesheet" href="{{ 'assets/less/events.less'|theme }}">
{% endstyles %}
```

## Combining Both Approaches

You can use both methods together:

1. **LESS for theme defaults** - Set base colors that match your theme
2. **Settings panel for runtime adjustments** - Allow administrators to fine-tune colors

The CSS custom properties (from settings) will override LESS-compiled values since they have higher specificity when applied inline.

## Asset Compilation Commands

### Development Mode
```bash
php artisan winter:util compile less
```

### Production Mode
```bash
php artisan winter:util compile less --production
```

### Watch Mode (Auto-compile on changes)
```bash
php artisan winter:util compile less --watch
```

## Theme Customization Example

Complete example for `theme.yaml`:

```yaml
name: My Theme
description: Custom theme with Campaignr integration
author: Your Name
homepage: https://example.com

form:
    tabs:
        fields:
            # Events Tab
            _events_section@events:
                label: Event Colors
                type: section
                comment: Customize event component colors

            event_primary_color@events:
                label: Primary Brand Color
                comment: Main color for events
                type: colorpicker
                default: '#003866'
                assetVar: 'less'

            event_secondary_color@events:
                label: Secondary Color
                comment: Accent color for gradients
                type: colorpicker
                default: '#0065b8'
                assetVar: 'less'

            event_component_primary@events:
                label: Button Color
                comment: Primary button and link color
                type: colorpicker
                default: '#007bff'
                assetVar: 'less'
```

## Benefits of LESS Approach

1. **Performance** - Compiled CSS is faster than runtime CSS variables
2. **Theme Integration** - Seamlessly integrates with theme customization
3. **Advanced Features** - Access to LESS functions, mixins, and operations
4. **Version Control** - Compiled CSS can be committed to repository
5. **No Backend Dependency** - Works without plugin settings

## Troubleshooting

### Styles not updating
- Run `php artisan cache:clear`
- Recompile assets: `php artisan winter:util compile less`
- Check file permissions on compiled assets

### Variables not applied
- Ensure `assetVar: 'less'` is set in theme.yaml
- Verify LESS file import order (variables must be defined before use)
- Check Winter CMS asset compiler configuration

### Compilation errors
- Validate LESS syntax
- Check import paths are correct
- Ensure all required LESS files exist

## Documentation References

- [Winter CMS Asset Compiler](https://wintercms.com/docs/develop/docs/themes/development#asset-compiler)
- [Theme Customization](https://wintercms.com/docs/develop/docs/themes/development#customization-file)
- [LESS Documentation](http://lesscss.org/)

## Support

For issues with LESS compilation, consult:
- Winter CMS documentation
- Plugin GitHub repository
- Winter CMS Discord community
