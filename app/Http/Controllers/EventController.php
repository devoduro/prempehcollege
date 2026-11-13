<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $showPast = $request->query('when') === 'past';

        $events = Event::published()->with('media')
            ->when($showPast, fn ($q) => $q->past(), fn ($q) => $q->upcoming())
            ->paginate(9)->withQueryString();

        return view('events.index', ['events' => $events, 'showPast' => $showPast]);
    }

    public function show(string $slug): View
    {
        $event = Event::published()->with('media')->where('slug', $slug)->firstOrFail();

        return view('events.show', [
            'event' => $event,
            'more' => Event::published()->with('media')->upcoming()->whereKeyNot($event->id)->take(3)->get(),
        ]);
    }

    /** "Add to calendar" file for phones and Outlook/Google Calendar. */
    public function ics(string $slug): Response
    {
        $event = Event::published()->where('slug', $slug)->firstOrFail();
        $fmt = fn ($d) => $d->copy()->utc()->format('Ymd\THis\Z');
        $esc = fn (?string $s) => str_replace(['\\', ';', ',', "\r\n", "\n"], ['\\\\', '\;', '\,', '\n', '\n'], (string) $s);
        $end = $event->ends_at ?? $event->starts_at->copy()->addHours(2);

        $lines = [
            'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Prempeh College//Events//EN', 'BEGIN:VEVENT',
            'UID:event-'.$event->id.'@'.parse_url(url('/'), PHP_URL_HOST),
            'DTSTAMP:'.$fmt(now()),
            $event->all_day ? 'DTSTART;VALUE=DATE:'.$event->starts_at->format('Ymd') : 'DTSTART:'.$fmt($event->starts_at),
            $event->all_day ? 'DTEND;VALUE=DATE:'.$end->copy()->addDay()->format('Ymd') : 'DTEND:'.$fmt($end),
            'SUMMARY:'.$esc($event->title),
            'LOCATION:'.$esc($event->venue),
            'DESCRIPTION:'.$esc($event->excerpt),
            'URL:'.url($event->path),
            'END:VEVENT', 'END:VCALENDAR',
        ];

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$event->slug.'.ics"',
        ]);
    }
}
