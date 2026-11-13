<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\View\View;

class HouseController extends Controller
{
    public function index(): View
    {
        return view('houses.index', ['houses' => House::active()->with(['media', 'years'])->get()]);
    }

    public function show(string $slug): View
    {
        $house = House::active()->with(['media', 'years.media'])->where('slug', $slug)->firstOrFail();

        return view('houses.show', [
            'house' => $house,
            'others' => House::active()->with('media')->whereKeyNot($house->id)->get(),
        ]);
    }
}
