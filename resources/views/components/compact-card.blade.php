@props(['item', 'kind' => 'announcement'])
@php
    $cover = \App\Support\Img::cover($item);
    $isEvent = $kind === 'event';
@endphp
{{-- Stacked card for the Announcements and Events columns: flyer image, title, date line, "Read More". --}}
<article class="group relative overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:shadow-xl">
    <div class="relative">
        @if ($cover)
            <img src="{{ \App\Support\Img::url($cover) }}" alt="{{ \App\Support\Img::alt($cover) }}" class="aspect-[16/9] w-full object-cover" loading="lazy" width="800" height="450">
        @else
            <div class="flex aspect-[16/9] w-full items-center justify-center bg-gradient-to-br from-green-900 to-green-700" aria-hidden="true">
                <img src="{{ asset('images/crest-white.png') }}" alt="" class="h-10 opacity-70" loading="lazy">
            </div>
        @endif
        @if ($isEvent)
            {{-- Date block --}}
            <div class="absolute left-3 top-3 w-14 overflow-hidden rounded-sm text-center shadow-lg">
                <span class="block bg-gold-400 py-0.5 text-[11px] font-bold uppercase text-green-950">{{ $item->starts_at->format('M') }}</span>
                <span class="block bg-white py-1 font-serif text-xl font-bold leading-none text-green-900">{{ $item->starts_at->format('j') }}</span>
            </div>
        @elseif ($item->is_pinned)
            <span class="absolute left-3 top-3 rounded-sm bg-gold-400 px-2 py-0.5 text-[11px] font-bold uppercase text-green-950 shadow">Pinned</span>
        @endif
    </div>
    <div class="p-4">
        <h3 class="font-serif text-base font-bold leading-snug text-green-950">
            <a href="{{ url($item->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ \Illuminate\Support\Str::limit($item->title, 80) }}</a>
        </h3>
        <p class="mt-1 text-xs text-green-950/60">
            @if ($isEvent)
                <time datetime="{{ $item->starts_at->toIso8601String() }}">{{ $item->starts_at->format($item->all_day ? 'D j M Y' : 'D j M Y, g:ia') }}</time>@if ($item->venue) &middot; {{ $item->venue }}@endif
            @elseif ($item->published_at)
                <time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->format('j M Y') }}</time>
            @endif
        </p>
        <p class="mt-2 text-sm font-semibold text-green-700">Read More &raquo;</p>
    </div>
</article>
