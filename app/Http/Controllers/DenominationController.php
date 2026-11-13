<?php

namespace App\Http\Controllers;

use App\Models\Denomination;
use App\Models\StaffMember;
use Illuminate\View\View;

class DenominationController extends Controller
{
    public function index(): View
    {
        // The chaplaincy team comes from the staff profiles (e.g. the College Chaplain and College Counsellor).
        $chaplaincy = StaffMember::query()->where('is_published', true)->whereNotNull('name')
            ->where(fn ($q) => $q->where('role', 'like', '%Chaplain%')->orWhere('role', 'like', '%Counsel%'))
            ->whereHas('group.page')->with(['media', 'group.page'])->get();

        return view('church.index', [
            'denominations' => Denomination::active()->with('media')->get(),
            'chaplaincy' => $chaplaincy,
        ]);
    }

    public function show(string $slug): View
    {
        $d = Denomination::active()->with('media')->where('slug', $slug)->firstOrFail();

        return view('church.show', ['denomination' => $d, 'others' => Denomination::active()->whereKeyNot($d->id)->get()]);
    }
}
