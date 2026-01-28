# EventComponent - Single Event Display

A modern, responsive Winter CMS component for displaying detailed information about a single event with rich features including status badges, location mapping, calendar integration, and social sharing.

## Features

- **Modern & Responsive Design**: Beautiful, mobile-friendly layout that works on all devices
- **Event Status Detection**: Automatically shows if event is happening now, upcoming, or past
- **Recurring Events Support**: Full support for daily, weekly, monthly, and yearly recurring events
- **Location Integration**: Display address with Google Maps integration
- **Calendar Integration**: Add to calendar functionality (iCal format)
- **Social Sharing**: Built-in share functionality using Web Share API
- **SEO Optimized**: Includes Schema.org structured data for better search engine visibility
- **Customizable**: Multiple configuration options to match your theme
- **Accessibility**: Follows WCAG guidelines with proper ARIA labels
- **Dark Mode Ready**: Automatic dark mode support for modern browsers

## Installation

The component is already installed as part of the Campaignr plugin. Simply add it to your page:

1. Go to your Winter CMS backend
2. Navigate to CMS > Pages
3. Open the page where you want to display a single event
4. Click on "Components" tab
5. Drag and drop "EventComponent" onto your page
6. Configure the component properties

## Component Properties

### Basic Properties

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `eventSlug` | String | `{{ :slug }}` | URL parameter for the event slug |
| `calendarPage` | Dropdown | `calendar` | Page to link back to the calendar |
| `icalPage` | Dropdown | `download/calendar` | Page for iCal download |
| `icalPageSlug` | String | `:slug` | URL parameter name for iCal |

### Display Options

| Property | Type | Default | Description |
|----------|------|---------|-------------|
| `showMap` | Checkbox | `true` | Display embedded map for location |
| `loadBootstrap` | Checkbox | `false` | Load Bootstrap 5 CSS |
| `loadFontAwesome` | Checkbox | `true` | Load Font Awesome icons |
| `show404` | Checkbox | `true` | Return 404 if event not found |

## Usage Examples

### Basic Usage

```twig
[eventComponent]
==

{% component 'eventComponent' %}
```

### Custom Configuration

```twig
[eventComponent]
eventSlug = "{{ :slug }}"
calendarPage = "events"
icalPage = "download/event"
showMap = 1
loadFontAwesome = 1
show404 = 1
==

<div class="container">
    {% component 'eventComponent' %}
</div>
```

### URL Structure

Create a page with the following URL pattern:
```
/event/:slug
```

Example URLs:
- `/event/summer-festival`
- `/event/weekly-meetup`
- `/event/annual-conference-2026`

## Accessing Component Data

The component provides several variables you can use in your templates:

```twig
{% set event = eventComponent.event %}
{% set isHappening = eventComponent.isHappening %}
{% set isPast = eventComponent.isPast %}
{% set nextOccurrence = eventComponent.nextOccurrence %}
```

### Available Page Variables

- `event` - The event model object
- `isHappening` - Boolean, true if event is currently happening
- `isPast` - Boolean, true if event has already passed
- `nextOccurrence` - Carbon date of next occurrence (for recurring events)
- `calendarPageUrl` - URL to calendar page
- `icalUrl` - URL to download iCal file

## Customization

### Custom Template

Create a custom template by creating a partial:

```
themes/your-theme/partials/event/custom.htm
```

Then use it in your component:

```twig
{% component 'eventComponent' template='custom' %}
```

### Custom CSS

Override the default styles by adding your own CSS:

```css
.event-component {
    /* Your custom styles */
}

.event-title {
    color: your-brand-color;
    font-family: your-font;
}
```

### Styling Classes

Main container classes:
- `.event-component` - Main wrapper
- `.event-header` - Header section
- `.event-title` - Event title
- `.event-status-badge` - Status indicator
- `.event-meta` - Meta information section
- `.event-description` - Description section
- `.event-location` - Location section
- `.event-actions` - Action buttons section

## Google Maps Integration

### Option 1: Simple Link (Default)

The default template uses a clickable link to open Google Maps:

```twig
<a href="{{ __SELF__.getMapUrl() }}" target="_blank">
    View on Google Maps
</a>
```

### Option 2: Embedded Map

To use embedded maps, you need a Google Maps API key:

