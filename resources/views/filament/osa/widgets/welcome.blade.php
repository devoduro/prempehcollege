<x-filament-widgets::widget>
    <div style="background:linear-gradient(120deg,#0b3d05,#115e03 60%,#1b7a0a);color:#fff;border-radius:10px;padding:24px 28px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;justify-content:space-between">
        <div>
            <p style="font-size:13px;opacity:.8;margin:0">{{ now()->format('l, j F Y') }}</p>
            <h2 style="font-size:26px;font-weight:700;margin:4px 0 6px">{{ $greeting }}, {{ $name }}</h2>
            <p style="margin:0;opacity:.9;font-size:14px">
                {{ $today }} check-in{{ $today === 1 ? '' : 's' }} today
                @if ($next) · Next meeting: <strong>{{ $next->title }}</strong>, {{ $next->starts_at->format('j M, g:i A') }}@else · No upcoming meeting yet @endif
            </p>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            <a href="{{ \App\Filament\Osa\Resources\OsaEventResource::getUrl('create') }}" style="background:#ffc53a;color:#0f2a12;font-weight:700;padding:9px 16px;border-radius:8px;font-size:14px">New meeting</a>
            @if ($next)<a href="{{ route('osa.print.qr', $next) }}?auto=1" target="_blank" style="background:rgba(255,255,255,.15);color:#fff;font-weight:600;padding:9px 16px;border-radius:8px;font-size:14px">Print next QR</a>@endif
            <a href="{{ \App\Filament\Osa\Resources\OsaExecutiveResource::getUrl('create') }}" style="background:rgba(255,255,255,.15);color:#fff;font-weight:600;padding:9px 16px;border-radius:8px;font-size:14px">Add executive</a>
        </div>
    </div>
</x-filament-widgets::widget>
