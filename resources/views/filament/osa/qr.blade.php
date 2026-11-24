@php $url = $event->registerUrl(); @endphp
<div class="text-center">
    <div class="mx-auto inline-block bg-white p-3">{!! \App\Support\OsaQr::svg($url, 300) !!}</div>
    <p class="mt-3 text-sm font-semibold">Scan to register your attendance</p>
    <p class="mt-1 break-all text-xs text-gray-500">{{ $url }}</p>
    @unless ($event->isOpen())<p class="mt-2 text-sm font-semibold text-danger-600">Registration is currently closed for this meeting.</p>@endunless
    <div class="mt-4 flex justify-center gap-2">
        <a href="{{ route('osa.print.qr', $event) }}?auto=1" target="_blank" class="rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white">Print QR poster</a>
        <button type="button" class="rounded-lg bg-gray-100 px-3 py-2 text-sm font-semibold text-gray-800" onclick="navigator.clipboard.writeText('{{ $url }}')">Copy link</button>
    </div>
</div>
