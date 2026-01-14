<?php namespace ImpulseTechnologies\Campaignr\Components;

use ImpulseTechnologies\Campaignr\Models\Event;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Carbon\Carbon;

class EventList extends ComponentBase
{
    public $events;
    public $eventPage;

    public function componentDetails()
    {
        return [
            'name' => 'Event List',
            'description' => 'Displays a modern, Bootstrap-styled list of events with filtering and search'
        ];
    }

    public function defineProperties()
    {
        return [
            'maxItems' => [
                'title' => 'Max Items',
                'description' => 'Maximum number of events to display (0 = all)',
                'default' => 0,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Must be a number'
            ],
            'sortOrder' => [
                'title' => 'Sort Order',
                'description' => 'Sort events by date',
                'type' => 'dropdown',
                'default' => 'asc',
                'options' => [
                    'asc' => 'Ascending (oldest first)',
                    'desc' => 'Descending (newest first)'
                ]
            ],
            'showPast' => [
                'title' => 'Show Past Events',
                'description' => 'Include events that have already occurred',
                'type' => 'checkbox',
                'default' => false
            ],
            'loadFontAwesome' => [
                'title' => 'Load Font Awesome',
                'description' => 'Load Font Awesome icons (disable if already loaded in theme)',
                'type' => 'checkbox',
                'default' => true
            ],
            'eventPage' => [
                'title' => 'Event Detail Page',
                'description' => 'Page to link to for event details',
                'type' => 'dropdown',
                'default' => 'event'
            ],
            'eventSlug' => [
                'title' => 'Event Slug Parameter',
                'description' => 'URL parameter name for the event slug',
                'default' => ':slug',
                'type' => 'string'
            ]
        ];
    }

    public function getEventPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->addCss('/plugins/impulsetechnologies/campaignr/assets/css/event-list.css');

        if ($this->property('loadFontAwesome', true)) {
            $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
        }

        // Inject custom color variables from settings
        $this->injectCustomColors();

        $this->prepareVars();
        $this->events = $this->page['events'] = $this->loadEvents();
        $this->eventPage = $this->page['eventPage'] = $this->property('eventPage');
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

    protected function prepareVars()
    {
        $this->eventPage = $this->page['eventPage'] = $this->property('eventPage');
    }

    protected function loadEvents()
    {
        $query = Event::query();
        $now = Carbon::now();

        // Filter past events if needed
        if (!$this->property('showPast')) {
            $query->where('time_end', '>=', $now);
        }

        // Sort order
        $sortOrder = $this->property('sortOrder', 'asc');
        $query->orderBy('time_begin', $sortOrder);

        // Limit items
        $maxItems = $this->property('maxItems');
        if ($maxItems > 0) {
            $query->limit($maxItems);
        }

        return $query->get();
    }

    public function onFilterEvents()
    {
        $search = post('search');
        $month = post('month');

        $query = Event::query();
        $now = Carbon::now();

        if (!$this->property('showPast')) {
            $query->where('time_end', '>=', $now);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('location_city', 'LIKE', "%{$search}%");
            });
        }

        if ($month) {
            $date = Carbon::parse($month);
            $query->whereMonth('time_begin', $date->month)
                  ->whereYear('time_begin', $date->year);
        }

        $sortOrder = $this->property('sortOrder', 'asc');
        $query->orderBy('time_begin', $sortOrder);

        $maxItems = $this->property('maxItems');
        if ($maxItems > 0) {
            $query->limit($maxItems);
        }

        $this->events = $this->page['events'] = $query->get();
        $this->eventPage = $this->page['eventPage'] = $this->property('eventPage');

        return [
            '#event-list-container' => $this->renderPartial('@list-items')
        ];
    }
}
