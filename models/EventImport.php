<?php namespace ImpulseTechnologies\Campaignr\Models;

use Backend\Models\ImportModel;

/**
 * Event Import Model
 */
class EventImport extends ImportModel
{
    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required',
        'time_begin' => 'required',
        'time_end' => 'required'
    ];

    /**
     * Import data from CSV
     *
     * @param array $results
     * @param string $sessionKey
     * @return void
     */
    public function importData($results, $sessionKey = null)
    {
        foreach ($results as $row => $data) {
            try {
                // Create new event
                $event = new Event();
                $event->fill($data);

                // Handle boolean values
                if (isset($data['repeat_event'])) {
                    $event->repeat_event = (bool) $data['repeat_event'];
                }

                // Set default repeat mode if not provided
                if (!isset($data['repeat_mode']) || empty($data['repeat_mode'])) {
                    $event->repeat_mode = 2; // Default to weekly
                }

                $event->save();

                $this->logCreated();
            } catch (\Exception $ex) {
                $this->logError($row, $ex->getMessage());
            }
        }
    }
}
