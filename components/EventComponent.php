<?php namespace ImpulseTechnologies\Campaignr\Components;

use ImpulseTechnologies\Campaignr\Models\Event;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Carbon\Carbon;
use ValidationException;

/**
 * EventComponent Component
 *
 * Displays a single event with full details, responsive layout, and modern styling.
 * Supports recurring events, location mapping, and calendar integration.
 *
 * @package ImpulseTechnologies\Campaignr
 * @author Impulse Technologies
 */
class EventComponent extends ComponentBase
{
    /**
     * @var Event The event being displayed
     */
    public $event;

    /**
     * @var string Calendar page URL
     */
    public $calendarPageUrl;

    /**
     * @var string iCal download URL
     */
    public $icalUrl;

    /**
     * @var bool Whether the event is currently happening
     */
    public $isHappening = false;

    /**
     * @var bool Whether the event has already passed
     */
    public $isPast = false;

    /**
     * @var Carbon Next occurrence of the event
     */
    public $nextOccurrence;

    /**
     * Component registration details
     */
    public function componentDetails()
    {
        return [
            'name' => 'impulsetechnologies.campaignr::lang.components.event.name',
            'description' => 'impulsetechnologies.campaignr::lang.components.event.description'
        ];
    }

    /**
     * Component properties
     */
    public function defineProperties()
    {
        return [
            'eventSlug' => [
                'title'         => 'impulsetechnologies.campaignr::lang.components.event.slug_name',
                'description'   => 'impulsetechnologies.campaignr::lang.components.event.slug_description',
                'default'       => '{{ :slug }}',
                'type'          => 'string'
            ],
            'calendarPage' => [
                'title'       => 'impulsetechnologies.campaignr::lang.components.event.page_name',
                'description' => 'impulsetechnologies.campaignr::lang.components.event.page_description',
                'type'        => 'dropdown',
                'default'     => 'calendar'
            ],
            'icalPage' => [
                'title'       => 'impulsetechnologies.campaignr::lang.components.event.ical.page_name',
                'description' => 'impulsetechnologies.campaignr::lang.components.event.ical.page_description',
                'default'     => 'download/calendar',
                'type'        => 'dropdown'
            ],
            'icalPageSlug' => [
                'title'       => 'impulsetechnologies.campaignr::lang.components.event.ical.slug_name',
                'description' => 'impulsetechnologies.campaignr::lang.components.event.ical.slug_description',
                'default'     => ':slug',
                'type'        => 'string'
            ],
            'showMap' => [
                'title'       => 'Show Map',
                'description' => 'Display an embedded map for the event location',
                'type'        => 'checkbox',
                'default'     => true
            ],
            'loadBootstrap' => [
                'title'       => 'Load Bootstrap CSS',
                'description' => 'Load Bootstrap 5 CSS (disable if already loaded in theme)',
                'type'        => 'checkbox',
                'default'     => false
            ],
            'loadFontAwesome' => [
                'title'       => 'Load Font Awesome',
                'description' => 'Load Font Awesome icons (disable if already loaded in theme)',
                'type'        => 'checkbox',
                'default'     => true
            ],
            'show404' => [
                'title'       => 'Show 404 Error',
                'description' => 'Return 404 HTTP error if event not found',
                'type'        => 'checkbox',
                'default'     => true
            ]
        ];
    }

    /**
     * Prepopulate the list of calendar pages
     */
    public function getCalendarPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Prepopulate the list of iCal pages
     */
    public function getIcalPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * Returns the event being displayed
     * This becomes available on the page as {{ component.event }}
     */
    public function event()
    {
        return $this->event;
    }

    /**
     * Component initialization
     */
    public function onRun()
    {
        // Load CSS assets
        $this->loadAssets();

        // Prepare page variables
        $this->prepareVars();

        // Load the event
        $this->event = $this->page['event'] = $this->loadEvent();

        // Handle event not found
        if (!$this->event) {
            if ($this->property('show404', true)) {
                return $this->controller->run('404');
            }
            return;
        }

        // Calculate event status
        $this->calculateEventStatus();

        // Make variables available to the page
        $this->page['isHappening'] = $this->isHappening;
        $this->page['isPast'] = $this->isPast;
        $this->page['nextOccurrence'] = $this->nextOccurrence;
        $this->page['calendarPageUrl'] = $this->calendarPageUrl;
        $this->page['icalUrl'] = $this->icalUrl;
    }

    /**
     * Load CSS and JS assets
     */
    protected function loadAssets()
    {
        // Load Bootstrap if requested
        if ($this->property('loadBootstrap', false)) {
            $this->addCss('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css');
            $this->addJs('https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js');
        }

        // Load Font Awesome if requested
        if ($this->property('loadFontAwesome', true)) {
            $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css');
        }

        // Load component CSS
        $this->addCss('/plugins/impulsetechnologies/campaignr/assets/css/event-component.css');

        // Inject custom color variables from settings
        $this->injectCustomColors();
    }

    /**
     * Inject custom CSS color variables from settings
     */
    protected function injectCustomColors()
    {
        $settings = \ImpulseTechnologies\Campaignr\Models\Settings::instance();
        $customCss = $settings::getCssVariables();

        // Add custom CSS if provided
        if (!empty($settings->custom_css)) {
            $customCss .= "\n" . $settings->custom_css;
        }

        $this->addCss($customCss);
    }

