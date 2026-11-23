<?php namespace ImpulseTechnologies\Campaignr\Components;

use ImpulseTechnologies\Campaignr\Models\Event;
use ImpulseTechnologies\Campaignr\Traits\EventRegistrationTrait;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Request;

class EventList extends ComponentBase
{
    use EventRegistrationTrait;

    public $events;
    public $eventPage;

    /** @var array<int, int> Events per year for the current period and category, newest year first. */
    public $years = [];

    /** @var array<string, int> Events per category for the current period and year. */
    public $categories = [];

    /** @var int|null Year being listed. */
    public $year;

    /** @var string|null Category being listed. */
    public $category;

    /** @var int|null Month (1-12) being listed; only with a year. */
    public $month;

    /** @var array<int, int> Events per month (1-12) of the chosen year, for the current category. */
    public $months = [];

    public function componentDetails()
    {
        return [
            'name' => 'Event List',
            'description' => 'Displays a list of events with optional period, year and category filters and pagination'
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
                'description' => 'Include events that have already occurred (ignored when Period is set)',
                'type' => 'checkbox',
                'default' => false
            ],
            'period' => [
                'title' => 'Period',
                'description' => 'Upcoming: not yet ended. Past: already ended. Leave empty to use Show Past Events.',
                'type' => 'dropdown',
                'default' => '',
                'options' => [
                    '' => 'Use Show Past Events',
                    'upcoming' => 'Upcoming',
                    'past' => 'Past',
                    'all' => 'All'
                ]
            ],
            'perPage' => [
                'title' => 'Events per page',
                'description' => 'Paginate the list (0 = no pagination)',
                'default' => 0,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Must be a number',
                'group' => 'Filters and pagination'
            ],
            'queryFilters' => [
                'title' => 'Filter from the query string',
                'description' => 'Read ?year=, ?category= and ?page= from the URL',
                'type' => 'checkbox',
                'default' => false,
                'group' => 'Filters and pagination'
            ],
            'year' => [
                'title' => 'Year',
                'description' => 'Only list events that begin in this year',
                'type' => 'string',
                'default' => '',
                'group' => 'Filters and pagination'
            ],
            'month' => [
                'title' => 'Month',
                'description' => 'Only list events that begin in this month (1-12); needs a year',
                'type' => 'string',
                'default' => '',
                'group' => 'Filters and pagination'
            ],
            'filtersShowAll' => [
                'title' => 'Date filters show every event',
                'description' => 'When a year or month is chosen, list all its events, past and upcoming, instead of only the Period',
                'type' => 'checkbox',
                'default' => false,
                'group' => 'Filters and pagination'
            ],
            'category' => [
                'title' => 'Category',
                'description' => 'Only list events in this category',
                'type' => 'string',
                'default' => '',
                'group' => 'Filters and pagination'
            ],
            'loadAssets' => [
                'title' => 'Load plugin styles and scripts',
                'description' => 'Disable when the theme styles the list',
                'type' => 'checkbox',
                'default' => true
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
        if ($this->property('loadAssets', true)) {
            $this->addCss('/plugins/impulsetechnologies/campaignr/assets/css/event-list.css');

            if ($this->property('loadFontAwesome', true)) {
                $this->addCss('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
            }

            // Load registration assets
            $this->loadRegistrationAssets();
        }

        $this->prepareVars();
        $this->events = $this->page['events'] = $this->loadEvents();
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

        $year = $this->filterValue('year');
        $this->year = $this->page['year'] = ctype_digit($year) ? (int) $year : null;

        $month = $this->filterValue('month');
        $this->month = $this->page['month'] = ($this->year && ctype_digit($month) && $month >= 1 && $month <= 12) ? (int) $month : null;

        $category = $this->filterValue('category');
        $this->category = $this->page['category'] = $category !== '' ? $category : null;

        $byCategory = fn ($q) => $q->when($this->category, fn ($q) => $q->where('category', $this->category));

        // Year (and month) choices count what choosing them would list.
        $this->years = $this->page['years'] = $this->countBy(
            $byCategory($this->periodQuery(true)),
            fn ($event) => $event->time_begin->year
        );
        krsort($this->years);

        $this->months = $this->page['months'] = $this->year ? $this->countBy(
            $byCategory($this->periodQuery(true))->whereYear('time_begin', $this->year),
            fn ($event) => $event->time_begin->month
        ) : [];
        ksort($this->months);

        $this->categories = $this->page['categories'] = $this->countBy(
            $this->dateFilter($this->periodQuery($this->year !== null))->whereNotNull('category'),
            fn ($event) => $event->category
        );
        ksort($this->categories);
    }

    /**
     * The Period's events; with filtersShowAll, every event once a date filter applies.
     */
    protected function periodQuery(bool $dateFiltered)
    {
        return ($dateFiltered && $this->property('filtersShowAll')) ? Event::query() : $this->baseQuery();
    }

    protected function dateFilter($query)
    {
        return $query
            ->when($this->year, fn ($q) => $q->whereYear('time_begin', $this->year))
            ->when($this->month, fn ($q) => $q->whereMonth('time_begin', $this->month));
    }

    /**
     * A filter's value: the request (query string, or the data of an onFilter
     * AJAX request) when queryFilters is on, otherwise the property.
     */
    protected function filterValue(string $name): string
    {
        $value = $this->property('queryFilters') ? Request::input($name, '') : $this->property($name, '');

        return trim(is_string($value) ? $value : '');
    }

    /**
     * Events in the configured period, before year and category filters.
     */
    protected function baseQuery()
    {
        $query = Event::query();
        $now = Carbon::now();

        $period = $this->property('period') ?: ($this->property('showPast') ? 'all' : 'upcoming');

        if ($period === 'upcoming') {
            $query->where('time_end', '>=', $now);
        } elseif ($period === 'past') {
            $query->where('time_end', '<', $now);
        }

        return $query;
    }

    protected function countBy($query, callable $key): array
    {
        return $query->get(['id', 'time_begin', 'category'])
            ->countBy($key)
            ->all();
    }

    protected function loadEvents()
    {
        // A single month reads best in calendar order.
        $direction = $this->month ? 'asc' : ($this->property('sortOrder', 'asc') === 'desc' ? 'desc' : 'asc');

        $query = $this->dateFilter($this->periodQuery($this->year !== null))
            ->with('image')
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->orderBy('time_begin', $direction);

        $perPage = (int) $this->property('perPage');
        if ($perPage > 0) {
            $page = $this->property('queryFilters') ? max(1, (int) Request::input('page', 1)) : 1;

            return $query->paginate($perPage, ['*'], 'page', $page)
                ->withPath(Request::url())
                ->appends(array_filter(['year' => $this->year, 'month' => $this->month, 'category' => $this->category]));
        }

        $maxItems = (int) $this->property('maxItems');
        if ($maxItems > 0) {
            $query->limit($maxItems);
        }

        return $query->get();
    }

    /**
     * AJAX: re-list with the posted year, month, category and page (queryFilters).
     * Pair it with data-request-update to re-render the theme's list partial.
     */
    public function onFilter()
    {
        $this->prepareVars();
        $this->events = $this->page['events'] = $this->loadEvents();
    }

    public function onFilterEvents()
    {
        $search = post('search');
        $month = post('month');

        $query = $this->baseQuery();

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
