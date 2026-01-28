# Campaignr Color Customization Guide

## Overview

The Campaignr plugin now supports full color customization through the WinterCMS settings panel. All colors used in the Event List and Event Component can be customized to match your brand.

## Accessing Settings

1. Log into your WinterCMS backend
2. Navigate to **Settings** → **Events** → **Campaignr Settings**
3. You'll see two tabs:
   - **Event List Colors** - For the Event List component
   - **Event Component Colors** - For the Event Detail component
   - **Advanced** - For custom CSS overrides

## Available Color Settings

### Event List Component Colors

These colors affect the `campaignrEventList` component:

- **Primary Color** (`#003866`) - Main brand color used in gradients and accents
- **Secondary Color** (`#0065b8`) - Secondary brand color for gradients
- **White/Light Color** (`#ffffff`) - White or light color for text and backgrounds
- **Primary Text Color** (`#2d3748`) - Main text color for headings
- **Secondary Text Color** (`#495057`) - Secondary text color for body content
- **Muted Text Color** (`#6c757d`) - Muted text for less important information
- **Light Background Color** (`#f8f9fa`) - Light background for sections
- **Border Color** (`#e9ecef`) - Border and separator color

### Event Component Colors

These colors affect the `campaignrEvent` component:

- **Primary Color** (`#007bff`) - Primary color for buttons and highlights
- **Primary Hover Color** (`#0056b3`) - Color when hovering over interactive elements
- **Success Color** (`#28a745`) - Color for success states (e.g., "happening now" badge)
- **Secondary Color** (`#6c757d`) - Secondary color for less prominent elements
- **Primary Text Color** (`#212529`) - Main text color for headings
- **Secondary Text Color** (`#495057`) - Color for body text and descriptions
- **Muted Text Color** (`#6c757d`) - Color for secondary information
- **Light Background Color** (`#f8f9fa`) - Light background for sections
- **Info Background Color** (`#e7f3ff`) - Background for informational sections
- **Border Color** (`#e9ecef`) - Main border color
- **Light Border Color** (`#dee2e6`) - Light border for subtle separators
- **White Color** (`#ffffff`) - White color for backgrounds and text
- **Info Text Color** (`#004085`) - Text color for informational messages

## Custom CSS

In the **Advanced** tab, you can add custom CSS to further customize the components. This is useful for:

- Overriding specific styles
- Adding custom animations
- Adjusting spacing or typography
- Creating entirely new visual styles

### Example Custom CSS

```css
/* Make event cards more rounded */
.event-card {
    border-radius: 20px;
}

/* Add a subtle animation to cards */
.event-card {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.event-card:hover {
    transform: translateY(-8px) scale(1.02);
}

/* Customize the gradient */
:root {
    --event-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
```

## CSS Variables Reference

All colors are implemented as CSS variables, which means they cascade throughout the component styles. Here's how they map:

### Event List Variables
```css
:root {
    --event-primary-color: #003866;
    --event-secondary-color: #0065b8;
    --event-white: #ffffff;
    --event-text-primary: #2d3748;
    --event-text-secondary: #495057;
    --event-text-muted: #6c757d;
    --event-bg-light: #f8f9fa;
    --event-border-color: #e9ecef;
}
```

### Event Component Variables
```css
:root {
    --event-component-primary: #007bff;
    --event-component-primary-hover: #0056b3;
    --event-component-success: #28a745;
    --event-component-secondary: #6c757d;
    --event-component-text-primary: #212529;
    --event-component-text-secondary: #495057;
    --event-component-text-muted: #6c757d;
    --event-component-bg-light: #f8f9fa;
    --event-component-bg-info: #e7f3ff;
    --event-component-border: #e9ecef;
    --event-component-border-light: #dee2e6;
    --event-component-white: #ffffff;
    --event-component-info-text: #004085;
}
```

## Tips for Color Customization

1. **Brand Consistency** - Use your brand's primary and secondary colors for a cohesive look
2. **Contrast** - Ensure sufficient contrast between text and backgrounds for accessibility
3. **Test Both Components** - If you use both components, make sure the colors work well together
4. **Use the Color Picker** - The settings panel includes a color picker for easy selection
5. **Preview Changes** - Changes take effect immediately on the frontend
6. **Backup Settings** - Consider documenting your color choices

## Accessibility Considerations

When customizing colors, keep these accessibility guidelines in mind:

- **Text Contrast**: Maintain at least 4.5:1 contrast ratio for normal text
- **Large Text**: Maintain at least 3:1 contrast ratio for large text (18pt+)
- **Interactive Elements**: Ensure buttons and links are easily distinguishable
- **Color Blindness**: Don't rely solely on color to convey information

## Troubleshooting

### Colors not updating
- Clear your browser cache
- Clear WinterCMS cache: `php artisan cache:clear`
- Refresh the page with Ctrl+F5 (hard refresh)

### Colors look wrong
- Check that you're editing the correct component settings (Event List vs Event Component)
- Verify that your theme doesn't have conflicting CSS
- Review your custom CSS for any overrides

### Reverting to defaults
- Each color field shows the default value
- Click "Reset to default" or manually enter the default hex code
- Save the settings

## Support

For issues or questions:
- Check the [WinterCMS documentation](https://wintercms.com/docs)
- Review the plugin's GitHub repository
- Contact Impulse Technologies support

## Version History

- **v1.1.0** - Added full color customization support with settings panel
- **v1.0.0** - Initial release with hardcoded colors
