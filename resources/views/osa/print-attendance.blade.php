<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Attendance: {{ $event->title }}</title>
    <style>
        @page { size: A4 landscape; margin: 10mm; }
        body { margin: 16px; font: 13px Arial, sans-serif; color: #111; }
        h1 { margin: 0; font-size: 20px; }
        .sub { color: #444; margin: 4px 0 10px; }
        .chips span { display: inline-block; background: #e8f3e6; border-radius: 999px; padding: 2px 10px; margin: 0 4px 6px 0; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #999; padding: 5px 7px; text-align: left; }
        th { background: #115e03; color: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        tr:nth-child(even) td { background: #f5f8f5; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .sign { width: 120px; }
        .bar { margin-bottom: 12px; }
        .bar button, .bar a { background: #115e03; color: #fff; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; text-decoration: none; font-size: 13px; margin-right: 6px; }
        @media print { .bar { display: none; } body { margin: 0; } }
    </style>
</head>
<body>
<div class="bar">
    <button onclick="window.print()">Print</button>
    <a href="{{ request()->fullUrlWithQuery(['format' => 'csv']) }}">Download CSV</a>
</div>
<h1>Prempeh College Old Students Association: Attendance</h1>
<div class="sub"><strong>{{ $event->title }}</strong> · {{ $event->starts_at->format('j F Y, g:i A') }}@if ($event->venue) · {{ $event->venue }}@endif · <strong>{{ $rows->count() }}</strong> present</div>
<div class="chips">
    @if ($houseName)<span>House: {{ $houseName }}</span>@endif
    @if (filled($filters['year_from'] ?? null) || filled($filters['year_to'] ?? null))<span>Year completed: {{ $filters['year_from'] ?? 'any' }} to {{ $filters['year_to'] ?? 'any' }}</span>@endif
    @if (filled($filters['sms'] ?? null))<span>SMS: {{ $filters['sms'] }}</span>@endif
    @if (filled($filters['email'] ?? null))<span>Email: {{ $filters['email'] }}</span>@endif
    @if (filled($filters['search'] ?? null))<span>Search: {{ $filters['search'] }}</span>@endif
</div>
<table>
    <thead><tr><th>#</th><th>Name</th><th>Year</th><th>House</th><th>Phone</th><th>Email</th><th>Checked in</th><th class="sign">Signature</th></tr></thead>
    <tbody>
    @forelse ($rows as $i => $a)
        <tr><td>{{ $i + 1 }}</td><td>{{ $a->name }}</td><td>{{ $a->year_completed }}</td><td>{{ $a->house?->name }}</td><td>{{ $a->phone }}</td><td>{{ $a->email }}</td><td>{{ $a->checked_in_at->format('j M, g:i A') }}</td><td></td></tr>
    @empty
        <tr><td colspan="8" style="text-align:center;padding:20px">No one matches these filters.</td></tr>
    @endforelse
    </tbody>
</table>
<p style="margin-top:12px;color:#666;font-size:11px">Printed {{ now()->format('j M Y, g:i A') }}</p>
@if (request()->boolean('auto'))<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>@endif
</body>
</html>
