@props(['news', 'desk', 'announcements', 'events'])
{{-- Home page: NEWS row, then the three-column EVENTS & ANNOUNCEMENTS block. --}}
@if ($news->isNotEmpty())
    <section class="bg-paper py-16" aria-labelledby="news-heading">
        <div class="container-pc">
            <x-section-title id="news-heading" title="News" :href="route('news.index')" link-label="All news" />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($news as $i => $post)
                    <x-news-card :post="$post" :highlight="$i === 0" />
                @endforeach
            </div>
        </div>
    </section>
@endif

<section id="events-announcements" class="scroll-mt-28 bg-green-50 py-16" aria-labelledby="ea-heading">
    <div class="container-pc">
        <x-section-title id="ea-heading" title="Events & Announcements" />

        <div class="mt-10 grid gap-8 lg:grid-cols-3">
            {{-- Headmaster's Desk --}}
            <div>
                <x-pill-label icon="news">{{ config('site.desk.label') }}</x-pill-label>
                <div class="mt-5">
                    @if ($desk)
                        <x-news-card :post="$desk" />
                    @else
                        <p class="rounded-sm bg-white p-6 text-sm text-green-950/70 ring-1 ring-green-100">Messages from the Headmaster will appear here.</p>
                    @endif
                </div>
            </div>

            {{-- Announcements --}}
            <div>
                <div class="flex items-center justify-between gap-2">
                    <x-pill-label icon="megaphone">Announcements</x-pill-label>
                    <a href="{{ route('announcements.index') }}" class="text-sm font-semibold text-green-700 hover:underline">All &raquo;</a>
                </div>
                <div class="mt-5 space-y-5">
                    @forelse ($announcements as $a)
                        <x-compact-card :item="$a" kind="announcement" />
                    @empty
                        <p class="rounded-sm bg-white p-6 text-sm text-green-950/70 ring-1 ring-green-100">No current announcements.</p>
                    @endforelse
                </div>
                @if ($announcements->hasPages())
                    <div class="mt-5">{{ $announcements->onEachSide(1)->links('components.mini-pager') }}</div>
                @endif
            </div>

            {{-- Events --}}
            <div>
                <div class="flex items-center justify-between gap-2">
                    <x-pill-label icon="calendar">Events</x-pill-label>
                    <a href="{{ route('events.index') }}" class="text-sm font-semibold text-green-700 hover:underline">All &raquo;</a>
                </div>
                <div class="mt-5 space-y-5">
                    @forelse ($events as $e)
                        <x-compact-card :item="$e" kind="event" />
                    @empty
                        <p class="rounded-sm bg-white p-6 text-sm text-green-950/70 ring-1 ring-green-100">No upcoming events. Check back soon.</p>
                    @endforelse
                </div>
                @if ($events->hasPages())
                    <div class="mt-5">{{ $events->onEachSide(1)->links('components.mini-pager') }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
