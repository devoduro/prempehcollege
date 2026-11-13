<?php

namespace App\Http\Controllers;

use App\Models\PrefectBoard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PrefectController extends Controller
{
    /** /prefects shows the current board (or the newest) and lists the earlier boards. */
    public function index(): View|RedirectResponse
    {
        $boards = PrefectBoard::published()->orderByDesc('is_current')->orderByDesc('academic_year')->get();
        if ($boards->isEmpty()) {
            return view('prefects.index', ['boards' => $boards, 'board' => null]);
        }

        return redirect()->route('prefects.show', $boards->first()->slug);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $board = PrefectBoard::published()->with('media')->where('slug', $slug)->first();
        if (! $board) {
            // Old addresses such as /prefects/class-of-2027 now live at the academic year, /prefects/2026-2027.
            if (preg_match('/^class-of-(\d{4})$/', $slug, $m)) {
                return redirect()->route('prefects.show', ((int) $m[1] - 1).'-'.$m[1], 301);
            }
            abort(404);
        }

        // Prefects grouped by position, in position order; custom titles come after the standard ones.
        $prefects = $board->prefects()->where('is_published', true)->with(['position', 'house', 'media'])->get()
            ->sortBy(fn ($p) => [$p->prefect_position_id ? $p->position->rank : 9999, $p->sort_order, $p->name])->values();

        return view('prefects.show', [
            'board' => $board,
            'prefects' => $prefects,
            'boards' => PrefectBoard::published()->orderByDesc('is_current')->orderByDesc('academic_year')->get(['id', 'name', 'slug', 'academic_year', 'is_current']),
        ]);
    }
}