    /**
     * Prepare component variables
     */
    protected function prepareVars()
    {
        // Generate calendar page URL
        if ($calendarPage = $this->property('calendarPage')) {
            $this->calendarPageUrl = $this->controller->pageUrl($calendarPage);
        }

        // Generate iCal URL if event is loaded
        $eventSlug = $this->property('eventSlug');
        if ($icalPage = $this->property('icalPage')) {
            $icalPageSlug = $this->property('icalPageSlug', ':slug');
            // Parse the slug if it's a parameter
            if (strpos($eventSlug, '{{') === false) {
                $this->icalUrl = $this->controller->pageUrl($icalPage, [$icalPageSlug => $eventSlug]);
            }
        }
    }

    /**
     * Load the event from database
     */
    protected function loadEvent()
    {
        $slug = $this->property('eventSlug');

        // Parse Twig variable if present
        if (preg_match('/\{\{\s*:(\w+)\s*\}\}/', $slug, $matches)) {
            $slug = $this->param($matches[1]);
        }

        if (!$slug) {
            return null;
        }

        return Event::where('slug', $slug)->first();
    }

    /**
     * Calculate event status (past, present, future)
     */
    protected function calculateEventStatus()
    {
        if (!$this->event) {
            return;
        }

        $now = Carbon::now();
        $timeBegin = Carbon::parse($this->event->time_begin);
        $timeEnd = Carbon::parse($this->event->time_end);

        // Check if event is currently happening
        $this->isHappening = $now->between($timeBegin, $timeEnd);

        // Check if event has passed
        if ($this->event->repeat_event) {
            // For recurring events, check if it has stopped repeating
            $hasNextOccurrence = $this->event->getNextOccurrence();
            $this->isPast = !$hasNextOccurrence;

            if ($hasNextOccurrence && $this->event->nextOccurrence) {
                $this->nextOccurrence = Carbon::parse($this->event->nextOccurrence);
            }
        } else {
            // For non-recurring events, check if end time has passed
            $this->isPast = $timeEnd->isPast();
        }
    }

    /**
     * Get formatted event date range
     */
    public function getFormattedDateRange()
    {
        if (!$this->event) {
            return '';
        }

        $timeBegin = Carbon::parse($this->event->time_begin);
        $timeEnd = Carbon::parse($this->event->time_end);

        // Handle recurring events
        if ($this->event->repeat_event && $this->event->repeat_mode > 0) {
            return $this->getRecurringDateFormat($timeBegin, $timeEnd);
        }

        // Handle single events
        return $this->getSingleEventDateFormat($timeBegin, $timeEnd);
    }

    /**
     * Format date for recurring events
     */
    protected function getRecurringDateFormat($timeBegin, $timeEnd)
    {
        $repeatMode = $this->event->repeat_mode;
        $timeFormat = $timeBegin->format('g:i A') . ' - ' . $timeEnd->format('g:i A');

        switch ($repeatMode) {
            case 1: // Daily
                return 'Every day, ' . $timeFormat;
            case 2: // Weekly
                return 'Every ' . $timeBegin->format('l') . ', ' . $timeFormat;
            case 3: // Monthly
                return 'Every month on ' . $timeBegin->format('l') . ', ' . $timeFormat;
            case 4: // Yearly
                return 'Every year on ' . $timeBegin->format('F j') . ', ' . $timeFormat;
            default:
                return $timeFormat;
        }
    }

    /**
     * Format date for single events
     */
    protected function getSingleEventDateFormat($timeBegin, $timeEnd)
    {
        // Same day event
        if ($timeBegin->isSameDay($timeEnd)) {
            return $timeBegin->format('F j, Y') . ' from ' .
                   $timeBegin->format('g:i A') . ' to ' .
                   $timeEnd->format('g:i A');
        }

        // Multi-day event
        if ($timeBegin->year === $timeEnd->year) {
            if ($timeBegin->month === $timeEnd->month) {
                // Same month
                return $timeBegin->format('F j') . ' - ' .
                       $timeEnd->format('j, Y');
            }
            // Different months, same year
            return $timeBegin->format('F j') . ' - ' .
                   $timeEnd->format('F j, Y');
        }

        // Different years
        return $timeBegin->format('F j, Y') . ' - ' .
               $timeEnd->format('F j, Y');
    }

    /**
     * Get full address as a single string
     */
    public function getFullAddress()
    {
        if (!$this->event) {
            return '';
        }

        $parts = array_filter([
            trim($this->event->location_street . ' ' . $this->event->location_number),
            $this->event->location_zip . ' ' . $this->event->location_city,
            $this->event->location_country
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get Google Maps URL for the event location
     */
    public function getMapUrl()
    {
        if (!$this->event) {
            return '';
        }

        $address = $this->getFullAddress();
        return 'https://www.google.com/maps/search/?api=1&query=' . urlencode($address);
    }

    /**
     * Get Google Maps embed URL
     */
    public function getMapEmbedUrl()
    {
        if (!$this->event) {
            return '';
        }

        $address = $this->getFullAddress();
        return 'https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=' . urlencode($address);
    }
}
