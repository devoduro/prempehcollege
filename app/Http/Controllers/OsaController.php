<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\OsaEvent;
use App\Models\OsaExecutive;
use App\Services\OsaAttendanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OsaController extends Controller
{
    public function home(): View
    {
        return view('osa.home', [
            'executives' => OsaExecutive::active()->with(['house', 'media'])->get(),
            'upcoming' => OsaEvent::where('starts_at', '>=', now()->startOfDay())->orderBy('starts_at')->limit(5)->get(),
        ]);
    }

    /** Old QR codes printed with the random token keep working and move to the readable address. */
    public function legacy(string $token): RedirectResponse
    {
        return redirect()->to(OsaEvent::where('token', $token)->firstOrFail()->registerUrl(), 301);
    }

    public function register(string $slug): View
    {
        $event = OsaEvent::where('slug', $slug)->firstOrFail();

        return view('osa.register', ['event' => $event, 'houses' => House::active()->get(), 'open' => $event->isOpen()]);
    }

    public function store(Request $request, string $slug, OsaAttendanceService $service): RedirectResponse
    {
        $event = OsaEvent::where('slug', $slug)->firstOrFail();

        // Honeypot: bots fill the hidden field, people never see it.
        if ($request->filled('website')) {
            return redirect()->route('osa.thanks', $slug);
        }

        abort_unless($event->isOpen(), 403, 'Registration for this meeting is closed.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'year_completed' => ['required', 'integer', 'min:1930', 'max:'.date('Y')],
            'house_id' => ['required', 'integer', 'exists:houses,id'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'regex:/^\+?[0-9 ]{9,15}$/'],
        ], ['phone.regex' => 'Enter a valid phone number, for example 0244123456.'], ['year_completed' => 'year group']);

        $result = $service->checkIn($event, $data);

        return redirect()->route('osa.thanks', $slug)->with('osa_name', $result['attendance']->name)->with('osa_duplicate', ! $result['created']);
    }

    public function thanks(string $slug): View
    {
        $event = OsaEvent::where('slug', $slug)->firstOrFail();

        return view('osa.thanks', ['event' => $event]);
    }
}
