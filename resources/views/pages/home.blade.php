@php
    $links = config('site.links');
    $firstSlide = $slides->first()?->getFirstMedia('image');
    $heroImg = $hero->first();
    $aboutImg = \App\Models\Page::where('section', 'about')->where('slug', 'overview')->first()?->getFirstMedia('content');
    $boardPhoto = $board?->getFirstMedia('group_photo') ?? $board?->getFirstMedia('gallery');
    // Photo backgrounds for the closing bands come from the home slider photos (Apply: first slide, Support: the Robofest slide).
    $ctaPhoto = $slides->first()?->getFirstMedia('image');
    $supportPhoto = $slides->first(fn ($sl) => str_contains(strtolower($sl->title), 'robofest'))?->getFirstMedia('image') ?? $slides->skip(1)->first()?->getFirstMedia('image');
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
    <section class="border-b border-green-100 bg-white py-20" aria-labelledby="achievements-heading">
        <div class="container-pc" data-scroll-row>
            <div class="mx-auto max-w-2xl text-center">
                <p class="eyebrow">Honours board</p>
                <h2 id="achievements-heading" class="mt-3 font-serif text-3xl font-bold text-green-900 md:text-4xl">The best school in Ghana</h2>
                <div class="mx-auto mt-5 flex items-center justify-center gap-3 text-gold-500" aria-hidden="true">
                    <span class="h-px w-16 bg-gold-500/50"></span><span class="text-xs">&#9670;</span><span class="h-px w-16 bg-gold-500/50"></span>
                </div>
                <p class="mt-4 font-serif text-base italic text-green-950/60">A record of excellence since {{ config('site.founded') }}</p>
            </div>
            <div class="mt-8 flex justify-end gap-2" aria-label="Scroll achievements">
                <button type="button" data-scroll-prev aria-label="Previous achievements" class="grid h-10 w-10 place-items-center border border-green-900/20 bg-white text-lg text-green-900 transition hover:bg-green-900 hover:text-white disabled:cursor-default disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-green-900">&larr;</button>
                <button type="button" data-scroll-next aria-label="Next achievements" class="grid h-10 w-10 place-items-center border border-green-900/20 bg-white text-lg text-green-900 transition hover:bg-green-900 hover:text-white disabled:cursor-default disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-green-900">&rarr;</button>
            </div>
            <ul data-scroll-track tabindex="0" aria-label="School achievements" class="-mx-4 mt-4 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-4 pb-8 pt-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($facts['achievements'] as $a)
                    <li class="group relative flex w-72 shrink-0 snap-start flex-col items-center border border-gold-500/40 bg-[#fbf8f0] px-8 py-12 text-center shadow-[0_1px_0_rgba(0,0,0,.04)] transition duration-300 before:pointer-events-none before:absolute before:inset-2 before:border before:border-gold-500/30 hover:-translate-y-1 hover:shadow-xl hover:shadow-green-950/10 md:w-80">
                        <p class="font-serif text-6xl font-bold leading-none text-green-900">{{ $a['value'] }}</p>
                        <span class="my-6 flex items-center gap-2 text-gold-500" aria-hidden="true"><span class="h-px w-8 bg-gold-500/60"></span><span class="text-[0.6rem]">&#9670;</span><span class="h-px w-8 bg-gold-500/60"></span></span>
                        <p class="text-[0.8rem] font-semibold uppercase leading-relaxed tracking-[0.16em] text-green-950">{{ $a['label'] }}</p>
                        <p class="mt-3 font-serif text-sm italic leading-relaxed text-green-950/65">{{ $a['detail'] }}</p>
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
    @if (config('site.show_facilities_on_home') && $facilities->isNotEmpty())
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
        <section class="container-pc py-16" aria-labelledby="programmes-heading" data-scroll-row>
            <x-section-title id="programmes-heading" title="Programmes" :href="route('programmes.index')" link-label="Course combinations" />
            <div class="mt-5 flex justify-end gap-2" aria-label="Scroll programmes">
                <button type="button" data-scroll-prev aria-label="Previous programmes" class="grid h-10 w-10 place-items-center rounded-full border border-green-200 bg-white text-lg text-green-900 shadow-sm transition hover:bg-green-900 hover:text-white disabled:cursor-default disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-green-900">&larr;</button>
                <button type="button" data-scroll-next aria-label="Next programmes" class="grid h-10 w-10 place-items-center rounded-full border border-green-200 bg-white text-lg text-green-900 shadow-sm transition hover:bg-green-900 hover:text-white disabled:cursor-default disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-green-900">&rarr;</button>
            </div>
            <div data-scroll-track tabindex="0" aria-label="Programmes" class="-mx-4 mt-4 flex snap-x snap-mandatory gap-6 overflow-x-auto scroll-smooth px-4 pb-6 pt-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($programmeCards as $p)
                    <a href="{{ url($p->path) }}" class="group relative flex w-72 shrink-0 snap-start flex-col overflow-hidden rounded-sm bg-white p-6 shadow-md lg:w-[19.5rem] ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <span class="absolute inset-x-0 top-0 z-10 h-1.5 bg-gold-400"></span>
                        @if ($pc = $p->getFirstMedia('cover'))<img src="{{ \App\Support\Img::url($pc, 'thumb') }}" alt="" class="-mx-6 -mt-6 mb-5 aspect-[3/2] max-w-none object-cover" style="width: calc(100% + 3rem)" loading="lazy" width="480" height="320">@endif
                        <h3 class="font-serif text-xl font-bold text-green-900">{{ $p->name }}</h3>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-green-950/80">{{ $p->summary }}</p>
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

    {{-- 8. NSMQ photo, video and programmes --}}
    <section class="bg-white py-16" aria-labelledby="proud-heading">
        <div class="container-pc">
            <div class="grid gap-10 lg:grid-cols-2">
                <figure>
                    <p class="eyebrow">Leaders of Ghana</p>
                    <h2 id="proud-heading" class="mt-2 font-serif text-3xl font-bold text-green-900">NSMQ champions</h2>
                    <a href="{{ url('student-life/nsmq-team') }}" class="group mt-6 block overflow-hidden rounded-lg shadow-lg ring-1 ring-green-100">
                        <span class="relative block aspect-video overflow-hidden bg-green-950">
                            <img src="{{ asset('images/nsmq-2021-trophy-otumfuo.jpg') }}" alt="Prempeh College NSMQ team presenting the championship trophy to Otumfuo Osei Tutu II" loading="lazy" width="696" height="464"
                                 class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-105">
                            <span class="absolute inset-0 bg-gradient-to-t from-green-950/75 via-transparent to-transparent" aria-hidden="true"></span>
                            <span class="absolute bottom-0 left-0 right-0 p-4 text-sm font-semibold text-white">NSMQ 2021: Prempeh College presents the trophy to Otumfuo</span>
                        </span>
                        <span class="block h-1.5 bg-gold-400"></span>
                    </a>
                </figure>
                @if ($videoId)
                    <div>
                        <p class="eyebrow">Watch</p>
                        <h2 class="mt-2 font-serif text-3xl font-bold text-green-900">Life at Prempeh</h2>
                        <div class="mt-6 overflow-hidden rounded-lg shadow-lg ring-1 ring-green-100">
                            <x-video-embed :id="$videoId" title="Life at Prempeh College" />
                            <span class="block h-1.5 bg-gold-400"></span>
                        </div>
                    </div>
                @endif
            </div>

            @if ($programmes->isNotEmpty())
                <div class="relative isolate mt-14 overflow-hidden rounded-lg bg-green-950 px-6 py-14 text-center text-white shadow-lg md:px-12">
                    <img src="{{ asset('images/programmes-bg.webp') }}" alt="" loading="lazy" class="absolute inset-0 -z-10 h-full w-full object-cover">
                    <div class="absolute inset-0 -z-10 bg-green-950/80"></div>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-br from-green-900/60 via-transparent to-green-950/70"></div>
                    <p class="eyebrow !text-gold-400">Programmes &amp; activities</p>
                    <h2 class="mt-2 font-serif text-3xl font-bold md:text-4xl">Shaping individual paths</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-green-50/90">Our programmes are designed to shape each student's path for a rapidly changing world.</p>
                    <ul class="mt-8 flex flex-wrap justify-center gap-2.5">
                        @foreach ($programmes as $p)
                            <li class="rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-sm font-medium text-white backdrop-blur-sm transition hover:bg-gold-400 hover:text-green-950">{{ $p }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- 9. Photo strip: one scrolling row, drifts slowly on its own and stops when touched or hovered --}}
    @if ($strip->count() > 1)
        <section class="py-10" aria-labelledby="photos-heading" data-scroll-row>
            <div class="container-pc flex items-end justify-between gap-4">
                <h2 id="photos-heading" class="font-serif text-2xl font-bold text-green-900 md:text-3xl">Life in pictures</h2>
                <div class="flex gap-2" aria-label="Scroll photos">
                    <button type="button" data-scroll-prev aria-label="Previous photos" class="grid h-10 w-10 place-items-center rounded-full border border-green-200 bg-white text-lg text-green-900 shadow-sm transition hover:bg-green-900 hover:text-white disabled:cursor-default disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-green-900">&larr;</button>
                    <button type="button" data-scroll-next aria-label="Next photos" class="grid h-10 w-10 place-items-center rounded-full border border-green-200 bg-white text-lg text-green-900 shadow-sm transition hover:bg-green-900 hover:text-white disabled:cursor-default disabled:opacity-30 disabled:hover:bg-white disabled:hover:text-green-900">&rarr;</button>
                </div>
            </div>
            <div data-scroll-track data-autoscroll tabindex="0" aria-label="Photo gallery" class="mt-5 flex gap-4 overflow-x-auto px-4 pb-4 md:px-8 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                @foreach ($strip as $img)
                    <figure class="m-0 w-64 shrink-0 overflow-hidden rounded-lg shadow-md ring-1 ring-green-100 md:w-80">
                        <img src="{{ \App\Support\Img::url($img, 'card') }}" data-full="{{ \App\Support\Img::url($img, 'large') }}" alt="{{ \App\Support\Img::alt($img, 'Prempeh College students') }}" loading="lazy" draggable="false"
                             class="aspect-[4/3] w-full cursor-zoom-in object-cover transition duration-500 hover:scale-105">
                    </figure>
                @endforeach
            </div>
        </section>
    @endif

    {{-- 10. Call to action and support --}}
    <div class="-mb-16">
        <section class="relative isolate overflow-hidden bg-gradient-to-br from-green-800 via-green-900 to-green-950 py-16 text-center text-white" aria-labelledby="cta-heading">
            @if ($ctaPhoto)
                <img src="{{ \App\Support\Img::url($ctaPhoto, 'large') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 -z-10 bg-green-950/80"></div>
                <div class="absolute inset-0 -z-10 bg-gradient-to-br from-green-900/60 via-transparent to-green-950/70"></div>
            @endif
            <div class="container-pc">
                <h2 id="cta-heading" class="font-serif text-4xl font-bold md:text-5xl">Start Your Journey Today</h2>
                <p class="mx-auto mt-5 max-w-xl text-xl font-light text-green-50">Join Prempeh College and become one of the Amanfoɔ shaping Ghana&rsquo;s future.</p>
                <div class="mt-9 flex flex-wrap justify-center gap-4">
                    <a href="{{ url(config('site.links.admission') && ! preg_match('~^https?://~', config('site.links.admission')) ? config('site.links.admission') : 'academics/overview') }}" class="inline-flex rounded-full bg-white px-8 py-4 font-bold text-green-900 shadow-xl transition hover:bg-gold-100">Apply for Admission</a>
                    <a href="{{ route('contact') }}" class="inline-flex rounded-full border-2 border-white/50 px-8 py-4 font-bold text-white transition hover:border-white hover:bg-white/10">Contact Us</a>
                </div>
            </div>
        </section>
        <section class="relative isolate overflow-hidden border-t-4 border-gold-400 bg-gradient-to-br from-green-900 via-green-950 to-green-900 py-20 pb-32 text-center text-white" aria-labelledby="support-heading">
            @if ($supportPhoto)
                <img src="{{ \App\Support\Img::url($supportPhoto, 'large') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 -z-10 bg-green-950/85"></div>
                <div class="absolute inset-0 -z-10 bg-gradient-to-br from-green-800/40 via-transparent to-green-950/70"></div>
                <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_bottom_right,rgba(255,197,58,.28),transparent_55%)]"></div>
            @endif
            <div class="container-pc">
                <p class="inline-block rounded-full bg-gold-400 px-5 py-1.5 text-xs font-extrabold uppercase tracking-[0.16em] text-green-950 shadow-md">Support our mission</p>
                <h2 id="support-heading" class="mt-5 font-serif text-4xl font-bold tracking-tight md:text-5xl">Partner With Us in <span class="text-gold-400">Excellence</span></h2>
                <p class="mx-auto mt-5 max-w-2xl text-lg font-light text-green-50">Prempeh College is growing. Your support helps us improve our facilities, support our students and continue a legacy of character and learning.</p>
                <a href="{{ route('contact') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-gold-400 px-9 py-4 font-bold text-green-950 shadow-xl shadow-black/30 ring-4 ring-gold-400/25 transition hover:-translate-y-0.5 hover:bg-gold-300 hover:ring-gold-300/40">Support the College <svg aria-hidden="true" class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="currentColor"><path d="M12 21s-7.5-4.6-9.6-9.2C.9 8.4 2.6 5 6 5c2 0 3.4 1.1 4.2 2.4h.1C11.1 6.1 12.5 5 14.5 5 17.9 5 19.6 8.4 18.1 11.8 16 16.400 12 21 12 21z"/></svg></a>
                <p class="mt-6 text-sm text-green-100">Or support by spreading the word <strong class="text-gold-400">#SupportPrempeh</strong></p>
            </div>
        </section>
    </div>
</x-layouts.app>
