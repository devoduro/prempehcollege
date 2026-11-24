@php
    $links = config('site.links');
    $firstSlide = $slides->first()?->getFirstMedia('image');
    $heroImg = $hero->first();
    $aboutImg = \App\Models\Page::where('section', 'about')->where('slug', 'overview')->first()?->getFirstMedia('content');
    $boardPhoto = $board?->getFirstMedia('group_photo') ?? $board?->getFirstMedia('gallery');
@endphp
<x-layouts.app :image="$firstSlide ? \App\Support\Img::url($firstSlide, 'large') : ($heroImg ? \App\Support\Img::url($heroImg, 'large') : null)">

    {{-- 1. Hero (photos managed in the admin: Site > Home slider) with the headline numbers --}}
    @php
        $facts = config('site.facts');
        $heroStats = [
            ['value' => (now()->year - config('site.founded')).'+', 'label' => 'Years of excellence'],
            ['value' => number_format(floor($facts['students'] / 100) * 100).'+', 'label' => 'Students'],
            ['value' => $facts['houses'], 'label' => 'Houses'],
            ['value' => $programmeCards->count(), 'label' => 'Programmes'],
        ];
    @endphp
    <x-hero-slider :slides="$slides" :fallback="$heroImg ? \App\Support\Img::url($heroImg, 'large') : null" :stats="$heroStats" />

    {{-- 2. Achievements (source: Wikipedia, see config/site.php) --}}
    <section class="border-b border-green-100 bg-white py-12" aria-labelledby="achievements-heading">
        <div class="container-pc">
            <p class="eyebrow text-center">Pride of Kumasi</p>
            <h2 id="achievements-heading" class="mt-2 text-center font-serif text-3xl font-bold text-green-900">Champions in the classroom and beyond</h2>
            <ul class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach ($facts['achievements'] as $a)
                    <li class="border-t-4 border-gold-400 bg-green-50 p-5 text-center">
                        <p class="font-serif text-4xl font-bold text-green-900">{{ $a['value'] }}</p>
                        <p class="mt-2 text-sm font-bold text-green-900">{{ $a['label'] }}</p>
                        <p class="mt-1 text-xs text-green-950/70">{{ $a['detail'] }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- 3. About us --}}
    <section class="container-pc grid items-center gap-10 py-16 lg:grid-cols-2" aria-labelledby="about-heading">
        <div>
            <p class="eyebrow">About us</p>
            <h2 id="about-heading" class="mt-2 font-serif text-3xl font-bold text-green-900 md:text-4xl">A tradition of character, learning and service</h2>
            <div class="prose-pc mt-5 !text-base">
                <p>Prempeh College is an {{ strtolower($facts['type']) }} in Kumasi, Ashanti Region. It opened on 5 February {{ config('site.founded') }}, founded by {{ $facts['founding_bodies'] }}, and is named after {{ $facts['namesake'] }}, who donated the land.</p>
                <p>The campus was designed in 1948 by the British modernist architects {{ $facts['architects'] }}. Today about {{ number_format(floor($facts['students'] / 100) * 100) }} students live and learn here under the motto &ldquo;{{ $facts['motto'] }}&rdquo;, wearing the school colours of {{ strtolower($facts['colours']) }}, and are known as the <em>{{ $facts['nickname'] }}</em>.</p>
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

    {{-- 10. Call to action and support --}}
    <div class="-mb-16">
        <section class="bg-gradient-to-br from-green-800 via-green-900 to-green-950 py-16 text-center text-white" aria-labelledby="cta-heading">
            <div class="container-pc">
                <h2 id="cta-heading" class="font-serif text-4xl font-bold md:text-5xl">Start Your Journey Today</h2>
                <p class="mx-auto mt-5 max-w-xl text-xl font-light text-green-50">Join Prempeh College and become one of the Amanfoɔ shaping Ghana&rsquo;s future.</p>
                <div class="mt-9 flex flex-wrap justify-center gap-4">
                    <a href="{{ url(config('site.links.admission') && ! preg_match('~^https?://~', config('site.links.admission')) ? config('site.links.admission') : 'academics/overview') }}" class="inline-flex rounded-full bg-white px-8 py-4 font-bold text-green-900 shadow-xl transition hover:bg-gold-100">Apply for Admission</a>
                    <a href="{{ route('contact') }}" class="inline-flex rounded-full border-2 border-white/50 px-8 py-4 font-bold text-white transition hover:border-white hover:bg-white/10">Contact Us</a>
                </div>
            </div>
        </section>
        <section class="bg-gradient-to-br from-gold-400 via-gold-500 to-gold-400 py-16 pb-28 text-center text-green-950" aria-labelledby="support-heading">
            <div class="container-pc">
                <p class="inline-block rounded-full bg-white/25 px-4 py-1 text-xs font-extrabold uppercase tracking-[0.12em]">Support our mission</p>
                <h2 id="support-heading" class="mt-4 font-serif text-4xl font-bold md:text-5xl">Partner With Us in Excellence</h2>
                <p class="mx-auto mt-5 max-w-2xl text-lg">Prempeh College is growing. Your support helps us improve our facilities, support our students and continue a legacy of character and learning.</p>
                <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-ink px-8 py-4 font-bold text-white shadow-xl transition hover:bg-green-950">Support the College <svg aria-hidden="true" class="h-4 w-4 text-red-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.6 5 6 5c2 0 3.4 1.1 4.2 2.4h.1C11.1 6.1 12.5 5 14.5 5 17.9 5 19.6 8.4 18.1 11.8 16 16.400 12 21 12 21z"/></svg></a>
                <p class="mt-5 text-sm">Or support by spreading the word <strong>#SupportPrempeh</strong></p>
            </div>
        </section>
    </div>
</x-layouts.app>
