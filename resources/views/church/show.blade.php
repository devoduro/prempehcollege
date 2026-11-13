@php
    $cover = $denomination->getFirstMedia('cover');
    $gallery = $denomination->getMedia('gallery');
    $activities = collect($denomination->activities ?? [])->filter(fn ($a) => ! empty($a['title']))->values();
    $hasAbout = trim(strip_tags((string) $denomination->about)) !== '';
    $details = array_filter(['Meets' => $denomination->meeting_times, 'Venue' => $denomination->venue, 'Patron' => trim($denomination->patron.($denomination->patron_role ? ', '.$denomination->patron_role : ''), ', ')]);
@endphp
<x-layouts.app :title="$denomination->name" :description="$denomination->summary" :image="$cover ? \App\Support\Img::url($cover, 'large') : null">
    <section class="relative overflow-hidden bg-green-950 text-white">
        @if ($cover)<img src="{{ \App\Support\Img::url($cover, 'large') }}" alt="" class="absolute inset-0 h-full w-full object-cover opacity-30" fetchpriority="high">@endif
        <div class="absolute inset-0 bg-gradient-to-r from-green-950 via-green-950/85 to-green-900/30"></div>
        <div class="container-pc relative py-14 md:py-20">
            <nav aria-label="Breadcrumb" class="text-sm text-green-200">
                <ol class="flex flex-wrap gap-2"><li><a class="hover:text-gold-400" href="{{ route('home') }}">Home</a></li><li aria-hidden="true">/</li><li><a class="hover:text-gold-400" href="{{ route('church.index') }}">Church life</a></li><li aria-hidden="true">/</li><li aria-current="page">{{ $denomination->name }}</li></ol>
            </nav>
            <p class="eyebrow mt-6 !text-gold-400">Church life</p>
            <h1 class="mt-2 max-w-4xl font-serif text-4xl font-bold leading-tight md:text-5xl">{{ $denomination->name }}</h1>
        </div>
        <div class="h-1.5 bg-gold-400"></div>
    </section>

    <div class="container-pc grid gap-12 py-12 lg:grid-cols-[1fr_20rem]">
        <article class="min-w-0 space-y-10">
            @if ($denomination->summary)<p class="text-xl leading-relaxed text-green-950">{{ $denomination->summary }}</p>@endif
            @if ($hasAbout)<div class="prose-pc">{!! \App\Support\Content::render($denomination->about) !!}</div>@endif

            @if ($activities->isNotEmpty())
                <section aria-labelledby="act-h">
                    <h2 id="act-h" class="font-serif text-2xl font-bold text-green-900">Activities</h2>
                    <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($activities as $a)
                            <li class="rounded-sm bg-white p-5 shadow-sm ring-1 ring-green-100" style="border-top: 3px solid #ffc53a"><h3 class="font-semibold text-green-900">{{ $a['title'] }}</h3>@if (! empty($a['text']))<p class="mt-1 text-sm text-green-950/80">{{ $a['text'] }}</p>@endif</li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($gallery->isNotEmpty())
                <section aria-labelledby="gal-h"><h2 id="gal-h" class="font-serif text-2xl font-bold text-green-900">Photos</h2>
                    <div class="pc-gallery mt-4">
                        @foreach ($gallery as $img)<figure><img src="{{ \App\Support\Img::url($img, 'card') }}" data-full="{{ \App\Support\Img::url($img, 'large') }}" alt="{{ \App\Support\Img::alt($img, $denomination->name.' photo') }}" loading="lazy"></figure>@endforeach
                    </div></section>
            @endif

            @if (! $hasAbout && $activities->isEmpty())
                <div class="rounded-sm border-l-4 border-gold-400 bg-white p-6 shadow-sm ring-1 ring-green-100">
                    <p class="font-semibold text-green-900">More details coming soon</p>
                    <p class="mt-1 text-sm text-green-950/75">Meeting times, activities and photos will be added here. <a href="{{ route('contact') }}" class="font-semibold text-green-700 underline">Contact the school office</a> for questions.</p>
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
                <div><h2 class="font-serif text-lg font-bold text-green-900">Other groups</h2>
                    <ul class="mt-3 space-y-1">@foreach ($others as $o)<li><a href="{{ url($o->path) }}" class="block rounded px-2 py-1.5 text-green-950/80 hover:bg-green-50 hover:text-green-900">{{ $o->name }}</a></li>@endforeach</ul>
                    <a href="{{ route('church.index') }}" class="mt-3 inline-block text-sm font-semibold text-green-700 hover:underline">All church life &raquo;</a></div>
            @endif
        </aside>
    </div>
</x-layouts.app>
