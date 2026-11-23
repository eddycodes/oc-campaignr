<?php namespace ImpulseTechnologies\Campaignr\Components;

use ImpulseTechnologies\Campaignr\Models\Event;
use Carbon\Carbon;
use Cms\Classes\Page;

class Ical extends \Cms\Classes\ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'impulsetechnologies.campaignr::lang.components.ical.name',
            'description' => 'impulsetechnologies.campaignr::lang.components.ical.description'
        ];
    }

    public function defineProperties()
    {
        return [
          'eventSlug' => [
              'title'             => 'impulsetechnologies.campaignr::lang.components.ical.slug_name',
              'description'       => 'impulsetechnologies.campaignr::lang.components.ical.slug_description',
              'default'           => '{{ :slug }}',
              'type'              => 'string'
          ],
          'eventPage' => [
                'title'         => 'impulsetechnologies.campaignr::lang.components.ical.page_name',
                'description'   => 'impulsetechnologies.campaignr::lang.components.ical.page_description',
                'type'          => 'dropdown',
                'default'       => 'event'
          ]
        ];
    }

    // Prepopulate the list of pages to link to
    public function getEventPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    // We need to eject the calendar onRun(), because otherwise other headers
    // have already been sent by the framework, preventing an automated download
    public function onRun()
    {
        $events = null;
        if ($this->property('eventSlug')) {
            $events = [Event::where('slug', $this->property('eventSlug'))->get()->first()];
        } else {
            $events = Event::all();
        }

        $eol = "\r\n"; // iCal requires CRLF
        // Use this page as the PRODID to provide calendar apps with a direct
        // link to this iCal page.
        $ics_content = "BEGIN:VCALENDAR".$eol."VERSION:2.0".$eol."PRODID:".Page::url($this->baseFileName).$eol."CALSCALE:GREGORIAN".$eol;

        foreach ($events as $event) {
          // First some preparations. We need some dates in the correct format.
          $evtBegin = Carbon::createFromFormat('Y-m-d H:i:s', $event->time_begin)->format('Ymd\THis');
          $evtEnd = Carbon::createFromFormat('Y-m-d H:i:s', $event->time_end)->format('Ymd\THis');
          $now = Carbon::now()->format('Ymd\THis');

          // Location, description and title as iCal text values: plain text,
          // with backslashes, commas, semicolons and newlines escaped (RFC 5545).
          $location = $this->icalText(implode(', ', array_unique(array_filter([
            $event->location_misc,
            trim($event->location_street . ' ' . $event->location_number),
            trim($event->location_zip . ' ' . $event->location_city),
            $event->location_country,
          ]))));
          $description = $this->icalText(strip_tags(str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", (string) $event->description)));
          $title = $this->icalText((string) $event->name);

          // Stable per event, so re-importing the file updates the entry instead of duplicating it.
          $uid = hash('md5', $event->id . '-' . $event->slug) . '@' . parse_url(url('/'), PHP_URL_HOST);

          // Try to provide a correct URL to the event.
          $url = url('/');
          if ($this->property('eventPage')) {
            $url = Page::url($this->property('eventPage'), [$this->paramName('eventSlug', ':slug') => $event->slug]);
          }

          // If this is a recurring event, we need a repeating rule
          $repeat = '';
          if($event->repeat_event) {
            switch ($event->repeat_mode) {
              case 1:
              $freq = 'DAILY';
              break;
              case 2:
              $freq = 'WEEKLY';
              break;
              case 3:
              $freq = 'MONTHLY';
              break;
              case 4:
              $freq = 'YEARLY';
              break;
            }
            $repeat .= 'RRULE:FREQ='.$freq;
            if ($event->end_repeat_on) {
              // Limit repetition of the event to the user given date
              $end = Carbon::createFromFormat('Y-m-d H:i:s', $event->end_repeat_on)->format('Ymd\THis');
              $repeat .= ';UNTIL='.$end;
            }
            $repeat .= $eol;
          }

          // Now build the event part
          $ics_content .= "BEGIN:VEVENT".$eol;
          if ($event->all_day) {
            // All-day events are dates; DTEND is the day after the last day.
            $ics_content .= 'DTSTART;VALUE=DATE:'.Carbon::parse($event->time_begin)->format('Ymd').$eol;
            $ics_content .= 'DTEND;VALUE=DATE:'.Carbon::parse($event->time_end)->addDay()->format('Ymd').$eol;
          } else {
            $ics_content .= 'DTSTART:'.$evtBegin.$eol;
            $ics_content .= 'DTEND:'.$evtEnd.$eol;
          }
          if ($location !== '') {
            $ics_content .= 'LOCATION:'.$location.$eol;
          }
          $ics_content .= 'DTSTAMP:'.$now.$eol;
          $ics_content .= 'SUMMARY:'.$title.$eol;
          $ics_content .= 'URL;VALUE=URI:'.$url.$eol;
          if ($description !== '') {
            $ics_content .= 'DESCRIPTION:'.$description.$eol;
          }
          $ics_content .= 'UID:'.$uid.$eol;
          $ics_content .= $repeat;
          $ics_content .= "END:VEVENT".$eol;
        }

        $ics_content .= 'END:VCALENDAR';

        $headers = [
        'Content-Type'        => 'text/calendar; charset=utf-8',
        'Content-Disposition' => 'attachment; filename="calendar.ics"',
        ];

        return \Response::make($ics_content, 200, $headers);
    }

    /**
     * An iCal TEXT value: entities decoded, whitespace tidied, and backslash,
     * comma, semicolon and line breaks escaped so the line stays valid.
     */
    protected function icalText(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', str_replace(["\r\n", "\r"], "\n", $text));
        $text = trim(preg_replace("/\n{2,}/", "\n", $text));
        $text = addcslashes($text, '\\,;');

        return str_replace("\n", '\\n', $text);
    }
}
