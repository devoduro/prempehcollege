<?php

namespace App\Http\Controllers;

use App\Support\LegacyRedirects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Any unmatched URL: send old WordPress paths to their new home with a 301, otherwise 404. */
class FallbackController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        if ($redirect = LegacyRedirects::find($request->path())) {
            return LegacyRedirects::respond($redirect);
        }

        abort(404);
    }
}
