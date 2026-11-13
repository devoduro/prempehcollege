@php
    $links = config('site.links');
    $firstSlide = $slides->first()?->getFirstMedia('image');
    $heroImg = $hero->first();
    $aboutImg = \App\Models\Page::where('section', 'about')->where('slug', 'overview')->first()?->getFirstMedia('content');
    $boardPhoto = $board?->getFirstMedia('group_photo') ?? $board?->getFirstMedia('gallery');
@endphp
<x-layouts.app :image="$firstSlide ? \App\Support\Img::url($firstSlide, 'large') : ($heroImg ? \App\Support\Img::url($heroImg, 'large') : null)">

    {{-- 1. Slider (managed in the admin: Site > Home slider) --}}
    @if ($slides->isNotEmpty())
        <x-hero-slider :slides="$slides" />
    @else
        <section class="relative overflow-hidden bg-green-950 text-white">
            @if ($heroImg)<img src="{{ \App\Support\Img::url($heroImg, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-35" width="1600" height="900" fetchpriority="high">@endif
            <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/85 to-green-950/20"></div>
            <div class="container-pc relative py-24 md:py-32">
                <p class="eyebrow !text-gold-400">Est. {{ config('site.founded') }} &middot; {{ config('site.location') }}</p>
                <h1 class="mt-4 max-w-3xl font-serif text-4xl font-bold leading-tight md:text-6xl">A storied tradition of <span class="text-gold-400">academic excellence</span></h1>
            </div>
            <div class="h-2 bg-gold-400"></div>
        </section>
    @endif
    <h1 class="sr-only">{{ config('site.name') }}, {{ config('site.location') }}</h1>

    {{-- 2. Fast facts (all counts come from the database) --}}
    <section class="border-b border-green-100 bg-white" aria-label="At a glance">
        <dl class="container-pc grid grid-cols-2 gap-6 py-8 text-center md:grid-cols-4">
            <div><dt class="text-xs font-bold uppercase tracking-widest text-green-800">Founded</dt><dd class="mt-1 font-serif text-4xl font-bold text-green-900">{{ config('site.founded') }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-widest text-green-800">Programmes</dt><dd class="mt-1 font-serif text-4xl font-bold text-green-900">{{ $programmeCards->count() }}</dd></div>
            @if ($houses->isNotEmpty())
                <div><dt class="text-xs font-bold uppercase tracking-widest text-green-800">Houses</dt><dd class="mt-1 font-serif text-4xl font-bold text-green-900">{{ $houses->count() }}</dd></div>
            @endif
            <div><dt class="text-xs font-bold uppercase tracking-widest text-green-800">Years of legacy</dt><dd class="mt-1 font-serif text-4xl font-bold text-green-900">{{ now()->year - config('site.founded') }}</dd></div>
        </dl>
    </section>

    {{-- 3. About us --}}
    <section class="container-pc grid items-center gap-10 py-16 lg:grid-cols-2" aria-labelledby="about-heading">
        <div>
            <p class="eyebrow">About us</p>
            <h2 id="about-heading" class="mt-2 font-serif text-3xl font-bold text-green-900 md:text-4xl">A rich and storied history of quality education</h2>
            <div class="prose-pc mt-5 !text-base">
                <p>Prempeh College has a rich and storied history of providing quality education in Ghana. Over the years, our school has evolved and grown, adapting to the changing needs of students and embracing innovative teaching methods.</p>
                <p>Since our founding in {{ config('site.founded') }}, the College has redrawn and redefined the boundaries of intellectual and creative thought as a preeminent academic centre.</p>
            </div>
            <div class="mt-7 flex flex-wrap gap-3">
                <a href="{{ url('about') }}" class="btn btn-green">About the College</a>
                <a href="{{ url('about/history') }}" class="btn btn-ghost text-green-800 hover:bg-green-50">Our history</a>
            </div>
        </div>
        <div class="relative">
            @if ($aboutImg)
                <img src="{{ \App\Support\Img::url($aboutImg, 'large') }}" alt="{{ \App\Support\Img::alt($aboutImg, 'Prempeh College') }}" class="w-full rounded-sm object-cover shadow-xl" loading="lazy" width="1200" height="800">
            @elseif ($heroImg)
                <img src="{{ \App\Support\Img::url($heroImg, 'large') }}" alt="Prempeh College students" class="w-full rounded-sm object-cover shadow-xl" loading="lazy" width="1200" height="800">
            @endif
            <div class="absolute -bottom-4 -left-4 -z-10 hidden h-full w-full rounded-sm border-4 border-gold-400 md:block"></div>
        </div>
    </section>

    {{-- 4. Houses (moving slider) --}}
    @if ($houses->isNotEmpty())
        <section class="bg-green-50 py-16" aria-labelledby="houses-heading">
            <div class="container-pc">
                <x-section-title id="houses-heading" title="Our Houses" :href="route('houses.index')" link-label="All houses" />
                <div class="mt-10">
                    <x-card-slider label="Houses" :per-view="3">
                        @foreach ($houses as $house)
                            <div class="swiper-slide"><x-house-card :house="$house" /></div>
                        @endforeach
                    </x-card-slider>
                </div>
            </div>
        </section>
    @endif

    {{-- Campus facilities --}}
    @if ($facilities->isNotEmpty())
        <section class="py-16" aria-labelledby="facilities-heading">
            <div class="container-pc">
                <x-section-title id="facilities-heading" title="Campus Facilities" :href="route('facilities.index')" link-label="All facilities" />
                <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($facilities as $f)
                        @php($cover = $f->getFirstMedia('cover') ?? $f->getFirstMedia('gallery'))
                        <li class="group relative overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                            @if ($cover)<img src="{{ \App\Support\Img::url($cover, 'card') }}" alt="" class="aspect-[16/10] w-full object-cover" loading="lazy" width="800" height="500">
                            @else<div class="grid aspect-[16/10] place-items-center bg-gradient-to-br from-green-900 to-green-700" aria-hidden="true"><x-facility-icon :category="$f->category" class="h-12 w-12 text-gold-400/90" /></div>@endif
                            <div class="border-t-4 border-gold-400 p-4"><h3 class="font-serif text-lg font-bold text-green-900"><a href="{{ url($f->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $f->name }}</a></h3></div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    {{-- 5. Programmes --}}
    @if ($programmeCards->isNotEmpty())
        <section class="container-pc py-16" aria-labelledby="programmes-heading">
            <x-section-title id="programmes-heading" title="Programmes" :href="route('programmes.index')" link-label="Course combinations" />
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($programmeCards as $p)
                    @php($stat = $stats->first(fn ($s) => strcasecmp($s['programme'], $p->name) === 0))
                    <a href="{{ url($p->path) }}" class="group relative flex flex-col overflow-hidden rounded-sm bg-white p-6 shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <span class="absolute inset-x-0 top-0 z-10 h-1.5 bg-gold-400"></span>
                        @if ($pc = $p->getFirstMedia('cover'))<img src="{{ \App\Support\Img::url($pc, 'thumb') }}" alt="" class="-mx-6 -mt-6 mb-5 aspect-[3/2] max-w-none object-cover" style="width: calc(100% + 3rem)" loading="lazy" width="480" height="320">@endif
                        <h3 class="font-serif text-xl font-bold text-green-900">{{ $p->name }}</h3>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-green-950/80">{{ $p->summary }}</p>
                        @if ($stat)
                            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-green-800">{{ $stat['percent'] }} of admissions</p>
                            <div class="mt-1 h-1.5 rounded-full bg-green-100"><div class="h-1.5 rounded-full bg-gold-400" style="width: {{ min(100, (int) $stat['percent']) }}%"></div></div>
                        @endif
                        <span class="mt-4 text-sm font-semibold text-green-700 group-hover:underline">Subjects &amp; combinations &raquo;</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 6. News row and Events & Announcements --}}
    <x-news-events-section :news="$news" :desk="$desk" :announcements="$announcements" :events="$events" />

    {{-- 7. Prefects --}}
    @if ($board)
        <section class="bg-green-900 py-16 text-white" aria-labelledby="prefects-heading">
            <div class="container-pc grid items-center gap-10 lg:grid-cols-2">
                <div>
                    <p class="eyebrow !text-gold-400">Student leadership</p>
                    <h2 id="prefects-heading" class="mt-2 font-serif text-3xl font-bold md:text-4xl">{{ $board->name }}</h2>
                    <p class="mt-4 max-w-xl text-green-100">{{ $board->description ?: 'Meet the student leaders who serve the College community.' }}</p>
                    <a href="{{ url($board->path) }}" class="btn btn-gold mt-7">Meet the prefects</a>
                </div>
                @if ($boardPhoto)
                    <img src="{{ \App\Support\Img::url($boardPhoto, 'large') }}" alt="{{ $board->name }}" class="w-full rounded-sm shadow-2xl ring-4 ring-gold-400/80" loading="lazy" width="1200" height="800">
                @endif
            </div>
        </section>
    @endif

    {{-- 8. Programmes in the school and video --}}
    <section class="container-pc grid gap-10 py-16 lg:grid-cols-2">
        @if ($programmes->isNotEmpty())
            <div>
                <p class="eyebrow">Programmes &amp; activities</p>
                <h2 class="mt-2 font-serif text-3xl font-bold text-green-900">Shaping individual paths</h2>
                <p class="mt-3 text-green-950/80">Our programmes are designed to shape each student's path for a rapidly changing world.</p>
                <ul class="mt-6 flex flex-wrap gap-2">
                    @foreach ($programmes as $p)
                        <li class="rounded-full bg-white px-4 py-1.5 text-sm font-medium text-green-900 ring-1 ring-green-200">{{ $p }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if ($videoId)
            <div>
                <p class="eyebrow">Watch</p>
                <h2 class="mt-2 font-serif text-3xl font-bold text-green-900">Life at Prempeh</h2>
                <div class="mt-6"><x-video-embed :id="$videoId" title="Life at Prempeh College" /></div>
            </div>
        @endif
    </section>

    {{-- 9. Photo strip --}}
    @if ($hero->count() > 1)
        <section class="container-pc pb-4">
            <h2 class="sr-only">Photos</h2>
            <div class="pc-gallery md:!grid-cols-4">
                @foreach ($hero->skip(1)->take(8) as $img)
                    <figure><img src="{{ \App\Support\Img::url($img, 'card') }}" alt="{{ \App\Support\Img::alt($img, 'Prempeh College students') }}" loading="lazy"></figure>
                @endforeach
            </div>
        </section>
    @endif
</x-layouts.app>
