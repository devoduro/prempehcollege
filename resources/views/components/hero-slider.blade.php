@props(['slides', 'fallback' => null, 'stats' => []])
{{-- Home hero. Each slide (managed in the admin: Site > Home slider) is a photo under a green overlay with its own eyebrow, title, subtitle and button.
     Photos zoom slowly, text rises in, and a gold bar shows the time left before the next slide. The stats row sits underneath. --}}
@php
    $apply = config('site.links.admission');
    $applyUrl = $apply && preg_match('~^https?://~', $apply) ? $apply : url($apply ?: 'academics/overview');
    // Last words of a title are set in gold, like "of Education" in the design.
    $goldTitle = function (string $title) {
        $words = preg_split('/\s+/', trim($title));
        $n = count($words);
        if ($n < 2) return e($title);
        $split = $n >= 4 ? $n - 2 : $n - 1;
        return e(implode(' ', array_slice($words, 0, $split))).' <span class="text-gold-400">'.e(implode(' ', array_slice($words, $split))).'</span>';
    };
    $btnUrl = fn ($u) => preg_match('~^https://~', $u) ? $u : url($u);
@endphp
<section aria-roledescription="carousel" aria-label="Welcome to {{ config('site.name') }}" class="relative isolate overflow-hidden bg-green-950 text-white">
    <div class="hero-slider swiper" data-hero-slider>
        <div class="swiper-wrapper">
            @forelse ($slides as $i => $slide)
                @php($img = $slide->getFirstMedia('image'))
                <div class="swiper-slide relative overflow-hidden" role="group" aria-label="Slide {{ $i + 1 }} of {{ $slides->count() }}">
                    @if ($img)
                        <img src="{{ \App\Support\Img::url($img, 'large') }}" alt="" class="hero-photo absolute inset-0 h-full w-full object-cover"
                             width="1600" height="900" @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif>
                    @endif
                    @include('components.partials.hero-overlay')
                    <div class="container-pc relative flex min-h-[32rem] flex-col items-center justify-center px-14 py-16 text-center md:min-h-[40rem]">
                        <div class="hero-text flex flex-col items-center">
                            <p class="text-base font-bold uppercase tracking-[0.12em] text-gold-400 md:text-xl">{{ $slide->eyebrow ?: 'Welcome to '.config('site.name') }}</p>
                            @if ($i === 0)
                                <h1 class="mt-3 max-w-4xl font-serif text-4xl font-bold leading-[1.12] md:text-7xl">{!! $goldTitle($slide->title) !!}</h1>
                            @else
                                <h2 class="mt-3 max-w-4xl font-serif text-4xl font-bold leading-[1.12] md:text-7xl">{!! $goldTitle($slide->title) !!}</h2>
                            @endif
                            @if ($slide->subtitle)<p class="mt-6 max-w-2xl text-lg font-light text-green-50 md:text-2xl">{{ $slide->subtitle }}</p>@endif
                            <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                                @if ($slide->button_label && $slide->button_url)
                                    <a href="{{ $btnUrl($slide->button_url) }}" class="inline-flex items-center gap-2 rounded-full bg-gold-400 px-8 py-4 font-bold text-green-950 shadow-lg shadow-black/20 transition hover:bg-gold-300">{{ $slide->button_label }} <span aria-hidden="true">&rarr;</span></a>
                                @endif
                                <a href="{{ $applyUrl }}" class="inline-flex items-center rounded-full border-2 border-white/90 px-8 py-4 font-bold text-white transition hover:bg-white hover:text-green-900">Apply Now</a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="swiper-slide relative overflow-hidden">
                    @if ($fallback)<img src="{{ $fallback }}" alt="" class="hero-photo absolute inset-0 h-full w-full object-cover" width="1600" height="900" fetchpriority="high">@endif
                    @include('components.partials.hero-overlay')
                    <div class="container-pc relative flex min-h-[32rem] flex-col items-center justify-center px-14 py-16 text-center md:min-h-[40rem]">
                        <div class="hero-text flex flex-col items-center">
                            <p class="text-base font-bold uppercase tracking-[0.12em] text-gold-400 md:text-xl">Welcome to</p>
                            <h1 class="mt-3 font-serif text-5xl font-bold leading-[1.1] md:text-7xl">Prempeh <span class="text-gold-400">College</span></h1>
                            <p class="mt-6 max-w-2xl text-lg font-light text-green-50 md:text-2xl">Shaping Ghana&rsquo;s leaders in Kumasi since {{ config('site.founded') }}</p>
                            <div class="mt-9"><a href="{{ $applyUrl }}" class="inline-flex rounded-full bg-gold-400 px-8 py-4 font-bold text-green-950 transition hover:bg-gold-300">Apply Now</a></div>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev" aria-label="Previous slide"></div>
        <div class="swiper-button-next" aria-label="Next slide"></div>
        <div class="absolute inset-x-0 bottom-0 z-10 h-1 bg-white/15" aria-hidden="true"><div class="hero-progress h-full origin-left bg-gold-400"></div></div>
    </div>

    @if (count($stats))
        <dl class="relative border-t border-white/10 bg-green-950/95">
            <div class="container-pc grid grid-cols-2 py-6 text-center md:grid-cols-4 md:py-9">
                @foreach ($stats as $stat)
                    <div class="flex flex-col-reverse px-3 py-3 md:py-0 @if (! $loop->first) md:border-l md:border-white/20 @endif">
                        <dt class="mt-1 text-xs font-medium uppercase tracking-[0.12em] text-gold-400 md:text-sm">{{ $stat['label'] }}</dt>
                        <dd class="font-sans text-3xl font-extrabold md:text-5xl">{{ $stat['value'] }}</dd>
                    </div>
                @endforeach
            </div>
        </dl>
    @endif
    <div class="h-1.5 bg-gold-400"></div>
</section>
