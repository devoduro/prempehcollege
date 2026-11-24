@php
    $sectionLabel = $page->section ? config('site.sections.'.$page->section) : null;
    $crumbs = $sectionLabel ? [$sectionLabel => url($page->section), $page->title => null] : [$page->title => null];
    $featured = $page->getFirstMedia('featured');
    // Banner photo: the featured image, otherwise the first photo used in the page body.
    $banner = $featured ?? $page->getFirstMedia('content');
    $rendered = \App\Support\Content::withToc($page->body);
    $toc = $rendered['toc'];
    $isPhotoPage = trim(strip_tags((string) $page->body)) === '' && str_contains((string) $page->body, '<img');
    $photoCount = substr_count((string) $page->body, '<img');
    $hasProfiles = $people->isNotEmpty();
    // A page of people is described by who is on it, which also reads better in search results.
    $peopleSummary = $hasProfiles ? $page->title.': '.$people->take(4)->map(fn ($p) => $p->name.($p->role ? ' ('.$p->role.')' : ''))->join(', ').($people->count() > 4 ? ' and more.' : '.') : null;
    if ($hasProfiles) {
        $banner = null;
    }
@endphp
<x-layouts.app :title="$page->seo_title ?: $page->title" :description="$page->seo_description ?: ($peopleSummary ?: \Illuminate\Support\Str::limit(strip_tags((string) $page->body), 160))"
    :image="$banner ? \App\Support\Img::url($banner, 'large') : null">

    {{-- Banner: dark green panel with a faded photo behind the title --}}
    <section class="relative overflow-hidden bg-green-900 text-white">
        @if ($banner && ! $isPhotoPage)
            <img src="{{ \App\Support\Img::url($banner, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-20" fetchpriority="high">
        @endif
        <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-900/90 to-green-800/60"></div>
        <div class="container-pc relative py-12 md:py-16">
            <nav aria-label="Breadcrumb" class="text-sm text-green-200">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li>
                    @foreach ($crumbs as $label => $url)
                        <li aria-hidden="true">/</li>
                        <li>@if ($url)<a class="hover:text-gold-400" href="{{ $url }}">{{ $label }}</a>@else<span aria-current="page">{{ $label }}</span>@endif</li>
                    @endforeach
                </ol>
            </nav>
            @if ($sectionLabel)<p class="eyebrow mt-6 !text-gold-400">{{ $sectionLabel }}</p>@endif
            <h1 class="mt-2 max-w-4xl font-serif text-3xl font-bold leading-tight md:text-5xl">{{ $page->title }}</h1>
        </div>
        <div class="h-1.5 bg-gold-400"></div>
    </section>

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_18rem]">
        <article class="min-w-0">
            @if ($featured && ! $isPhotoPage)
                <img src="{{ \App\Support\Img::url($featured, 'large') }}" alt="{{ \App\Support\Img::alt($featured) }}" class="mb-8 w-full rounded-sm shadow-lg">
            @endif

            {{-- Table of contents for long pages --}}
            @if (count($toc) >= 3)
                <nav aria-label="On this page" class="mb-8 rounded-sm border-l-4 border-gold-400 bg-white p-5 shadow-sm ring-1 ring-green-100">
                    <p class="text-xs font-bold uppercase tracking-widest text-gold-600">On this page</p>
                    <ol class="mt-2 grid gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                        @foreach ($toc as $item)<li><a class="text-green-800 hover:underline" href="#{{ $item['id'] }}">{{ $item['text'] }}</a></li>@endforeach
                    </ol>
                </nav>
            @endif

            @if ($hasProfiles && $groupKind === 'timeline')
                {{-- Timeline (e.g. past headmasters): intro text, then each person in order, then any archive portraits --}}
                @php([$top, $bottom] = array_pad(explode('<h2 id="archive-portraits"', $rendered['html'], 2), 2, null))
                @if (trim(strip_tags((string) $top)) !== '')<div class="prose-pc mb-10">{!! $top !!}</div>@endif

                <ol class="relative ml-3 space-y-8 border-l-4 border-gold-400/60 pl-8" aria-label="{{ $page->title }}">
                    @foreach ($people as $person)
                        <li class="relative">
                            <span class="absolute -left-[2.65rem] top-2 h-5 w-5 rounded-full border-4 border-white bg-green-800 shadow ring-2 ring-gold-400" aria-hidden="true"></span>
                            @if ($person->role)<p class="inline-block rounded-sm bg-green-900 px-3 py-1 text-xs font-bold uppercase tracking-widest text-gold-400">{{ $person->role }}</p>@endif
                            <h3 class="mt-2 font-serif text-xl font-bold text-green-900">{{ $person->name }}</h3>
                            @if ($person->bio)
                                <p class="mt-2 leading-relaxed text-green-950/85">{{ $person->bio }}</p>
                            @else
                                <p class="mt-2 text-sm italic text-green-950/50">More details will be added.</p>
                            @endif
                        </li>
                    @endforeach
                </ol>

                @if ($bottom)<div class="prose-pc mt-12"><h2 id="archive-portraits"{!! $bottom !!}</div>@endif
            @elseif ($hasProfiles && $people->count() === 1)
                {{-- One person (e.g. the Board Chairman): a spotlight with the full portrait, role and page text --}}
                @php($one = $people->first())
                @php($onePhoto = $one->getFirstMedia('photo'))
                <section class="overflow-hidden rounded-sm bg-white shadow-lg ring-1 ring-green-100 md:grid md:grid-cols-[minmax(0,22rem)_1fr]" aria-label="{{ $one->name }}">
                    @if ($onePhoto)
                        <img src="{{ \App\Support\Img::url($onePhoto, 'large') }}" data-full="{{ \App\Support\Img::url($onePhoto, 'large') }}" alt="{{ $one->name }}, {{ $one->role }}" class="h-full w-full cursor-zoom-in object-cover object-top" width="1100" height="1505">
                    @endif
                    <div class="flex flex-col justify-center border-t-4 border-gold-400 p-6 md:border-l-4 md:border-t-0 md:p-10">
                        <p class="eyebrow">{{ $one->role }}</p>
                        <h2 class="mt-2 font-serif text-3xl font-bold text-green-900 md:text-4xl">{{ $one->name }}</h2>
                        @if (trim(strip_tags((string) $page->body)) !== '')<div class="prose-pc mt-4 !text-base">{!! $rendered['html'] !!}</div>@endif
                        @if ($one->bio)<p class="mt-4 leading-relaxed text-green-950/85">{{ $one->bio }}</p>@endif
                        <p class="mt-6 text-sm text-green-950/60">Other members of the Board will be added here as they are confirmed.</p>
                    </div>
                </section>

                {{-- Where the Board sits among the College's leadership --}}
                @php($related = $siblings->reject(fn ($s) => $s->id === $page->id)->take(4))
                @if ($related->isNotEmpty())
                    <h2 class="mt-10 font-serif text-xl font-bold text-green-900">Meet the rest of the College leadership</h2>
                    <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($related as $s)
                            <li class="rounded-sm bg-green-900 p-5 text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-xl">
                                <a href="{{ url($s->path) }}" class="font-serif text-lg font-bold text-gold-400 hover:underline">{{ $s->title }} &raquo;</a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            @elseif ($hasProfiles)
                <p class="mb-5 text-sm text-green-950/60">{{ $people->count() }} {{ \Illuminate\Support\Str::plural('person', $people->count()) }} &middot; select a photo to see the full portrait.</p>
                <ul class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3" aria-label="{{ $page->title }}">
                    @foreach ($people as $i => $person)
                        {{-- The head of a body (first person of Management, or a lone chairman) is featured across two columns --}}
                        <x-profile-card :person="$person" :lead="$people->count() === 1" />
                    @endforeach
                </ul>
                @if (trim(strip_tags((string) $page->body)) !== '')
                    <div class="prose-pc mt-10">{!! $rendered['html'] !!}</div>
                @endif
            @else
                @if ($isPhotoPage && $photoCount > 1)
                    <p class="mb-4 text-sm text-green-950/60">{{ $photoCount }} photos &middot; select a photo to enlarge it.</p>
                @endif
                <div class="prose-pc {{ $isPhotoPage ? 'is-photo-page' : '' }} {{ $isPhotoPage && $photoCount === 1 ? 'is-single' : '' }}">
                    {!! $rendered['html'] !!}
                </div>
            @endif
        </article>

        <aside class="space-y-8 lg:sticky lg:top-28 lg:self-start" aria-label="Related">
            @if ($siblings->count() > 1)
                <nav aria-label="{{ $sectionLabel }} pages">
                    <h2 class="font-serif text-lg font-bold text-green-900">{{ $sectionLabel }}</h2>
                    <ul class="mt-3 space-y-1 border-l-2 border-green-100">
                        @foreach ($siblings as $s)
                            <li>
                                <a href="{{ url($s->path) }}" @if ($s->id === $page->id) aria-current="page" @endif
                                   class="-ml-0.5 block border-l-2 py-1.5 pl-4 {{ $s->id === $page->id ? 'border-gold-400 font-semibold text-green-900' : 'border-transparent text-green-950/70 hover:border-green-300 hover:text-green-900' }}">{{ $s->title }}</a>
                            </li>
                        @endforeach
                    </ul>
                </nav>
            @endif
            <div class="rounded-sm bg-green-900 p-5 text-white shadow-md">
                <h2 class="font-serif text-lg font-bold text-gold-400">Explore the College</h2>
                <ul class="mt-3 space-y-1.5 text-sm">
                    <li><a class="hover:text-gold-300" href="{{ route('houses.index') }}">Our houses &raquo;</a></li>
                    <li><a class="hover:text-gold-300" href="{{ route('programmes.index') }}">Programmes &amp; combinations &raquo;</a></li>
                    <li><a class="hover:text-gold-300" href="{{ route('prefects.index') }}">Prefects &raquo;</a></li>
                    <li><a class="hover:text-gold-300" href="{{ route('contact') }}">Contact us &raquo;</a></li>
                </ul>
            </div>
        </aside>
    </div>

    @if ($hasProfiles)
        {{-- Structured data so search engines can show who is on the page --}}
        <script type="application/ld+json" nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $page->title,
            'itemListElement' => $people->values()->map(fn ($p, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1,
                'item' => array_filter(['@type' => 'Person', 'name' => $p->name, 'jobTitle' => $p->role, 'worksFor' => ['@type' => 'HighSchool', 'name' => config('site.name')]]),
            ])->all(),
        ], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif
</x-layouts.app>
