@php($featured = $event->getFirstMedia('featured'))
<x-layouts.app :title="$event->title" :description="$event->excerpt ?: $event->starts_at->format('j F Y').' at Prempeh College'"
    :image="$featured ? \App\Support\Img::url($featured, 'large') : null">
    <x-page-header :title="$event->title" eyebrow="Event" :crumbs="['Events' => route('events.index'), \Illuminate\Support\Str::limit($event->title, 40) => null]" />

    <div class="container-pc grid gap-10 py-12 lg:grid-cols-[1fr_20rem]">
        <article class="min-w-0">
            @if ($event->isPast())
                <p class="mb-6 rounded-sm bg-gold-100 px-4 py-2 text-sm font-semibold text-gold-700">This event has taken place.</p>
            @endif
            @if ($featured)
                <img src="{{ \App\Support\Img::url($featured, 'large') }}" alt="{{ \App\Support\Img::alt($featured, 'Event flyer') }}" class="mb-8 w-full rounded-sm shadow-md" data-lightbox>
            @endif
            <div class="prose-pc">
                @if ($event->body)
                    {!! \App\Support\Content::render($event->body) !!}
                @elseif ($event->excerpt)
                    <p>{{ $event->excerpt }}</p>
                @endif
            </div>
        </article>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            <div class="overflow-hidden rounded-sm shadow-md ring-1 ring-green-100">
                <div class="bg-green-900 p-5 text-white">
                    <p class="text-xs font-bold uppercase tracking-widest text-gold-400">{{ $event->starts_at->format('l') }}</p>
                    <p class="mt-1 font-serif text-2xl font-bold">{{ $event->starts_at->format('j F Y') }}</p>
                </div>
                <dl class="space-y-3 bg-white p-5 text-sm">
                    <div><dt class="font-semibold text-green-900">Time</dt>
                        <dd>{{ $event->all_day ? 'All day' : $event->starts_at->format('g:ia').($event->ends_at ? ' – '.$event->ends_at->format($event->ends_at->isSameDay($event->starts_at) ? 'g:ia' : 'j M Y, g:ia') : '') }}</dd></div>
                    @if ($event->venue)<div><dt class="font-semibold text-green-900">Venue</dt><dd>{{ $event->venue }}</dd></div>@endif
                </dl>
                @unless ($event->isPast())
                    <div class="border-t border-green-100 bg-white p-5">
                        <a href="{{ route('events.ics', $event->slug) }}" class="btn btn-green w-full justify-center">Add to calendar</a>
                    </div>
                @endunless
            </div>

            @if ($more->isNotEmpty())
                <div>
                    <x-pill-label icon="calendar">More events</x-pill-label>
                    <div class="mt-4 space-y-4">
                        @foreach ($more as $e)<x-compact-card :item="$e" kind="event" />@endforeach
                    </div>
                </div>
            @endif
        </aside>
    </div>

    <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $event->title,
        'startDate' => $event->starts_at->toIso8601String(),
        'endDate' => $event->ends_at?->toIso8601String(),
        'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
        'location' => ['@type' => 'Place', 'name' => $event->venue ?: config('site.name'), 'address' => config('site.location')],
        'image' => $featured ? [\App\Support\Img::url($featured, 'large')] : null,
        'description' => $event->excerpt,
        'organizer' => ['@type' => 'Organization', 'name' => config('site.name'), 'url' => url('/')],
    ]), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
</x-layouts.app>
