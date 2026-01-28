<?php namespace ImpulseTechnologies\Campaignr\Models;

use Model;
use Mail;
use ValidationException;

/**
 * Registration Model
 */
class Registration extends Model
{
    use \Winter\Storm\Database\Traits\Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'impulsetechnologies_campaignr_registrations';

    /**
     * @var array Fillable fields
     */
    protected $fillable = [
        'first_name',
        'other_name',
        'last_name',
        'phone_number',
        'email',
        'organisation_name',
        'additional_information',
        'event_id'
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'phone_number' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'event_id' => 'required|integer|exists:impulsetechnologies_campaignr_events,id'
    ];

    /**
     * @var array Custom validation messages
     */
    public $customMessages = [
        'first_name.required' => 'First name is required',
        'last_name.required' => 'Last name is required',
        'phone_number.required' => 'Phone number is required',
        'email.required' => 'Email address is required',
        'email.email' => 'Please enter a valid email address',
        'event_id.required' => 'Event selection is required',
        'event_id.exists' => 'Selected event does not exist'
    ];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'event' => [
            'ImpulseTechnologies\Campaignr\Models\Event',
            'key' => 'event_id'
        ]
    ];

    /**
     * @var array Dates to be cast as Carbon instances
     */
    protected $dates = ['created_at', 'updated_at'];

    /**
     * Get the full name of the registrant
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        $name = $this->first_name;

        if ($this->other_name) {
            $name .= ' ' . $this->other_name;
        }

        $name .= ' ' . $this->last_name;

        return $name;
    }

    /**
     * Send confirmation emails after registration
     *
     * @return void
     */
    public function afterCreate()
    {
        $this->sendConfirmationEmails();
    }

    /**
     * Send confirmation emails to registrant and admin
     *
     * @return void
     */
    protected function sendConfirmationEmails()
    {
        $vars = [
            'registration' => $this,
            'event' => $this->event,
            'fullName' => $this->full_name
        ];

        // Send confirmation to registrant
        Mail::send('impulsetechnologies.campaignr::mail.registration-confirmation', $vars, function($message) {
            $message->to($this->email, $this->full_name);
            $message->subject('Event Registration Confirmation - ' . $this->event->name);
        });

        // Send notification to admin
        $adminEmail = \Config::get('mail.from.address');
        if ($adminEmail) {
            Mail::send('impulsetechnologies.campaignr::mail.registration-admin', $vars, function($message) use ($adminEmail) {
                $message->to($adminEmail);
                $message->subject('New Event Registration - ' . $this->event->name);
            });
        }
    }
}
