<?php

namespace App\Mail;

use App\Models\OsaAttendance;
use Illuminate\Mail\Mailable;

/** Sent straight away (no queue worker needed) to acknowledge a member's presence at a meeting. */
class OsaAttendanceMail extends Mailable
{
    public function __construct(public OsaAttendance $attendance) {}

    public function build(): self
    {
        return $this->subject('Attendance confirmed: '.$this->attendance->event->title)
            ->view('emails.osa-attendance');
    }
}
