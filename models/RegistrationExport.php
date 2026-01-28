<?php namespace ImpulseTechnologies\Campaignr\Models;

use Backend\Models\ExportModel;

/**
 * Registration Export Model
 */
class RegistrationExport extends ExportModel
{
    /**
     * @var array fillable fields
     */
    protected $fillable = ['event_id'];

    /**
     * Export data to CSV/Excel
     *
     * @param array $columns
     * @param string $sessionKey
     * @return \Generator
     */
    public function exportData($columns, $sessionKey = null)
    {
        // Get event filter if provided
        $eventId = post('event_id');

        $query = Registration::with('event');

        // Filter by event if specified
        if ($eventId) {
            $query->where('event_id', $eventId);
        }

        $registrations = $query->orderBy('created_at', 'desc')->get();

        foreach ($registrations as $registration) {
            $data = [];

            foreach ($columns as $column) {
                $value = $this->getColumnValue($registration, $column);
                $data[$column] = $value;
            }

            yield $data;
        }
    }

    /**
     * Get the value for a specific column
     *
     * @param Registration $registration
     * @param string $column
     * @return mixed
     */
    protected function getColumnValue($registration, $column)
    {
        switch ($column) {
            case 'event_name':
                return $registration->event ? $registration->event->name : '';

            case 'event_date':
                return $registration->event && $registration->event->time_begin
                    ? $registration->event->time_begin->format('Y-m-d H:i')
                    : '';

            case 'full_name':
                return $registration->full_name;

            case 'registration_date':
                return $registration->created_at ? $registration->created_at->format('Y-m-d H:i') : '';

            default:
                return $registration->{$column} ?? '';
        }
    }

    /**
     * Get columns available for export
     *
     * @return array
     */
    public function exportColumns()
    {
        return [
            'id' => 'ID',
            'event_name' => 'Event Name',
            'event_date' => 'Event Date',
            'first_name' => 'First Name',
            'other_name' => 'Other Name',
            'last_name' => 'Last Name',
            'full_name' => 'Full Name',
            'email' => 'Email',
            'phone_number' => 'Phone Number',
            'organisation_name' => 'Organisation',
            'additional_information' => 'Additional Information',
            'registration_date' => 'Registration Date'
        ];
    }
}
