<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\View\View;

class FacilityController extends Controller
{
    public function index(): View
    {
        $facilities = Facility::active()->with('media')->get();

        return view('facilities.index', ['facilities' => $facilities, 'groups' => $facilities->groupBy('category')]);
    }

    public function show(string $slug): View
    {
        $facility = Facility::active()->with('media')->where('slug', $slug)->firstOrFail();

        return view('facilities.show', [
            'facility' => $facility,
            'others' => Facility::active()->with('media')->whereKeyNot($facility->id)->get(),
        ]);
    }
}
