<?php

namespace App\Services;

use App\Mail\OsaAttendanceMail;
use App\Models\OsaAttendance;
use App\Models\OsaEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Records a check-in, then acknowledges it by SMS (Speso) and email. Notification failures never lose the check-in. */
class OsaAttendanceService
{
    public function __construct(private SpesoSmsService $sms) {}

    /**
     * @param  array{name: string, year_completed: int|string, house_id: int|string|null, email: string, phone: string}  $data
     * @return array{attendance: OsaAttendance, created: bool}
     */
    public function checkIn(OsaEvent $event, array $data, string $source = 'qr'): array
    {
        $data['phone'] = $this->sms->normalizePhone($data['phone']);

        $existing = $event->attendances()->where('phone', $data['phone'])->first();
        if ($existing) {
            return ['attendance' => $existing, 'created' => false];
        }

        $attendance = $event->attendances()->create($data + ['source' => $source, 'checked_in_at' => now()]);
        $attendance->setRelation('event', $event);

        $this->notify($attendance);

        return ['attendance' => $attendance, 'created' => true];
    }

    public function notify(OsaAttendance $a): void
    {
        $a->loadMissing('event', 'house');
        $first = trim(explode(' ', trim($a->name))[0]);
        $when = $a->event->starts_at->format('j M Y');

        $sms = $this->sms->sendSms($a->phone, "Hello {$first}, your attendance at the Prempeh College Old Students Association meeting \"{$a->event->title}\" ({$when}) has been recorded. Thank you for coming. - OSA");
        $a->sms_status = $sms['success'] ? 'sent' : 'failed';
        $a->sms_note = $sms['success'] ? null : mb_substr($sms['message'], 0, 250);

        try {
            Mail::to($a->email)->send(new OsaAttendanceMail($a));
            $a->email_status = 'sent';
            $a->email_note = null;
        } catch (\Throwable $e) {
            Log::error('OSA attendance email failed', ['attendance' => $a->id, 'error' => $e->getMessage()]);
            $a->email_status = 'failed';
            $a->email_note = mb_substr($e->getMessage(), 0, 250);
        }

        $a->save();
    }
}
