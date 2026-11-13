<?php

namespace App\Support;

use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;

/** Looks up an old WordPress path in the redirects table (tolerant of trailing slashes and case). */
class LegacyRedirects
{
    public static function find(string $path): ?Redirect
    {
        $clean = '/'.trim(strtolower($path), '/');
        $candidates = array_unique([$clean.'/', $clean, rtrim($clean, '/').'/']);

        return Redirect::whereIn('from_path', $candidates)->first();
    }

    public static function respond(Redirect $r): RedirectResponse
    {
        Redirect::whereKey($r->id)->increment('hits');

        return redirect(url($r->to_path), $r->status_code);
    }
}
