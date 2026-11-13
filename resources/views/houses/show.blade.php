@php
    $crest = $house->getFirstMedia('crest');
    $cover = $house->getFirstMedia('cover');
    $gallery = $house->getMedia('gallery');
    $colour = $house->cssColour();
    $years = $house->years;
    $now = $years->first();
    $housemaster = $now?->housemaster ?: $house->housemaster;
    $hasAbout = trim(strip_tags((string) $house->about)) !== '';
    $hasHistory = trim(strip_tags((string) $house->history)) !== '';
    // Section links appear only for sections that have something to show.
    $nav = array_filter([
        $hasAbout ? ['about', 'About'] : null,
        $hasHistory ? ['history', 'History'] : null,
        ($housemaster || $now?->house_prefect) ? ['leadership', 'Leadership'] : null,
        $years->isNotEmpty() ? ['years', 'Year by year'] : null,
        $gallery->isNotEmpty() ? ['gallery', 'Gallery'] : null,
    ]);
@endphp
<x-layouts.app :title="$house->name" :description="$house->summary" :image="$cover ? \App\Support\Img::url($cover, 'large') : null">
    <section class="relative overflow-hidden bg-green-950 text-white">
        @if ($cover)<img src="{{ \App\Support\Img::url($cover, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30" fetchpriority="high">@endif
        <div class="absolute inset-0" style="background: linear-gradient(90deg, #032a0d 35%, {{ $colour }}66)"></div>
        <div class="container-pc relative py-14 md:py-20">
            <nav aria-label="Breadcrumb" class="text-sm text-green-200">
                <ol class="flex flex-wrap gap-2"><li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li><li aria-hidden="true">/</li><li><a class="hover:text-gold-400" href="{{ route('houses.index') }}">Houses</a></li><li aria-hidden="true">/</li><li aria-current="page">{{ $house->name }}</li></ol>
            </nav>
            <div class="mt-6 flex flex-wrap items-center gap-6">
                <div class="grid h-24 w-24 place-items-center overflow-hidden rounded-full bg-white shadow-xl ring-4" style="--tw-ring-color: {{ $colour }}">
                    @if ($crest)<img src="{{ \App\Support\Img::url($crest, 'card') }}" alt="{{ $house->name }} crest" class="h-full w-full object-contain p-2">
                    @else<span class="font-serif text-4xl font-bold" style="color: {{ $colour }}" aria-hidden="true">{{ mb_substr($house->name, 0, 1) }}</span>@endif
                </div>
                <div>
                    <p class="eyebrow !text-gold-400">House</p>
                    <h1 class="font-serif text-4xl font-bold md:text-5xl">{{ $house->name }}</h1>
                    @if ($house->motto)<p class="mt-2 text-lg italic text-green-100">&ldquo;{{ $house->motto }}&rdquo;</p>@endif
                </div>
            </div>
        </div>
        <div class="h-1.5" style="background-color: {{ $colour }}"></div>
    </section>

    {{-- Section links --}}
    @if (count($nav) > 1)
        <nav aria-label="On this page" class="sticky top-20 z-30 border-b border-green-100 bg-white/95 backdrop-blur">
            <ul class="container-pc flex gap-1 overflow-x-auto py-2 text-sm font-semibold">
                @foreach ($nav as [$id, $label])
                    <li><a href="#{{ $id }}" class="block whitespace-nowrap rounded px-3 py-1.5 text-green-900 hover:bg-green-50">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>
    @endif

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_18rem]">
        <div class="min-w-0 space-y-14">
            @if ($house->summary)<p class="text-xl leading-relaxed text-green-950">{{ $house->summary }}</p>@endif

            @if ($hasAbout)
                <section id="about" class="scroll-mt-40"><h2 class="font-serif text-2xl font-bold text-green-900">About {{ $house->name }}</h2>
                    <div class="prose-pc mt-3">{!! \App\Support\Content::render($house->about) !!}</div></section>
            @endif

            @if ($hasHistory)
                <section id="history" class="scroll-mt-40"><h2 class="font-serif text-2xl font-bold text-green-900">History of {{ $house->name }}</h2>
                    <div class="prose-pc mt-3">{!! \App\Support\Content::render($house->history) !!}</div></section>
            @elseif (! $hasAbout && $years->isEmpty())
                <p class="rounded-sm bg-white p-6 text-green-950/60 ring-1 ring-green-100">The history of this house will be added soon.</p>
            @endif

            {{-- Current leadership: taken from the latest year on record --}}
            @if ($housemaster || $now?->house_prefect)
                <section id="leadership" class="scroll-mt-40" aria-labelledby="lead-h">
                    <h2 id="lead-h" class="font-serif text-2xl font-bold text-green-900">Leadership{{ $now ? ' in '.$now->academic_year : '' }}</h2>
                    <ul class="mt-5 grid gap-5 sm:grid-cols-2">
                        @foreach ([['Housemaster', $housemaster, $now?->getFirstMedia('housemaster_photo'), $now?->assistant_housemaster, 'Assistant'], ['House prefect', $now?->house_prefect, $now?->getFirstMedia('house_prefect_photo'), $now?->assistant_house_prefect, 'Assistant']] as [$role, $name, $photo, $assistant, $aLabel])
                            @continue(! $name)
                            <li class="flex items-center gap-4 overflow-hidden rounded-sm bg-white p-4 shadow-md ring-1 ring-green-100" style="border-left: 6px solid {{ $colour }}">
                                @if ($photo)
                                    <img src="{{ \App\Support\Img::url($photo, 'thumb') }}" data-full="{{ \App\Support\Img::url($photo, 'large') }}" alt="{{ $name }}, {{ $role }}" class="h-20 w-20 shrink-0 cursor-zoom-in rounded-full object-cover object-top ring-2 ring-gold-400" loading="lazy">
                                @else
                                    <span class="grid h-20 w-20 shrink-0 place-items-center rounded-full bg-green-100 font-serif text-2xl font-bold text-green-800" aria-hidden="true">{{ mb_substr($name, 0, 1) }}</span>
                                @endif
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-widest text-gold-700">{{ $role }}</p>
                                    <p class="font-serif text-xl font-bold text-green-900">{{ $name }}</p>
                                    @if ($assistant)<p class="mt-1 text-sm text-green-950/70">{{ $aLabel }}: {{ $assistant }}</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            {{-- Year by year --}}
            @if ($years->isNotEmpty())
                <section id="years" class="scroll-mt-40" aria-labelledby="years-h">
                    <h2 id="years-h" class="font-serif text-2xl font-bold text-green-900">{{ $house->name }}, year by year</h2>
                    @if ($years->count() > 4)
                        <nav aria-label="Jump to a school year" class="mt-4 flex flex-wrap gap-2">
                            @foreach ($years as $y)<a href="#year-{{ str_replace('/', '-', $y->academic_year) }}" class="rounded-sm bg-white px-2.5 py-1 text-xs font-bold text-green-900 ring-1 ring-green-200 hover:ring-gold-400">{{ $y->academic_year }}</a>@endforeach
                        </nav>
                    @endif

                    <ol class="relative ml-3 mt-8 space-y-8 border-l-4 pl-8" style="border-color: {{ $colour }}66">
                        @foreach ($years as $y)
                            @php
                                $group = $y->getFirstMedia('group_photo');
                                $hmPhoto = $y->getFirstMedia('housemaster_photo');
                                $hpPhoto = $y->getFirstMedia('house_prefect_photo');
                                $yGallery = $y->getMedia('gallery');
                                $lines = $y->achievementLines();
                            @endphp
                            <li id="year-{{ str_replace('/', '-', $y->academic_year) }}" class="relative scroll-mt-40">
                                <span class="absolute -left-[2.65rem] top-1 h-5 w-5 rounded-full border-4 border-white shadow ring-2" style="background-color: {{ $colour }}; --tw-ring-color: {{ $colour }}" aria-hidden="true"></span>
                                <article class="overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100">
                                    <header class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 text-white" style="background-color: {{ $colour }}">
                                        <h3 class="font-serif text-xl font-bold [text-shadow:0_1px_2px_rgba(0,0,0,.45)]">{{ $y->academic_year }}</h3>
                                    </header>
                                    @if ($group)
                                        <img src="{{ \App\Support\Img::url($group, 'large') }}" data-full="{{ \App\Support\Img::url($group, 'large') }}" alt="{{ $house->name }} group photo, {{ $y->academic_year }}" class="max-h-96 w-full cursor-zoom-in object-cover" loading="lazy">
                                    @endif
                                    <div class="grid gap-5 p-5 md:grid-cols-2">
                                        <dl class="space-y-4 text-sm">
                                            @foreach ([['Housemaster', $y->housemaster, $hmPhoto, $y->assistant_housemaster], ['House prefect', $y->house_prefect, $hpPhoto, $y->assistant_house_prefect]] as [$label, $nm, $ph, $asst])
                                                @continue(! $nm && ! $asst)
                                                <div class="flex items-center gap-3">
                                                    @if ($ph)<img src="{{ \App\Support\Img::url($ph, 'thumb') }}" data-full="{{ \App\Support\Img::url($ph, 'large') }}" alt="{{ $nm }}" class="h-14 w-14 shrink-0 cursor-zoom-in rounded-full object-cover object-top ring-2 ring-gold-400" loading="lazy">@endif
                                                    <div><dt class="text-xs font-bold uppercase tracking-widest text-gold-700">{{ $label }}</dt>
                                                        <dd class="font-semibold text-green-900">{{ $nm ?: '—' }}@if ($asst)<span class="block text-xs font-normal text-green-950/60">Assistant: {{ $asst }}</span>@endif</dd></div>
                                                </div>
                                            @endforeach
                                        </dl>
                                        @if ($lines)
                                            <div><p class="text-xs font-bold uppercase tracking-widest text-gold-700">Achievements</p>
                                                <ul class="mt-2 space-y-1.5 text-sm">@foreach ($lines as $line)<li class="flex gap-2"><span class="text-gold-600" aria-hidden="true">&#10003;</span>{{ $line }}</li>@endforeach</ul></div>
                                        @endif
                                    </div>
                                    @if ($yGallery->isNotEmpty())
                                        <div class="pc-gallery border-t border-green-100 p-4 md:!grid-cols-4">
                                            @foreach ($yGallery as $img)<figure><img src="{{ \App\Support\Img::url($img, 'card') }}" data-full="{{ \App\Support\Img::url($img, 'large') }}" alt="{{ \App\Support\Img::alt($img, $house->name.' '.$y->academic_year) }}" loading="lazy"></figure>@endforeach
                                        </div>
                                    @endif
                                </article>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if ($gallery->isNotEmpty())
                <section id="gallery" class="scroll-mt-40"><h2 class="font-serif text-2xl font-bold text-green-900">Photo gallery</h2>
                    <div class="pc-gallery mt-4">
                        @foreach ($gallery as $img)
                            <figure><img src="{{ \App\Support\Img::url($img, 'card') }}" data-full="{{ \App\Support\Img::url($img, 'large') }}" alt="{{ \App\Support\Img::alt($img, $house->name.' photo') }}" loading="lazy"></figure>
                        @endforeach
                    </div></section>
            @endif
        </div>

        <aside class="space-y-6 lg:sticky lg:top-40 lg:self-start">
            <dl class="divide-y divide-green-100 overflow-hidden rounded-sm bg-white text-sm shadow-md ring-1 ring-green-100" style="border-top: 4px solid {{ $colour }}">
                @if ($house->namesake)<div class="p-4"><dt class="font-semibold text-green-900">Named after</dt><dd class="mt-0.5">{{ $house->namesake }}</dd></div>@endif
                @if ($housemaster)<div class="p-4"><dt class="font-semibold text-green-900">Housemaster</dt><dd class="mt-0.5">{{ $housemaster }}</dd></div>@endif
                @if ($now?->house_prefect)<div class="p-4"><dt class="font-semibold text-green-900">House prefect</dt><dd class="mt-0.5">{{ $now->house_prefect }}</dd></div>@endif
                @if ($house->founded_year)<div class="p-4"><dt class="font-semibold text-green-900">Founded</dt><dd class="mt-0.5">{{ $house->founded_year }}</dd></div>@endif
                @if ($years->isNotEmpty())<div class="p-4"><dt class="font-semibold text-green-900">Years on record</dt><dd class="mt-0.5">{{ $years->last()->academic_year }} to {{ $years->first()->academic_year }}</dd></div>@endif
                <div class="flex items-center gap-3 p-4"><dt class="font-semibold text-green-900">Colour</dt><dd class="flex items-center gap-2"><span class="inline-block h-5 w-5 rounded-full ring-1 ring-black/10" style="background-color: {{ $colour }}"></span></dd></div>
            </dl>
            @if ($others->isNotEmpty())
                <div>
                    <h2 class="font-serif text-lg font-bold text-green-900">Other houses</h2>
                    <ul class="mt-3 space-y-1">
                        @foreach ($others as $o)
                            <li><a href="{{ url($o->path) }}" class="flex items-center gap-2 rounded px-2 py-1.5 text-green-950/80 hover:bg-green-50 hover:text-green-900"><span class="h-3 w-3 rounded-full" style="background-color: {{ $o->cssColour() }}"></span>{{ $o->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </aside>
    </div>
</x-layouts.app>
