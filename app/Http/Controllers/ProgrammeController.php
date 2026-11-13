<?php

namespace App\Http\Controllers;

use App\Models\Programme;
use Illuminate\View\View;

class ProgrammeController extends Controller
{
    public function index(): View
    {
        return view('programmes.index', ['programmes' => Programme::active()->with(['combinations', 'media'])->get()]);
    }

    public function show(string $slug): View
    {
        $programme = Programme::active()->with(['combinations', 'media'])->where('slug', $slug)->firstOrFail();

        return view('programmes.show', [
            'programme' => $programme,
            'others' => Programme::active()->whereKeyNot($programme->id)->get(),
        ]);
    }
}
