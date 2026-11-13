<x-layouts.app :title="$showPast ? 'Past events' : 'Events'" description="Upcoming events at Prempeh College, Kumasi.">
    <x-page-header :title="$showPast ? 'Past events' : 'Events'" eyebrow="News & Events" :crumbs="['Events' => null]" />

    <div class="container-pc py-12">
        <x-news-tabs active="events" />

        <div class="mt-10">
            <x-section-title :title="$showPast ? 'Past events' : 'Upcoming events'"
                :href="$showPast ? route('events.index') : route('events.index', ['when' => 'past'])"
                :link-label="$showPast ? 'Upcoming events' : 'Past events'" />
        </div>

        @if ($events->isEmpty())
            <p class="mt-10 rounded-sm bg-white p-6 text-green-950/70 ring-1 ring-green-100">
                {{ $showPast ? 'No past events yet.' : 'No upcoming events at the moment. Check back soon.' }}
            </p>
        @else
            <ul class="mt-8 space-y-5">
                @foreach ($events as $event)
                    @php($cover = \App\Support\Img::cover($event))
                    <li class="group relative grid overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:shadow-xl sm:grid-cols-[7rem_1fr] md:grid-cols-[7rem_16rem_1fr]">
                        {{-- Date block --}}
                        <div class="flex items-center gap-3 bg-green-900 p-4 text-white sm:flex-col sm:justify-center sm:gap-0 sm:text-center">
                            <span class="text-xs font-bold uppercase tracking-widest text-gold-400">{{ $event->starts_at->format('M') }}</span>
                            <span class="font-serif text-4xl font-bold leading-none">{{ $event->starts_at->format('j') }}</span>
                            <span class="text-xs text-green-200">{{ $event->starts_at->format('Y') }}</span>
                        </div>
                        <div class="hidden md:block">
                            @if ($cover)
                                <img src="{{ \App\Support\Img::url($cover, 'thumb') }}" alt="" class="h-full w-full object-cover" loading="lazy" width="480" height="320">
                            @else
                                <div class="grid h-full place-items-center bg-green-800" aria-hidden="true"><img src="{{ asset('images/crest-white.png') }}" alt="" class="h-10 opacity-70"></div>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="font-serif text-xl font-bold uppercase leading-snug text-green-950">
                                <a href="{{ url($event->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $event->title }}</a>
                            </h3>
                            <p class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-green-950/70">
                                <span>{{ $event->all_day ? 'All day' : $event->starts_at->format('g:ia').($event->ends_at ? ' – '.$event->ends_at->format($event->ends_at->isSameDay($event->starts_at) ? 'g:ia' : 'j M, g:ia') : '') }}</span>
                                @if ($event->venue)<span>{{ $event->venue }}</span>@endif
                            </p>
                            @if ($event->excerpt)<p class="mt-2 text-green-950/80">{{ \Illuminate\Support\Str::limit($event->excerpt, 180) }}</p>@endif
                            <p class="mt-3 text-sm font-semibold text-green-700">Read More &raquo;</p>
                        </div>
                    </li>
                @endforeach
            </ul>
            <div class="mt-10">{{ $events->links() }}</div>
        @endif
    </div>
</x-layouts.app>
