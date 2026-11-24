<!doctype html>
<html>
<body style="margin:0;background:#f3f6f3;font-family:Arial,Helvetica,sans-serif;color:#0f2a12">
<div style="max-width:560px;margin:0 auto;padding:24px">
    <div style="background:#115e03;color:#fff;padding:20px 24px;border-radius:6px 6px 0 0;border-bottom:4px solid #ffc53a">
        <h1 style="margin:0;font-size:20px">Prempeh College Old Students Association</h1>
    </div>
    <div style="background:#fff;padding:24px;border-radius:0 0 6px 6px">
        <p>Dear {{ $attendance->name }},</p>
        <p>Thank you for coming. We confirm that your presence has been recorded at:</p>
        <table style="width:100%;border-collapse:collapse;margin:16px 0">
            <tr><td style="padding:6px 0;color:#557">Meeting</td><td style="padding:6px 0"><strong>{{ $attendance->event->title }}</strong></td></tr>
            <tr><td style="padding:6px 0;color:#557">Date</td><td style="padding:6px 0">{{ $attendance->event->starts_at->format('l, j F Y \a\t g:i A') }}</td></tr>
            @if ($attendance->event->venue)<tr><td style="padding:6px 0;color:#557">Venue</td><td style="padding:6px 0">{{ $attendance->event->venue }}</td></tr>@endif
            <tr><td style="padding:6px 0;color:#557">Year completed</td><td style="padding:6px 0">{{ $attendance->year_completed }}</td></tr>
            @if ($attendance->house)<tr><td style="padding:6px 0;color:#557">House</td><td style="padding:6px 0">{{ $attendance->house->name }}</td></tr>@endif
            <tr><td style="padding:6px 0;color:#557">Checked in</td><td style="padding:6px 0">{{ $attendance->checked_in_at->format('j M Y, g:i A') }}</td></tr>
        </table>
        <p>Floreat Prempeh!</p>
    </div>
</div>
</body>
</html>
