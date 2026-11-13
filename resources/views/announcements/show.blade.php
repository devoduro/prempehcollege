@php
    $featured = $announcement->getFirstMedia('featured');
    $files = $announcement->getMedia('files');
    $expired = $announcement->expires_at && $announcement->expires_at->isPast();
@endphp
<x-layouts.app :title="$announcement->title" :description="$announcement->excerpt"
    :image="$featured ? \App\Support\Img::url($featured, 'large') : null">
    <x-page-header :title="$announcement->title" eyebrow="Announcement"
        :crumbs="['Announcements' => route('announcements.index'), \Illuminate\Support\Str::limit($announcement->title, 40) => null]" />

    <div class="container-pc grid gap-10 py-12 lg:grid-cols-[1fr_20rem]">
        <article class="min-w-0">
            <p class="text-sm text-green-950/60">
                @if ($announcement->published_at)Published <time datetime="{{ $announcement->published_at->toDateString() }}">{{ $announcement->published_at->format('j F Y') }}</time>@endif
            </p>
            @if ($expired)
                <p class="mt-4 rounded-sm bg-gold-100 px-4 py-2 text-sm font-semibold text-gold-700">This notice expired on {{ $announcement->expires_at->format('j F Y') }} and is kept for reference.</p>
            @endif
            @if ($featured)
                <img src="{{ \App\Support\Img::url($featured, 'large') }}" alt="{{ \App\Support\Img::alt($featured, 'Announcement flyer') }}" class="mt-6 w-full rounded-sm shadow-md" data-lightbox>
            @endif
            <div class="prose-pc mt-8">
                @if ($announcement->body)
                    {!! \App\Support\Content::render($announcement->body) !!}
                @elseif ($announcement->excerpt)
                    <p>{{ $announcement->excerpt }}</p>
                @endif
            </div>

            @if ($files->isNotEmpty())
                <div class="mt-10">
                    <h2 class="font-serif text-xl font-bold text-green-900">Downloads</h2>
                    <ul class="mt-3 divide-y divide-green-100 rounded-sm bg-white ring-1 ring-green-100">
                        @foreach ($files as $file)
                            <li>
                                <a href="{{ $file->getUrl() }}" class="flex items-center justify-between gap-4 p-4 hover:bg-green-50" download>
                                    <span class="font-semibold text-green-800">{{ $file->name }}</span>
                                    <span class="shrink-0 text-xs uppercase text-green-950/60">{{ strtoupper(pathinfo($file->file_name, PATHINFO_EXTENSION)) }} &middot; {{ $file->human_readable_size }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </article>

        @if ($more->isNotEmpty())
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <x-pill-label icon="megaphone">More announcements</x-pill-label>
                <div class="mt-4 space-y-4">
                    @foreach ($more as $a)<x-compact-card :item="$a" kind="announcement" />@endforeach
                </div>
            </aside>
        @endif
    </div>
</x-layouts.app>
