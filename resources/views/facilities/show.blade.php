@php
    $cover = $facility->getFirstMedia('cover') ?? $facility->getFirstMedia('gallery');
    $gallery = $facility->getMedia('gallery');
    $services = collect($facility->services ?? [])->filter()->values();
    $hasAbout = trim(strip_tags((string) $facility->about)) !== '';
    $details = array_filter(['Opening hours' => $facility->hours, 'Location' => $facility->location, 'In charge' => trim($facility->in_charge.($facility->in_charge_role ? ', '.$facility->in_charge_role : ''), ', '), 'Contact' => $facility->contact]);
@endphp
<x-layouts.app :title="$facility->name" :description="$facility->summary" :image="$cover ? \App\Support\Img::url($cover, 'large') : null">
    <section class="relative overflow-hidden bg-green-950 text-white">
        @if ($cover)<img src="{{ \App\Support\Img::url($cover, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30" fetchpriority="high">@endif
        <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/85 to-green-900/30"></div>
        <div class="container-pc relative py-14 md:py-20">
            <nav aria-label="Breadcrumb" class="text-sm text-green-200">
                <ol class="flex flex-wrap gap-2"><li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li><li aria-hidden="true">/</li><li><a class="hover:text-gold-400" href="{{ route('facilities.index') }}">Facilities</a></li><li aria-hidden="true">/</li><li aria-current="page">{{ $facility->name }}</li></ol>
            </nav>
            <p class="eyebrow mt-6 !text-gold-400">{{ \App\Models\Facility::CATEGORIES[$facility->category] ?? 'Facility' }}@if ($facility->is_planned) &middot; Planned @endif</p>
            <h1 class="mt-2 max-w-4xl font-serif text-4xl font-bold leading-tight md:text-5xl">{{ $facility->name }}</h1>
        </div>
        <div class="h-1.5 bg-gold-400"></div>
    </section>

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_20rem]">
        <article class="min-w-0 space-y-10">
            @if ($facility->summary)<p class="text-xl leading-relaxed text-green-950">{{ $facility->summary }}</p>@endif

            @if ($hasAbout)<div class="prose-pc">{!! \App\Support\Content::render($facility->about) !!}</div>@endif

            @if ($services->isNotEmpty())
                <section aria-labelledby="svc-h">
                    <h2 id="svc-h" class="font-serif text-2xl font-bold text-green-900">Services and features</h2>
                    <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach ($services as $s)
                            <li class="flex gap-3 rounded-sm bg-white p-4 shadow-sm ring-1 ring-green-100"><span class="mt-0.5 text-gold-600" aria-hidden="true">&#10003;</span><span>{{ $s }}</span></li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($gallery->isNotEmpty())
                <section aria-labelledby="gal-h"><h2 id="gal-h" class="font-serif text-2xl font-bold text-green-900">Photos</h2>
                    <div class="pc-gallery mt-4">
                        @foreach ($gallery as $img)<figure><img src="{{ \App\Support\Img::url($img, 'card') }}" data-full="{{ \App\Support\Img::url($img, 'large') }}" alt="{{ \App\Support\Img::alt($img, $facility->name.' photo') }}" loading="lazy"></figure>@endforeach
                    </div></section>
            @endif

            @if ($facility->needsDetails())
                <div class="rounded-sm border-l-4 border-gold-400 bg-white p-6 shadow-sm ring-1 ring-green-100">
                    <p class="font-semibold text-green-900">More details coming soon</p>
                    <p class="mt-1 text-sm text-green-950/75">Photos, opening times and the people in charge of the {{ $facility->name }} will be added here. Questions in the meantime? <a href="{{ route('contact') }}" class="font-semibold text-green-700 underline">Contact the school office</a>.</p>
                </div>
            @endif
        </article>

        <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
            @if ($details)
                <dl class="divide-y divide-green-100 overflow-hidden rounded-sm bg-white text-sm shadow-md ring-1 ring-green-100" style="border-top: 4px solid #ffc53a">
                    @foreach ($details as $label => $value)<div class="p-4"><dt class="font-semibold text-green-900">{{ $label }}</dt><dd class="mt-0.5">{{ $value }}</dd></div>@endforeach
                </dl>
            @endif
            @if ($others->isNotEmpty())
                <div><h2 class="font-serif text-lg font-bold text-green-900">Other facilities</h2>
                    <ul class="mt-3 space-y-1">@foreach ($others as $o)<li><a href="{{ url($o->path) }}" class="flex items-center gap-2 rounded px-2 py-1.5 text-green-950/80 hover:bg-green-50 hover:text-green-900">{{ $o->name }}@if ($o->is_planned)<span class="rounded bg-gold-100 px-1.5 text-[10px] font-bold uppercase text-gold-700">Planned</span>@endif</a></li>@endforeach</ul>
                    <a href="{{ route('facilities.index') }}" class="mt-3 inline-block text-sm font-semibold text-green-700 hover:underline">All facilities &raquo;</a></div>
            @endif
        </aside>
    </div>
</x-layouts.app>
