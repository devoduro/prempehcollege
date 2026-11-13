<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\ContactMessage;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.contact', [
            'page' => Page::published()->whereNull('section')->where('slug', 'contact')->first(),
            'formToken' => Crypt::encryptString((string) time()),
        ]);
    }

    public function store(ContactRequest $request): RedirectResponse
    {
        ContactMessage::create($request->safe()->only(['name', 'email', 'phone', 'subject', 'message']) + [
            // Store a keyed hash, not the raw IP, so abuse can be traced without keeping personal data.
            'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
        ]);

        return redirect()->route('contact')->with('status', 'Thank you. Your message has been received and the school will reply soon.');
    }
}
