<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(): View
    {
        return view('announcements.index', [
            'announcements' => Announcement::current()->with('media')->paginate(12),
        ]);
    }

    /** Expired notices stay reachable by direct link, with a note that they have expired. */
    public function show(string $slug): View
    {
        $a = Announcement::published()->with('media')->where('slug', $slug)->firstOrFail();

        return view('announcements.show', [
            'announcement' => $a,
            'more' => Announcement::current()->with('media')->whereKeyNot($a->id)->take(4)->get(),
        ]);
    }
}
