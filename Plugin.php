<?php namespace ImpulseTechnologies\Campaignr;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    /**
     * @var array Plugin dependencies
     */
    public $require = [];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'impulsetechnologies.campaignr::lang.plugin.name',
            'description' => 'impulsetechnologies.campaignr::lang.plugin.description',
            'author'      => 'Impulse Technologies',
            'icon'        => 'icon-bullhorn'
        ];
    }

    public function registerComponents()
    {
      return [
        'ImpulseTechnologies\Campaignr\Components\Calendar' => 'campaignrCalendar',
        'ImpulseTechnologies\Campaignr\Components\EventComponent' => 'campaignrEvent',
        'ImpulseTechnologies\Campaignr\Components\Upcoming' => 'campaignrUpcoming',
        'ImpulseTechnologies\Campaignr\Components\Ical' => 'campaignrIcal',
        'ImpulseTechnologies\Campaignr\Components\EventList' => 'campaignrEventList'
      ];
    }

    public function registerNavigation()
    {
        return [
            'main-menu-item' => [
                'label'       => 'impulsetechnologies.campaignr::lang.plugin.name',
                'url'         => \Backend::url('impulsetechnologies/campaignr/events'),
                'icon'        => 'icon-bullhorn',
                'permissions' => ['campaignr.events.*'],
                'order'       => 500,
                'sideMenu' => [
                    'event-create' => [
                        'label'       => 'impulsetechnologies.campaignr::lang.plugin.create',
                        'icon'        => 'icon-plus',
                        'url'         => \Backend::url('impulsetechnologies/campaignr/events/create'),
                        'permissions' => ['campaignr.events.edit']
                    ],
                    'event-list' => [
                        'label'       => 'impulsetechnologies.campaignr::lang.plugin.events',
                        'icon'        => 'icon-list',
                        'url'         => \Backend::url('impulsetechnologies/campaignr/events'),
                        'permissions' => ['campaignr.events.*']
                    ],
                    'event-calendar' => [
                        'label'       => 'impulsetechnologies.campaignr::lang.plugin.calendar',
                        'icon'        => 'icon-calendar',
                        'url'         => \Backend::url('impulsetechnologies/campaignr/calendar'),
                        'permissions' => ['campaignr.events.*']
                    ]
                ]
            ]
        ];
    }

    public function registerPermissions()
    {
        return [
            'campaignr.events.edit' => [
                'tab'   => 'impulsetechnologies.campaignr::lang.permissions.tab',
                'label' => 'impulsetechnologies.campaignr::lang.permissions.edit'
            ],
            'campaignr.events.*' => [
                'tab'   => 'impulsetechnologies.campaignr::lang.permissions.tab',
                'label' => 'Access Campaign Events'
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Campaignr Settings',
                'description' => 'Customize event component colors and styles',
                'category'    => 'Events',
                'icon'        => 'icon-palette',
                'class'       => 'ImpulseTechnologies\Campaignr\Models\Settings',
                'order'       => 500,
                'keywords'    => 'events colors theme style campaignr',
                'permissions' => ['campaignr.events.*']
            ]
        ];
    }
}
