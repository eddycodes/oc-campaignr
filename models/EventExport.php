<?php namespace ImpulseTechnologies\Campaignr\Models;

use Backend\Models\ExportModel;

/**
 * Event Export Model
 */
class EventExport extends ExportModel
{
    /**
     * Export data to CSV
     * 
     * @param array $columns
     * @param string $sessionKey
     * @return \Generator
     */
    public function exportData($columns, $sessionKey = null)
    {
        $events = Event::all();
        
        foreach ($events as $event) {
            $event->addVisible($columns);
            yield $event->toArray();
        }
    }
}
