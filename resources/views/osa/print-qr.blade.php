<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>QR: {{ $event->title }}</title>
    <style>
        @page { size: A4; margin: 12mm; }
        body { margin: 0; font-family: Georgia, 'Times New Roman', serif; color: #0f2a12; text-align: center; }
        .frame { border: 6px solid #115e03; border-radius: 12px; padding: 28px 24px; min-height: 250mm; box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; }
        .brand { font: 700 18px Arial, sans-serif; letter-spacing: .12em; text-transform: uppercase; color: #115e03; }
        h1 { font-size: 40px; margin: 6px 0 0; line-height: 1.15; }
        .meta { font: 18px Arial, sans-serif; color: #345; }
        .qr svg { width: 420px; height: 420px; }
        .cta { font: 700 28px Arial, sans-serif; background: #ffc53a; padding: 10px 26px; border-radius: 999px; }
        .url { font: 13px monospace; color: #567; word-break: break-all; max-width: 520px; }
        .bar { position: fixed; top: 12px; right: 12px; font-family: Arial, sans-serif; }
        .bar button { background: #115e03; color: #fff; border: 0; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        @media print { .bar { display: none; } }
    </style>
</head>
<body>
<div class="bar"><button onclick="window.print()">Print</button></div>
<div class="frame">
    <div class="brand">Prempeh College Old Students Association</div>
    <h1>{{ $event->title }}</h1>
    <div class="meta">{{ $event->starts_at->format('l, j F Y · g:i A') }}@if ($event->venue)<br>{{ $event->venue }}@endif</div>
    <div class="qr">{!! \App\Support\OsaQr::svg($event->registerUrl(), 420) !!}</div>
    <div class="cta">Scan to register your attendance</div>
    <div class="url">{{ $event->registerUrl() }}</div>
</div>
@if (request()->boolean('auto'))<script>window.addEventListener('load', () => setTimeout(() => window.print(), 300));</script>@endif
</body>
</html>
