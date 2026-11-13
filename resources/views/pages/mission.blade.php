@php
    $photos = $page->getMedia('content');
    $banner = $page->getFirstMedia('featured') ?? $photos->get(0);
    $strip = $photos->get(1);
    $values = collect($page->highlights ?? [])->filter(fn ($v) => filled($v['title'] ?? null))->values();
    $sectionLabel = $page->section ? config('site.sections.'.$page->section) : null;
    $desc = $page->seo_description ?: trim(($vision ? 'Vision: '.strip_tags($vision).' ' : '').($mission ? 'Mission: '.strip_tags($mission) : ''));
@endphp
<x-layouts.app :title="$page->seo_title ?: $page->title" :description="$desc ?: null" :image="$banner ? \App\Support\Img::url($banner, 'large') : null">

    {{-- Banner with a Prempeh photo --}}
    <section class="relative overflow-hidden bg-green-950 text-white">
        @if ($banner)<img src="{{ \App\Support\Img::url($banner, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-top opacity-35" fetchpriority="high">@endif
        <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/85 to-green-900/20"></div>
        <div class="container-pc relative py-16 md:py-24">
            <nav aria-label="Breadcrumb" class="text-sm text-green-200">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li>
                    <li aria-hidden="true">/</li><li><a class="hover:text-gold-400" href="{{ url($page->section) }}">{{ $sectionLabel }}</a></li>
                    <li aria-hidden="true">/</li><li aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>
            <p class="eyebrow mt-6 !text-gold-400">{{ $sectionLabel }}</p>
            <h1 class="mt-2 max-w-3xl font-serif text-4xl font-bold leading-tight md:text-6xl">What Prempeh College <span class="text-gold-400">stands for</span></h1>
            <p class="mt-5 max-w-2xl text-lg text-green-100">Our vision, our mission and the values that shape every Premps.</p>
        </div>
        <div class="h-1.5 bg-gold-400"></div>
    </section>

    {{-- Vision and mission --}}
    @if ($vision || $mission)
        <section class="container-pc grid gap-6 py-14 md:grid-cols-2" aria-label="Vision and mission">
            @if ($vision)
                <article class="relative overflow-hidden rounded-sm bg-green-900 p-8 text-white shadow-xl md:p-10">
                    <span class="absolute -right-6 -top-6 font-serif text-[9rem] leading-none text-gold-400/15" aria-hidden="true">&ldquo;</span>
                    <p class="eyebrow !text-gold-400">Our vision</p>
                    <div class="mt-4 font-serif text-2xl font-bold leading-snug md:text-3xl [&_p+p]:mt-3">{!! $vision !!}</div>
                </article>
            @endif
            @if ($mission)
                <article class="relative overflow-hidden rounded-sm border-t-8 border-gold-400 bg-white p-8 shadow-xl ring-1 ring-green-100 md:p-10">
                    <p class="eyebrow">Our mission</p>
                    <div class="mt-4 font-serif text-2xl font-bold leading-snug text-green-900 md:text-3xl [&_p+p]:mt-3">{!! $mission !!}</div>
                </article>
            @endif
        </section>
    @endif

    {{-- Core values --}}
    @if ($values->isNotEmpty())
        <section class="bg-green-50 py-14" aria-labelledby="values-heading">
            <div class="container-pc">
                <x-section-title id="values-heading" title="Our Core Values" />
                @php($cols = [1 => 'lg:grid-cols-1', 2 => 'lg:grid-cols-2', 3 => 'lg:grid-cols-3', 4 => 'lg:grid-cols-4'][min($values->count(), 4)])
                <ol class="mt-10 grid gap-6 sm:grid-cols-2 {{ $cols }}">
                    @foreach ($values as $i => $v)
                        <li class="group relative overflow-hidden rounded-sm bg-white p-7 shadow-md ring-1 ring-green-100 transition hover:-translate-y-1 hover:shadow-xl">
                            <span class="absolute inset-x-0 top-0 h-1.5 bg-gold-400"></span>
                            <span class="font-serif text-5xl font-bold text-gold-400/70 transition group-hover:text-gold-500" aria-hidden="true">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            <h3 class="mt-3 font-serif text-2xl font-bold text-green-900">{{ $v['title'] }}</h3>
                            @if (filled($v['text'] ?? null))<p class="mt-2 text-sm leading-relaxed text-green-950/80">{{ $v['text'] }}</p>@endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    {{-- Anything else an editor adds to the page text appears here --}}
    @if (trim(strip_tags((string) $other)) !== '')
        <section class="container-pc py-12"><div class="prose-pc max-w-4xl">{!! \App\Support\Content::render($other) !!}</div></section>
    @endif

    {{-- Photo band --}}
    @if ($strip)
        <section class="relative overflow-hidden bg-green-950 text-white" aria-label="Excellence in action">
            <img src="{{ \App\Support\Img::url($strip, 'large') }}" alt="{{ \App\Support\Img::alt($strip, 'Prempeh College students with the national championship trophy') }}" class="absolute inset-0 h-full w-full object-cover opacity-30" loading="lazy">
            <div class="container-pc relative py-16 text-center">
                <p class="eyebrow !text-gold-400">Values in action</p>
                <p class="mx-auto mt-3 max-w-3xl font-serif text-2xl font-bold leading-snug md:text-3xl">Champions in the classroom and on the track.</p>
                <a href="{{ route('news.index') }}" class="btn btn-gold mt-7">Read our latest news</a>
            </div>
        </section>
    @endif

    {{-- Where the values live --}}
    <section class="container-pc py-14" aria-labelledby="live-heading">
        <x-section-title id="live-heading" title="See our values lived out" />
        <ul class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([['Our history', url('about/history'), 'From the 1949 pioneers to today.'], ['The house system', route('houses.index'), 'Belonging, competition and service.'], ['Student leadership', route('prefects.index'), 'The prefects who serve the College.'], ['Student life', url('student-life'), 'Teams, clubs and activities.']] as [$t, $u, $b])
                <li class="group relative rounded-sm bg-white p-5 shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                    <h3 class="font-serif text-lg font-bold text-green-900"><a href="{{ $u }}" class="after:absolute after:inset-0 group-hover:underline">{{ $t }}</a></h3>
                    <p class="mt-2 text-sm text-green-950/75">{{ $b }}</p>
                    <p class="mt-3 text-sm font-semibold text-green-700">Explore &raquo;</p>
                </li>
            @endforeach
        </ul>
    </section>
</x-layouts.app>
