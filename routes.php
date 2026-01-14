<?php

use ImpulseTechnologies\Campaignr\Models\Event;
use Carbon\Carbon;

Route::get('/calendar/download/{slug}', function($slug) {
    // Find the event
    $event = Event::where('slug', $slug)->first();

    if (!$event) {
        return response('Event not found', 404);
    }

    $eol = "\r\n"; // iCal requires CRLF

    // Generate the iCal content
    $ics_content = "BEGIN:VCALENDAR".$eol."VERSION:2.0".$eol."PRODID:-//NASCU//Event Calendar//EN".$eol."CALSCALE:GREGORIAN".$eol;

    // Prepare dates
    $evtBegin = Carbon::parse($event->time_begin)->format('Ymd\THis');
    $evtEnd = Carbon::parse($event->time_end)->format('Ymd\THis');
    $now = Carbon::now()->format('Ymd\THis');

    // Prepare location
    $location = trim(
        $event->location_street . ' ' . $event->location_number . ', ' .
        $event->location_zip . ' ' . $event->location_city . ' ' .
        $event->location_country
    );
    $location = preg_replace('/([\,;])/','\\\$1', $location);

    // Prepare description and title
    $description = html_entity_decode(strip_tags($event->description), ENT_COMPAT, 'UTF-8');
    $title = html_entity_decode($event->name, ENT_COMPAT, 'UTF-8');

    // Generate unique ID
    $uid = hash("md5", $now.$evtBegin.$evtEnd.$title.$description.$event->slug);

    // Event URL
    $url = url('/event/' . $event->slug);

    // Handle recurring events
    $repeat = '';
    if($event->repeat_event) {
        switch ($event->repeat_mode) {
            case 1: $freq = 'DAILY'; break;
            case 2: $freq = 'WEEKLY'; break;
            case 3: $freq = 'MONTHLY'; break;
            case 4: $freq = 'YEARLY'; break;
        }
        $repeat .= 'RRULE:FREQ='.$freq;
        if ($event->end_repeat_on) {
            $end = Carbon::parse($event->end_repeat_on)->format('Ymd\THis');
            $repeat .= ';UNTIL='.$end;
        }
        $repeat .= $eol;
    }

    // Build the event
    $ics_content .= "BEGIN:VEVENT".$eol;
    $ics_content .= 'DTSTART:'.$evtBegin.$eol;
    $ics_content .= 'DTEND:'.$evtEnd.$eol;
    $ics_content .= 'LOCATION:'.$location.$eol;
    $ics_content .= 'DTSTAMP:'.$now.$eol;
    $ics_content .= 'SUMMARY:'.$title.$eol;
    $ics_content .= 'URL;VALUE=URI:'.$url.$eol;
    $ics_content .= 'DESCRIPTION:'.$description.$eol;
    $ics_content .= 'UID:'.$uid.$eol;
    $ics_content .= $repeat;
    $ics_content .= "END:VEVENT".$eol;
    $ics_content .= 'END:VCALENDAR';

    // Return with proper headers for download
    return response($ics_content, 200)
        ->header('Content-Type', 'text/calendar; charset=utf-8')
        ->header('Content-Disposition', 'attachment; filename="' . str_slug($event->name) . '.ics"')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
});
