<?php namespace ImpulseTechnologies\Campaignr\Components;

use Cms\Classes\ComponentBase;
use ImpulseTechnologies\Campaignr\Models\Registration;
use ImpulseTechnologies\Campaignr\Models\Event;
use ValidationException;
use Flash;

/**
 * EventRegistration Component
 * 
 * Displays an event registration form
 */
class EventRegistration extends ComponentBase
{
    /**
     * @var Event The event to register for
     */
    public $event;

    /**
     * Component details
     * 
     * @return array
     */
    public function componentDetails()
    {
        return [
            'name' => 'Event Registration',
            'description' => 'Displays a registration form for events'
        ];
    }

    /**
     * Component properties
     * 
     * @return array
     */
    public function defineProperties()
    {
        return [
            'eventSlug' => [
                'title' => 'Event Slug',
                'description' => 'The slug of the event to register for (leave empty to use URL parameter)',
                'default' => '{{ :slug }}',
                'type' => 'string'
            ],
            'modalMode' => [
                'title' => 'Modal Mode',
                'description' => 'Display as modal instead of inline form',
                'default' => false,
                'type' => 'checkbox'
            ]
        ];
    }

    /**
     * Initialize component
     */
    public function onRun()
    {
        $this->addCss('/plugins/impulsetechnologies/campaignr/assets/css/registration.css');
        $this->addJs('/plugins/impulsetechnologies/campaignr/assets/js/registration.js');
        
        $this->prepareVars();
        
        // Use modal view if in modal mode
        if ($this->property('modalMode')) {
            return $this->renderPartial('@modal');
        }
    }

    /**
     * Prepare component variables
     */
    protected function prepareVars()
    {
        $slug = $this->property('eventSlug');
        
        if ($slug) {
            $this->event = $this->page['event'] = Event::where('slug', $slug)->first();
        }
    }

    /**
     * Handle registration form submission
     */
    public function onRegister()
    {
        try {
            // Get form data
            $data = post();
            
            // Validate and create registration
            $registration = new Registration();
            $registration->fill($data);
            $registration->save();

            // Flash success message
            Flash::success('Thank you for registering! You will receive a confirmation email shortly.');

            return [
                'success' => true,
                'message' => 'Registration successful!',
                '#registration-form' => $this->renderPartial('@success')
            ];

        } catch (ValidationException $e) {
            Flash::error($e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Flash::error('An error occurred during registration. Please try again.');
            throw $e;
        }
    }

    /**
     * Get event by ID (for AJAX calls)
     */
    public function onGetEvent()
    {
        $eventId = post('event_id');
        
        if (!$eventId) {
            return ['error' => 'Event ID is required'];
        }

        $event = Event::find($eventId);
        
        if (!$event) {
            return ['error' => 'Event not found'];
        }

        return [
            'event' => $event,
            'eventName' => $event->name,
            'eventDate' => $event->time_begin ? $event->time_begin->format('F j, Y') : 'TBD'
        ];
    }
}
