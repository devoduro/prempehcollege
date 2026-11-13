@php
    // Dynamic features that belong to a section (houses, programmes, prefects) are shown as cards beside its pages.
    $extras = config("site.section_extras.$section", []);
@endphp
<x-layouts.app :title="$label" :description="$overview ? \Illuminate\Support\Str::limit(strip_tags((string) $overview->body), 160) : null">
    <x-page-header :title="$label" :crumbs="[$label => null]" />

    @php
        $facts = collect($overview?->facts ?? [])->filter(fn ($f) => filled($f['value'] ?? null) && filled($f['label'] ?? null))->values();
        $milestones = collect($overview?->highlights ?? [])->filter(fn ($h) => filled($h['title'] ?? null))->values();
        $online = config('site.links.online_classes');
        // The programmes hub already lists programmes, so its generic card is dropped to avoid repeating it.
        if ($programmes->isNotEmpty()) {
            $extras = array_values(array_filter($extras, fn ($e) => $e[1] !== 'programmes.index'));
        }
    @endphp

    {{-- Key facts strip (from the overview page) --}}
    @if ($facts->isNotEmpty())
        @php($cols = [1 => 'md:grid-cols-1', 2 => 'md:grid-cols-2', 3 => 'md:grid-cols-3', 4 => 'md:grid-cols-4'][min($facts->count(), 4)])
        <section class="border-b border-green-100 bg-white" aria-label="At a glance">
            <dl class="container-pc grid grid-cols-2 gap-6 py-8 text-center {{ $cols }}">
                @foreach ($facts as $f)
                    <div><dd class="font-serif text-3xl font-bold text-green-900 md:text-4xl">{{ $f['value'] }}</dd><dt class="mt-1 text-xs font-bold uppercase tracking-widest text-green-800">{{ $f['label'] }}</dt></div>
                @endforeach
            </dl>
        </section>
    @endif

    <div class="container-pc py-12">
        @if ($overview)
            <div class="prose-pc max-w-4xl">{!! \App\Support\Content::render($overview->body) !!}</div>
        @endif

        {{-- Programmes with core subjects and elective combinations --}}
        @if ($programmes->isNotEmpty())
            <div class="mt-12"><x-section-title title="Programmes" :href="route('programmes.index')" link-label="Course combinations" /></div>
            <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($programmes as $pr)
                    <li class="group relative flex flex-col overflow-hidden rounded-sm bg-white p-6 shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                        <span class="absolute inset-x-0 top-0 h-1.5 bg-gold-400"></span>
                        <h3 class="font-serif text-xl font-bold text-green-900"><a href="{{ url($pr->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $pr->name }}</a></h3>
                        <p class="mt-3 flex-1 text-sm leading-relaxed text-green-950/80">{{ $pr->summary }}</p>
                        <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-green-800">{{ $pr->combinations->count() ? $pr->combinations->count().' elective '.\Illuminate\Support\Str::plural('combination', $pr->combinations->count()) : 'Core subjects & combinations' }}</p>
                        <p class="mt-1 text-sm font-semibold text-green-700">View subjects &raquo;</p>
                    </li>
                @endforeach
            </ul>
        @endif

        {{-- Learn online --}}
        @if ($section === 'academics' && $online)
            <div class="mt-12 flex flex-wrap items-center justify-between gap-4 rounded-sm bg-green-900 p-6 text-white shadow-lg md:p-8">
                <div><p class="eyebrow !text-gold-400">e-Learning</p>
                    <h2 class="mt-1 font-serif text-2xl font-bold">Keep learning from the comfort of home</h2>
                    <p class="mt-1 text-green-100">Lesson content delivered online to stimulate students' interest in learning.</p></div>
                <a href="{{ $online }}" class="btn btn-gold" rel="noopener">Open online classes <span aria-hidden="true">&rarr;</span></a>
            </div>
        @endif

        {{-- Academic milestones --}}
        @if ($milestones->isNotEmpty())
            <div class="mt-14"><x-section-title title="Academic milestones" /></div>
            <ol class="relative ml-3 mt-8 space-y-7 border-l-4 border-gold-400/60 pl-8" aria-label="Academic milestones">
                @foreach ($milestones as $m)
                    <li class="relative">
                        <span class="absolute -left-[2.65rem] top-2 h-5 w-5 rounded-full border-4 border-white bg-green-800 shadow ring-2 ring-gold-400" aria-hidden="true"></span>
                        @if (filled($m['year'] ?? null))<p class="inline-block rounded-sm bg-green-900 px-3 py-1 text-xs font-bold uppercase tracking-widest text-gold-400">{{ $m['year'] }}</p>@endif
                        <h3 class="mt-2 font-serif text-xl font-bold text-green-900">{{ $m['title'] }}</h3>
                        @if (filled($m['text'] ?? null))<p class="mt-1 leading-relaxed text-green-950/85">{{ $m['text'] }}</p>@endif
                    </li>
                @endforeach
            </ol>
        @endif

        {{-- Featured leaders (e.g. Board Chairman and Headmaster) --}}
        @if ($featured->isNotEmpty())
            <div class="{{ $overview ? 'mt-12' : '' }}"><x-section-title title="Meet our leaders" /></div>
            <ul class="mt-8 grid gap-6 md:grid-cols-2">
                @foreach ($featured as ['page' => $fp, 'person' => $fl])
                    @php($ph = $fl->getFirstMedia('photo'))
                    <li class="group relative flex overflow-hidden rounded-sm bg-white shadow-lg ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                        @if ($ph)<img src="{{ \App\Support\Img::url($ph, 'card') }}" alt="{{ $fl->name }}, {{ $fl->role }}" class="aspect-[4/5] w-2/5 object-cover object-top" loading="lazy" width="800" height="1000">@endif
                        <div class="flex flex-1 flex-col justify-center border-l-4 border-gold-400 p-5">
                            <p class="eyebrow">{{ $fl->role }}</p>
                            <h3 class="mt-1 font-serif text-2xl font-bold leading-snug text-green-900">
                                <a href="{{ url($fp->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $fl->name }}</a>
                            </h3>
                            <p class="mt-3 text-sm font-semibold text-green-700">{{ $fp->title }} &raquo;</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        @if (count($extras) + $pages->count() > 0)
        <div class="mt-12"><x-section-title :title="'Explore '.$label" /></div>

        <ul class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($extras as [$title, $route, $blurb])
                <li class="group relative overflow-hidden rounded-sm bg-green-900 p-6 text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-xl">
                    <span class="absolute inset-x-0 top-0 h-1.5 bg-gold-400"></span>
                    <h2 class="font-serif text-xl font-bold text-gold-400"><a href="{{ route($route) }}" class="after:absolute after:inset-0">{{ $title }}</a></h2>
                    <p class="mt-3 text-sm text-green-100">{{ $blurb }}</p>
                    <p class="mt-4 text-sm font-semibold">Explore &raquo;</p>
                </li>
            @endforeach
            @foreach ($pages as $p)
                @php($team = $p->staffGroup?->members ?? collect())
                @php($img = $team->isEmpty() ? ($covers[$p->id] ?? \App\Support\Img::cover($p)) : null)
                @php($lead = collect($p->facts ?? [])->first(fn ($f) => filled($f['value'] ?? null) && filled($f['label'] ?? null)))
                <li class="group relative overflow-hidden rounded-sm bg-white shadow-md ring-1 ring-green-100 transition hover:-translate-y-0.5 hover:shadow-xl">
                    @if ($team->isNotEmpty())
                        {{-- A page of people: overlapping faces show who is on it --}}
                        <div class="flex aspect-[3/2] items-center justify-center bg-gradient-to-br from-green-900 to-green-700" aria-hidden="true">
                            <div class="flex -space-x-4">
                                @foreach ($team->filter(fn ($m) => $m->getFirstMedia('photo'))->take(4) as $m)
                                    <img src="{{ \App\Support\Img::url($m->getFirstMedia('photo'), 'thumb') }}" alt="" class="h-16 w-16 rounded-full object-cover object-top ring-4 ring-green-900" loading="lazy">
                                @endforeach
                            </div>
                        </div>
                    @elseif ($img)
                        <img src="{{ \App\Support\Img::url($img, 'thumb') }}" alt="" class="aspect-[3/2] w-full object-cover" loading="lazy" width="480" height="320">
                    @else
                        <div class="aspect-[3/2] w-full bg-gradient-to-br from-green-900 to-green-700" aria-hidden="true"></div>
                    @endif
                    <div class="p-5">
                        <h2 class="font-serif text-xl font-bold text-green-900">
                            <a href="{{ url($p->path) }}" class="after:absolute after:inset-0 group-hover:underline">{{ $p->title }}</a>
                        </h2>
                        @if ($team->isNotEmpty())<p class="mt-1 text-sm text-green-950/70">{{ $team->count() }} {{ \Illuminate\Support\Str::plural('person', $team->count()) }}</p>
                        @elseif ($lead)<p class="mt-1 text-sm text-green-950/70"><span class="font-bold text-green-900">{{ $lead['value'] }}</span> &middot; {{ $lead['label'] }}</p>@endif
                        <p class="mt-2 text-sm font-semibold text-green-700">{{ $team->isNotEmpty() ? 'Meet the team' : 'Read more' }} &raquo;</p>
                    </div>
                </li>
            @endforeach
        </ul>
        @endif
    </div>

    {{-- Latest stories for this section --}}
    @if ($news->isNotEmpty())
        <section class="bg-green-50 py-14" aria-labelledby="section-news">
            <div class="container-pc">
                <x-section-title id="section-news" :title="'Latest from '.$label" :href="route('news.index')" link-label="All news" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($news as $i => $post)<x-news-card :post="$post" :highlight="$i === 0" />@endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
