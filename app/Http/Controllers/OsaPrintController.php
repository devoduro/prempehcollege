<?php

namespace App\Http\Controllers;

use App\Models\House;
use App\Models\OsaEvent;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/** Printable pages for portal admins only (guard "osa"). */
class OsaPrintController extends Controller
{
    private function guard(): ?RedirectResponse
    {
        $admin = auth('osa')->user();
        if (! $admin) {
            return redirect()->guest(url('/osa/login'));
        }
        abort_unless($admin->is_active, 403);

        return null;
    }

    public function qr(OsaEvent $event): View|RedirectResponse
    {
        return $this->guard() ?? view('osa.print-qr', ['event' => $event]);
    }

    public function attendance(Request $request, OsaEvent $event): View|RedirectResponse|StreamedResponse
    {
        if ($redirect = $this->guard()) {
            return $redirect;
        }

        $filters = $request->only(['house', 'year_from', 'year_to', 'sms', 'email', 'search']);
        $rows = $event->attendances()->with('house')->filtered($filters)->orderBy('name')->get();

        if ($request->query('format') === 'csv') {
            return response()->streamDownload(function () use ($rows) {
                $out = fopen('php://output', 'w');
                fputcsv($out, ['No', 'Name', 'Year group', 'House', 'Email', 'Phone', 'SMS', 'Email status', 'Checked in']);
                foreach ($rows as $i => $a) {
                    // Guard against spreadsheet formula injection from member-typed text.
                    $safe = fn (string $v) => preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
                    fputcsv($out, [$i + 1, $safe($a->name), $a->year_completed, $a->house?->name, $safe($a->email), $a->phone, $a->sms_status, $a->email_status, $a->checked_in_at->format('Y-m-d H:i')]);
                }
                fclose($out);
            }, 'attendance-'.str($event->title)->slug().'.csv', ['Content-Type' => 'text/csv']);
        }

        return view('osa.print-attendance', [
            'event' => $event,
            'rows' => $rows,
            'filters' => $filters,
            'houseName' => filled($filters['house'] ?? null) ? House::find($filters['house'])?->name : null,
        ]);
    }
}