1. Get an API key from [Google Cloud Console](https://console.cloud.google.com/)
2. Enable the "Maps Embed API"
3. Update the component's `getMapEmbedUrl()` method with your API key:

```php
public function getMapEmbedUrl()
{
    if (!$this->event) {
        return '';
    }

    $address = $this->getFullAddress();
    return 'https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=' . urlencode($address);
}
```

4. Update the template to use iframe:

```twig
<iframe src="{{ __SELF__.getMapEmbedUrl() }}"
        allowfullscreen
        loading="lazy"></iframe>
```

## Event Model Properties

The component accesses these Event model properties:

### Basic Info
- `name` - Event name
- `slug` - URL-friendly slug
- `description` - Markdown description

### Date & Time
- `time_begin` - Start date/time
- `time_end` - End date/time
- `repeat_event` - Boolean, is recurring
- `repeat_mode` - Recurrence pattern (1=daily, 2=weekly, 3=monthly, 4=yearly)
- `end_repeat_on` - When to stop recurring

### Location
- `location_street` - Street name
- `location_number` - Street number
- `location_zip` - Postal code
- `location_city` - City
- `location_country` - Country
- `location_misc` - Additional info

## Methods

### Public Methods

#### `event()`
Returns the event being displayed.

```twig
{% set event = __SELF__.event() %}
```

#### `getFormattedDateRange()`
Returns a human-readable date range string.

```twig
{{ __SELF__.getFormattedDateRange() }}
```

#### `getFullAddress()`
Returns the complete address as a single string.

```twig
{{ __SELF__.getFullAddress() }}
```

#### `getMapUrl()`
Returns Google Maps search URL for the location.

```twig
<a href="{{ __SELF__.getMapUrl() }}">View on Map</a>
```

## Browser Support

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari 14+, Chrome Android 90+)

## Accessibility Features

- Semantic HTML5 elements
- Proper heading hierarchy
- ARIA labels where appropriate
- Keyboard navigation support
- Screen reader friendly
- High contrast mode compatible

## SEO Features

- Schema.org Event markup
- Semantic HTML structure
- Meta information optimization
- Social sharing support

## Performance

- Lazy loading for embedded maps
- CSS animations with GPU acceleration
- Optimized CSS delivery
- Print-friendly styles

## Troubleshooting

### Event Not Found

If you see "Event Not Found":
1. Check that the slug in the URL matches an event in the database
2. Verify the `eventSlug` property is configured correctly
3. Ensure the event hasn't been soft-deleted

### Map Not Displaying

If the map isn't showing:
1. Check that location fields are filled in the event
2. Verify `showMap` property is enabled
3. If using embedded maps, ensure API key is valid

### Styles Not Loading

If styles aren't applying:
1. Check browser console for CSS loading errors
2. Verify the CSS file path is correct
3. Clear Winter CMS cache: `php artisan cache:clear`

### Icons Not Showing

If Font Awesome icons aren't displaying:
1. Ensure `loadFontAwesome` is enabled
2. Check if Font Awesome is already loaded by your theme (disable component loading)
3. Verify CDN is accessible

## Advanced Usage

### Custom Date Formatting

Override the date format in your template:

```twig
{% set event = __SELF__.event %}
{{ event.time_begin|date('l, F j, Y \at g:i A') }}
```

### Conditional Display

Show different content based on event status:

```twig
{% if isHappening %}
    <div class="alert alert-success">This event is happening right now!</div>
{% elseif isPast %}
    <div class="alert alert-secondary">This event has ended.</div>
{% else %}
    <div class="alert alert-info">This event is coming up!</div>
{% endif %}
```

### Custom Share Buttons

Add specific social media sharing:

```html
<!-- Twitter -->
<a href="https://twitter.com/intent/tweet?text={{ event.name|url_encode }}&url={{ url_current() }}"
   target="_blank" class="event-btn event-btn-secondary">
    <i class="fab fa-twitter"></i> Share on Twitter
</a>

<!-- Facebook -->
<a href="https://www.facebook.com/sharer/sharer.php?u={{ url_current() }}"
   target="_blank" class="event-btn event-btn-secondary">
    <i class="fab fa-facebook"></i> Share on Facebook
</a>
```

## License

This component is part of the Campaignr plugin and follows the same license terms.

## Support

For issues, questions, or contributions, please contact Impulse Technologies or submit an issue on the plugin repository.

## Changelog

### Version 2.0 (January 2026)
- Complete rewrite with modern PHP practices
- Added responsive design with Bootstrap 5 compatibility
- Implemented event status detection
- Added Google Maps integration
- Included Schema.org structured data
- Added social sharing functionality
- Improved accessibility
- Added dark mode support
- Enhanced documentation

### Version 1.0
- Initial release
- Basic event display functionality
